<?php

namespace App\Controller\CompanyPost;

use App\Repository\CompanyPostRepository;
use App\Service\CompanyPost\CompanyPostResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class ListCompanyPost extends AbstractController
{
    public function __invoke(CompanyPostRepository $repository, CompanyPostResponseBuilder $responseBuilder): JsonResponse
    {
        $items = $repository->findAll();

        return $this->json($responseBuilder->buildCollection($items), 200);
    }
}
