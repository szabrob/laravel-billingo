<?php

declare(strict_types=1);

namespace Workbench\App\Http\Controllers;

use BackedEnum;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\Events\ConnectionFailed;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JsonException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Spatie\LaravelData\Attributes\MapName;
use Szabrob\LaravelBillingo\Billingo;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Data\Documents\PaymentData;
use Szabrob\LaravelBillingo\Exceptions\BillingoException;
use Szabrob\LaravelBillingo\Exceptions\ValidationException;
use Szabrob\LaravelBillingo\Resources\Resource;
use Szabrob\LaravelBillingo\Support\PaginatedResponse;
use Throwable;

/**
 * A manual testing UI for the package: every public resource method can be called with live arguments.
 */
class PlaygroundController
{
    /**
     * The maximum number of items taken from a lazy collection.
     */
    private const LAZY_LIMIT = 50;

    public function __construct(
        private readonly Billingo $billingo,
    ) {}

    public function index(): View
    {
        $apiKey = config('laravel-billingo.api_key');

        return view('playground', [
            'catalog' => $this->catalog(),
            'config' => [
                'api_key' => is_string($apiKey) && $apiKey !== '' ? Str::mask($apiKey, '*', 4, -4) : null,
                'base_url' => config('laravel-billingo.base_url'),
                'timeout' => config('laravel-billingo.timeout'),
                'rate_limit' => config('laravel-billingo.rate_limit'),
            ],
        ]);
    }

    public function call(Request $request): JsonResponse
    {
        $resourceName = $request->string('resource')->toString();
        $methodName = $request->string('method')->toString();
        $method = $this->resolveMethod($resourceName, $methodName);

        if ($method === null) {
            return response()->json(['ok' => false, 'error' => ['message' => "Unknown method {$resourceName}()->{$methodName}()."]], 404);
        }

        $client = $request->filled('api_key')
            ? $this->billingo->withApiKey($request->string('api_key')->toString())
            : $this->billingo;

        $http = [];
        Event::listen(ResponseReceived::class, function (ResponseReceived $event) use (&$http): void {
            $http[] = [
                'method' => $event->request->method(),
                'url' => $event->request->url(),
                'request_body' => $this->decode($event->request->body()),
                'status' => $event->response->status(),
                'response_headers' => collect($event->response->headers())->map(fn (array $values): string => implode(', ', $values))->all(),
                'response_body' => $this->decode($event->response->body()),
            ];
        });
        Event::listen(ConnectionFailed::class, function (ConnectionFailed $event) use (&$http): void {
            $http[] = [
                'method' => $event->request->method(),
                'url' => $event->request->url(),
                'request_body' => $this->decode($event->request->body()),
                'status' => null,
                'error' => $event->exception->getMessage(),
            ];
        });

        $started = microtime(true);

        try {
            /** @var array<string, mixed> $input */
            $input = $request->input('args', []);
            $arguments = $this->arguments($method, is_array($input) ? $input : []);

            /** @var resource $resource */
            $resource = $client->{$resourceName}();
            $result = $resource->{$methodName}(...$arguments);

            return response()->json([
                'ok' => true,
                'duration_ms' => $this->duration($started),
                'result' => $this->present($result),
                'http' => $http,
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'ok' => false,
                'duration_ms' => $this->duration($started),
                'error' => $this->presentException($exception),
                'http' => $http,
            ]);
        }
    }

    /**
     * Describe every public method of every resource exposed by the client.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private function catalog(): array
    {
        $catalog = [];

        foreach ($this->resourceAccessors() as $accessor => $resourceClass) {
            foreach ((new ReflectionClass($resourceClass))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->isConstructor() || $method->isStatic() || $method->getDeclaringClass()->getName() !== $resourceClass) {
                    continue;
                }

                $catalog[$accessor][] = [
                    'name' => $method->getName(),
                    'kind' => $this->kind($method->getName()),
                    'doc' => $this->summary($method),
                    'returns' => (string) $method->getReturnType(),
                    'params' => array_map(fn (ReflectionParameter $parameter): array => $this->describeParameter($parameter), $method->getParameters()),
                ];
            }
        }

        return $catalog;
    }

    /**
     * @return array<string, class-string<resource>>
     */
    private function resourceAccessors(): array
    {
        $accessors = [];

        foreach ((new ReflectionClass(Billingo::class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $type = $method->getReturnType();

            if ($type instanceof ReflectionNamedType && is_subclass_of($type->getName(), Resource::class)) {
                $accessors[$method->getName()] = $type->getName();
            }
        }

        ksort($accessors);

        return $accessors;
    }

    private function resolveMethod(string $resource, string $method): ?ReflectionMethod
    {
        $resourceClass = $this->resourceAccessors()[$resource] ?? null;

        if ($resourceClass === null || ! method_exists($resourceClass, $method)) {
            return null;
        }

        $reflection = new ReflectionMethod($resourceClass, $method);

        return $reflection->isPublic() && ! $reflection->isConstructor() ? $reflection : null;
    }

    private function kind(string $method): string
    {
        return match (true) {
            (bool) preg_match('/^(delete|cancel)/', $method) => 'danger',
            (bool) preg_match('/^(create|update|send|archive|copy)/', $method) => 'write',
            default => 'read',
        };
    }

    private function summary(ReflectionMethod $method): ?string
    {
        $doc = $method->getDocComment();

        if ($doc === false) {
            return null;
        }

        foreach (preg_split('/\R/', $doc) ?: [] as $line) {
            $line = trim($line, " \t/*");

            if ($line !== '' && ! str_starts_with($line, '@')) {
                return $line;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function describeParameter(ReflectionParameter $parameter): array
    {
        $type = $parameter->getType();
        $typeName = $type instanceof ReflectionNamedType ? $type->getName() : (string) $type;

        $description = [
            'name' => $parameter->getName(),
            'type' => $typeName,
            'nullable' => $type?->allowsNull() ?? true,
            'optional' => $parameter->isOptional(),
            'default' => $parameter->isDefaultValueAvailable() ? $this->scalarDefault($parameter->getDefaultValue()) : null,
            'input' => 'text',
        ];

        return match (true) {
            $typeName === 'int' => [...$description, 'input' => 'number'],
            $typeName === 'bool' => [...$description, 'input' => 'checkbox'],
            is_subclass_of($typeName, BackedEnum::class) => [
                ...$description,
                'input' => 'select',
                'options' => array_map(fn (BackedEnum $case): string|int => $case->value, $typeName::cases()),
            ],
            is_a($typeName, DateTimeInterface::class, true) => [...$description, 'input' => 'date'],
            is_subclass_of($typeName, BillingoData::class) => [
                ...$description,
                'input' => 'json',
                'template' => $this->template($typeName),
            ],
            $typeName === 'iterable' => [
                ...$description,
                'input' => 'json',
                'template' => [$this->template(PaymentData::class)],
            ],
            $typeName === 'array' => [...$description, 'input' => 'json', 'template' => []],
            default => $description,
        };
    }

    private function scalarDefault(mixed $value): mixed
    {
        return $value instanceof BackedEnum ? $value->value : (is_scalar($value) || is_array($value) ? $value : null);
    }

    /**
     * Build a JSON skeleton of a data object with every property set to null.
     *
     * @param  class-string<BillingoData>  $class
     * @return array<string, mixed>
     */
    private function template(string $class, int $depth = 0): array
    {
        $reflection = new ReflectionClass($class);
        $snake = $reflection->getAttributes(MapName::class) !== [];
        $template = [];

        foreach ($reflection->getConstructor()?->getParameters() ?? [] as $parameter) {
            $type = $parameter->getType();
            $typeName = $type instanceof ReflectionNamedType ? $type->getName() : null;
            $key = $snake ? Str::snake($parameter->getName()) : $parameter->getName();

            $template[$key] = $typeName !== null && $depth < 2 && is_subclass_of($typeName, BillingoData::class)
                ? $this->template($typeName, $depth + 1)
                : null;
        }

        return $template;
    }

    /**
     * Convert the submitted form values into method arguments.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function arguments(ReflectionMethod $method, array $input): array
    {
        $arguments = [];

        foreach ($method->getParameters() as $parameter) {
            $name = $parameter->getName();
            $value = $input[$name] ?? null;

            if ($value === null || $value === '') {
                if ($parameter->isDefaultValueAvailable()) {
                    continue;
                }

                if ($parameter->allowsNull()) {
                    $arguments[$name] = null;

                    continue;
                }

                throw new InvalidArgumentException("The \"{$name}\" argument is required.");
            }

            $arguments[$name] = $this->cast($parameter, $value);
        }

        return $arguments;
    }

    private function cast(ReflectionParameter $parameter, mixed $value): mixed
    {
        $type = $parameter->getType();
        $typeName = $type instanceof ReflectionNamedType ? $type->getName() : 'mixed';
        $name = $parameter->getName();

        return match (true) {
            $typeName === 'int' => is_numeric($value)
                ? (int) $value
                : throw new InvalidArgumentException("The \"{$name}\" argument must be an integer."),
            $typeName === 'bool' => filter_var($value, FILTER_VALIDATE_BOOL),
            $typeName === 'string' => is_scalar($value) ? (string) $value : throw new InvalidArgumentException("The \"{$name}\" argument must be a string."),
            is_subclass_of($typeName, BackedEnum::class) => $typeName::from(is_scalar($value) ? $value : ''),
            is_a($typeName, DateTimeInterface::class, true) => CarbonImmutable::parse(is_string($value) ? $value : ''),
            is_subclass_of($typeName, BillingoData::class) => $typeName::from($this->json($name, $value)),
            $typeName === 'iterable' => PaymentData::collect($this->json($name, $value)),
            $typeName === 'array' => $this->json($name, $value),
            default => $value,
        };
    }

    /**
     * @return array<array-key, mixed>
     */
    private function json(string $name, mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        try {
            $decoded = json_decode(is_string($value) ? $value : '', true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException("The \"{$name}\" argument is not valid JSON: {$exception->getMessage()}");
        }

        return is_array($decoded) ? $decoded : throw new InvalidArgumentException("The \"{$name}\" argument must be a JSON object or array.");
    }

    /**
     * Turn a method result into something JSON serializable for the UI.
     *
     * @return array<string, mixed>
     */
    private function present(mixed $result): array
    {
        return match (true) {
            $result === null => ['type' => 'void', 'value' => null],
            $result instanceof PaginatedResponse => [
                'type' => PaginatedResponse::class,
                'meta' => [
                    'total' => $result->total,
                    'per_page' => $result->perPage,
                    'current_page' => $result->currentPage,
                    'last_page' => $result->lastPage,
                    'has_more_pages' => $result->hasMorePages(),
                ],
                'value' => $result->data->toArray(),
            ],
            $result instanceof LazyCollection => [
                'type' => LazyCollection::class,
                'note' => 'Only the first '.self::LAZY_LIMIT.' items are shown.',
                'value' => $result->take(self::LAZY_LIMIT)->values()->toArray(),
            ],
            $result instanceof Collection => ['type' => Collection::class, 'value' => $result->toArray()],
            $result instanceof BillingoData => ['type' => $result::class, 'value' => $result->toArray()],
            is_string($result) && ! mb_check_encoding($result, 'UTF-8') || is_string($result) && str_starts_with($result, '%PDF') => [
                'type' => 'binary',
                'mime' => str_starts_with($result, '%PDF') ? 'application/pdf' : 'application/octet-stream',
                'size' => strlen($result),
                'base64' => base64_encode($result),
            ],
            default => ['type' => get_debug_type($result), 'value' => $result],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function presentException(Throwable $exception): array
    {
        return [
            'class' => $exception::class,
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'errors' => $exception instanceof ValidationException ? $exception->errors() : null,
            'response' => $exception instanceof BillingoException && $exception->response !== null
                ? ['status' => $exception->response->status(), 'body' => $this->decode($exception->response->body())]
                : null,
            'location' => $exception instanceof BillingoException || $exception instanceof InvalidArgumentException
                ? null
                : $exception->getFile().':'.$exception->getLine(),
        ];
    }

    private function decode(string $body): mixed
    {
        if ($body === '') {
            return null;
        }

        $decoded = json_decode($body, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return mb_check_encoding($body, 'UTF-8')
            ? Str::limit($body, 2000)
            : '[binary content, '.strlen($body).' bytes]';
    }

    private function duration(float $started): int
    {
        return (int) round((microtime(true) - $started) * 1000);
    }
}
