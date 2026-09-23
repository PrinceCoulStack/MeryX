<?php
namespace App\Controller\StudentDocuments;

use App\Entity\StudentDocuments;
use App\Repository\StudentDocumentsRepository;
use App\Repository\StudentProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateStudentDocuments extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, StudentProfileRepository $studentProfileRepo)
    {
        $studentDocument = new StudentDocuments();
        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('POST')) {
            if (!empty($data['studentProfileId'])) {
                $studentProfile = $studentProfileRepo->find($data['studentProfileId']);
                if ($studentProfile) {
                    $studentDocument->setStudentProfileId($studentProfile);
                } else {
                    return $this->json(['message' => 'Invalid student profile ID'], 400);
                }
            } else {
                return $this->json(['message' => 'Student profile ID is required'], 400);
            }

            $studentDocument->setType($data['type'] ?? null);
            $studentDocument->setTitle($data['title'] ?? null);
            $studentDocument->setFileName($data['fileName'] ?? null);
            $studentDocument->setFilePath($data['filePath'] ?? null);
            $studentDocument->setMimeType($data['mimeType'] ?? null);
            $studentDocument->setFileSize($data['fileSize'] ?? null);
            $studentDocument->setIsPublic($data['isPublic'] ?? false);
            $studentDocument->setUploadedAt(new \DateTimeImmutable($data['uploadedAt'] ?? 'now'));
            $studentDocument->setUpdatedAt(new \DateTimeImmutable());

            $em->persist($studentDocument);
            $em->flush();

            return $this->json(['message' => 'Student document created successfully', 'id' => $studentDocument->getId()], 201);
        }
    }
}
