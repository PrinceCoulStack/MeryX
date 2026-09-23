<?php
namespace App\Controller\StudentDocuments;

use App\Repository\StudentDocumentsRepository;
use App\Repository\StudentProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateStudentDocuments extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, StudentDocumentsRepository $studentDocumentsRepository, StudentProfileRepository $studentProfileRepo, int $id)
    {
        $studentDocument = $studentDocumentsRepository->find($id);
        if (!$studentDocument) {
            return $this->json(['message' => 'Student document not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            if (isset($data['type'])) {
                $studentDocument->setType($data['type']);
            }
            if (isset($data['title'])) {
                $studentDocument->setTitle($data['title']);
            }
            if (isset($data['fileName'])) {
                $studentDocument->setFileName($data['fileName']);
            }
            if (isset($data['filePath'])) {
                $studentDocument->setFilePath($data['filePath']);
            }
            if (isset($data['mimeType'])) {
                $studentDocument->setMimeType($data['mimeType']);
            }
            if (isset($data['fileSize'])) {
                $studentDocument->setFileSize($data['fileSize']);
            }
            if (isset($data['isPublic'])) {
                $studentDocument->setIsPublic($data['isPublic']);
            }
            if (isset($data['uploadedAt'])) {
                $studentDocument->setUploadedAt(new \DateTimeImmutable($data['uploadedAt']));
            }

            if (!empty($data['studentProfileId'])) {
                $studentProfile = $studentProfileRepo->find($data['studentProfileId']);
                if ($studentProfile) {
                    $studentDocument->setStudentProfileId($studentProfile);
                } else {
                    return $this->json(['message' => 'Invalid student profile ID'], 400);
                }
            }

            $studentDocument->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->json(['message' => 'Student document updated successfully'], 200);
        }
    }
}
