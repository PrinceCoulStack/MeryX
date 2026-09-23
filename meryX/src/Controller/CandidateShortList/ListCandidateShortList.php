<?php

namespace App\Controller\CandidateShortList;

use App\Repository\CandidateShortListRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class ListCandidateShortList extends AbstractController
{
    public function __invoke(CandidateShortListRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $items = $repository->findAll();
        $json = $serializer->serialize($items, 'json', [
            'groups' => ['candidateShortList:read'],
        ]);

        return new JsonResponse($json, 200, [
            'Content-Type' => 'application/json',
        ]);
    }
}
