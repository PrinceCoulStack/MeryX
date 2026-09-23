<?php

namespace App\Security\Authentication;

use App\Entity\User;
use App\Security\AuthenticatedUserPayloadBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class LoginSuccessHandler extends AuthenticationSuccessHandler
{
    public function __construct(
        \Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface $jwtManager,
        \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $dispatcher,
        private readonly AuthenticatedUserPayloadBuilder $payloadBuilder,
        private readonly EntityManagerInterface $entityManager,
        iterable $cookieProviders = [],
        bool $removeTokenFromBodyWhenCookiesUsed = true
    ) {
        parent::__construct($jwtManager, $dispatcher, $cookieProviders, $removeTokenFromBodyWhenCookiesUsed);
    }

    public function handleAuthenticationSuccess(UserInterface $user, $jwt = null, array $data = []): Response
    {
        if ($user instanceof User) {
            $user->setLastLoginAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            $payload = $this->payloadBuilder->build($user);

            $data = array_merge($data, [
                'user' => $payload,
            ], $payload);
        }

        return parent::handleAuthenticationSuccess($user, $jwt, $data);
    }
}
