<?php
namespace App\Controller\Partnership;

use App\Repository\PartnershipRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class ListPartnership extends AbstractController
{
    public function __invoke(PartnershipRepository $partnershipRepository, SerializerInterface $serializer)
    {
        $partnerships = $partnershipRepository->findAll();
        $json = $serializer->serialize($partnerships, 'json', [
            'groups' => ['partnership:read']
        ]);

        return new JsonResponse($json, 200, [
            'Content-Type' => 'application/json'
        ]);
    }
}
