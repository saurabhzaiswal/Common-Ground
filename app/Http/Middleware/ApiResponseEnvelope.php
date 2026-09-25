<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseEnvelope
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $response instanceof JsonResponse) {
            return $response;
        }

        if ($response->getStatusCode() === Response::HTTP_NO_CONTENT) {
            $response->setStatusCode(Response::HTTP_OK);
        }

        $payload = $response->getData(true);
        $payload = is_array($payload) ? $payload : ['data' => $payload];
        $isError = $response->isClientError() || $response->isServerError();

        $payload['error'] = $isError;
        $payload['message'] ??= $isError
            ? 'Something went wrong. Please try again later.'
            : 'Request completed successfully.';

        $response->setData($payload);

        return $response;
    }
}
