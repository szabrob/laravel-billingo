<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Exceptions;

use Illuminate\Http\Client\Response;

class ValidationException extends BillingoException
{
    public function __construct(string $message, Response $response)
    {
        parent::__construct($message, $response->status(), $response);
    }

    /**
     * The validation errors returned by Billingo, keyed by field name.
     *
     * @return array<string, list<string>>
     */
    public function errors(): array
    {
        $errors = [];
        $items = $this->response?->json('errors');

        foreach (is_array($items) ? $items : [] as $error) {
            if (is_array($error) && is_string($error['field'] ?? null) && is_string($error['message'] ?? null)) {
                $errors[$error['field']][] = $error['message'];
            }
        }

        return $errors;
    }
}
