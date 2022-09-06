<?php

namespace App\Traits;

use Exception;
use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Arr;

trait ApiHandlerTrait
{
    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson()
                    ? response()->json([
                        'error'  => [
                            'code' => 401,
                            'title' => $exception->getMessage(),
                            'errors' => [
                                [
                                    'title' => 'auth',
                                    'message' => $exception->getMessage()
                                ]
                            ]
                        ]
                    ], 401)
                    : redirect()->guest(
                        $exception->redirectTo() ??
                            route(Arr::get($exception->guards(), 0) . '.login')
                        );
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Validation\ValidationException  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        $errors = [];

        foreach ($exception->errors() as $key => $message) {
            $errors[] = [
                'title' => $key,
                'message' => $message[0]
            ];
        }

        return response()->json([
            'error' => [
                'code'   => $exception->status,
                'title'  => $exception->getMessage(),
                'errors' => $errors,
            ]
        ], $exception->status);
    }

    /**
     * Prepare a JSON response for the given exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function prepareJsonResponse($request, Throwable $e)
    {
        $status = $this->isHttpException($e) ? $e->getStatusCode() : 500;

        $headers = $this->isHttpException($e) ? $e->getHeaders() : [];

        $error = "";

        switch ($status) {
            case 401:
                $error = "Invalid API key.";
                break;

            case 403:
                $error = "API key is missing.";
                break;

            case 404:
                $error = "User not found.";
                break;

            default:
                $error = "Something went wrong. Please try again later.";
                break;
        }

        $errorsArray = [
            'code'  => $status,
            'error' => $error,
        ];

        return new JsonResponse(
            $errorsArray,
            $status, $headers,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }
}
