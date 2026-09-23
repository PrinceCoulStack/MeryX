<?php

namespace App\Controller\Opportunities;

use App\Repository\OpportunitiesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class ListOpportunities extends AbstractController
{
    public function __invoke(OpportunitiesRepository $repository): JsonResponse
    {
        $items = $repository->findAll();

        return $this->json($items, 200, [], [
            'groups' => ['opportunities:read'],
        ]);
    }
}
