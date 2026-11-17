<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class DomainRuleException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 409,
    ) {
        parent::__construct($message, $statusCode);
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->getMessage(),
            ], $this->statusCode);
        }

        return back()->withInput()->with('error', $this->getMessage());
    }
}
