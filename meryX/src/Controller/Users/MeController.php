<?php

namespace App\Controller\Users;

use App\Entity\User;
use App\Security\AuthenticatedUserPayloadBuilder;
use App\Service\Api\ApiErrorResponseFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class MeController extends AbstractController
{
    public function __construct(
        private readonly AuthenticatedUserPayloadBuilder $payloadBuilder,
        private readonly ApiErrorResponseFactory $errorResponseFactory,
    )
    {
    }

    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->errorResponseFactory->create(401, 'Unauthorized', 'Missing or invalid JWT token.');
        }
        return $this->json($this->payloadBuilder->build($user));
    }
}
