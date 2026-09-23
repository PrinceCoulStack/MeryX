<?php

namespace App\Security\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;

class LoginFailureHandler extends AuthenticationFailureHandler
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());
        $statusCode = $exception instanceof AccountStatusException ? Response::HTTP_FORBIDDEN : Response::HTTP_UNAUTHORIZED;

        return new JsonResponse([
            'status' => $statusCode,
            'message' => $message,
            'detail' => $message,
            'error' => $message,
            'error_type' => $exception::class,
        ], $statusCode);
    }
}
