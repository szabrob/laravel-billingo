<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Exceptions;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;

class ValidationException extends BillingoException
{
    public function __construct(string $message, Response $response)
    {
        parent::__construct($message.self::details($response), $response->status(), $response);
    }

    /**
     * The validation errors returned by Billingo, keyed by field name.
     *
     * @return array<string, list<string>>
     */
    public function errors(): array
    {
        return $this->response === null ? [] : self::parseErrors($this->response);
    }

    /**
     * @return array<string, list<string>>
     */
    private static function parseErrors(Response $response): array
    {
        $errors = [];
        $items = $response->json('errors');

        foreach (is_array($items) ? $items : [] as $error) {
            if (is_array($error) && is_string($error['field'] ?? null) && is_string($error['message'] ?? null)) {
                $errors[$error['field']][] = $error['message'];
            }
        }

        return $errors;
    }

    /**
     * Describe the failed fields, so the exception message tells what to fix even when only the message is logged.
     */
    private static function details(Response $response): string
    {
        $errors = self::parseErrors($response);

        if ($errors !== []) {
            $fields = [];

            foreach ($errors as $field => $messages) {
                foreach ($messages as $message) {
                    $fields[] = "{$field}: {$message}";
                }
            }

            return ' ('.implode('; ', $fields).')';
        }

        // Unknown error format: include the start of the response body instead, unless it holds nothing but the message.
        $body = trim($response->body());
        $json = $response->json();
        $onlyMessage = is_array($json) && array_diff(array_keys($json), ['message', 'error']) === []
            && (! isset($json['error']) || (is_array($json['error']) && array_diff(array_keys($json['error']), ['message']) === []));

        return $body === '' || $onlyMessage ? '' : ' Response: '.Str::limit($body, 500);
    }
}
