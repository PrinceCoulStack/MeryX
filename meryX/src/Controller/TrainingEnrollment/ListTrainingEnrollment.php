<?php
namespace App\Controller\TrainingEnrollment;

use App\Repository\TrainingEnrollmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class ListTrainingEnrollment extends AbstractController
{
    public function __invoke(TrainingEnrollmentRepository $trainingEnrollmentRepository, SerializerInterface $serializer)
    {
        $trainingEnrollments = $trainingEnrollmentRepository->findAll();
        $json = $serializer->serialize($trainingEnrollments, 'json', [
            'groups' => ['trainingEnrollment:read']
        ]);

        return new JsonResponse($json, 200, [
            'Content-Type' => 'application/json'
        ]);
    }
}
