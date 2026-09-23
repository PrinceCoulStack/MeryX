<?php
namespace App\Controller\TrainingRequirement;
use App\Repository\TrainingRequirementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

#[AsController]
class ListTrainingRequirement extends AbstractController
{
    public function __invoke(TrainingRequirementRepository $trainingRequirementRepository, SerializerInterface $serializer)
    {
        $trainingRequirements = $trainingRequirementRepository->findAll();
        $json = $serializer->serialize($trainingRequirements, 'json', [
            'groups' => ['trainingRequirement:read']
        ]);
        return new JsonResponse($json, 200, [
            'Content-Type' => 'application/json'
        ]);
    }
}
