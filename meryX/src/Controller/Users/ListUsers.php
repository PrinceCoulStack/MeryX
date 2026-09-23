<?php

namespace App\Controller\Users;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class ListUsers extends AbstractController
{
    public function __invoke(UserRepository $repo): JsonResponse
    {
        $users = $repo->findAll();

        return $this->json($users, 200, [], ['groups' => ['user:read']]);
    }
}
