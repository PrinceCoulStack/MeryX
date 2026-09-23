<?php
namespace App\Controller\StudentDocuments;

use App\Repository\StudentDocumentsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class ListStudentDocuments extends AbstractController
{
    public function __invoke(StudentDocumentsRepository $studentDocumentsRepository, SerializerInterface $serializer)
    {
        $studentDocuments = $studentDocumentsRepository->findAll();
        $json = $serializer->serialize($studentDocuments, 'json', [
            'groups' => ['studentDocument:read']
        ]);

        return new JsonResponse($json, 200, [
            'Content-Type' => 'application/json'
        ]);
    }
}
