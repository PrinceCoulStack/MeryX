<?php
namespace App\Controller\Training;

use App\Repository\TrainingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class ListTraining extends AbstractController
{
    public function __invoke(TrainingRepository $trainingRepository, SerializerInterface $serializer)
    {
        $trainings = $trainingRepository->findAll();
        $json = $serializer->serialize($trainings, 'json', [
            'groups' => ['training:read']
        ]);

        return new JsonResponse($json, 200, [
            'Content-Type' => 'application/json'
        ]);
    }
}
