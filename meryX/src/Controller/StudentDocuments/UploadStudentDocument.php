<?php

namespace App\Controller\StudentDocuments;

use App\Entity\StudentDocuments;
use App\Entity\User;
use App\Repository\StudentProfileRepository;
use App\Security\StudentProfileAccessService;
use App\Service\StudentDocuments\StudentDocumentStorageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UploadStudentDocument extends AbstractController
{
    public function __invoke(
        Request $request,
        StudentProfileRepository $studentProfileRepository,
        StudentProfileAccessService $accessService,
        StudentDocumentStorageService $storageService,
        EntityManagerInterface $entityManager,
        int $id
    ) {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $profile = $studentProfileRepository->find($id);
        if ($profile === null) {
            return $this->json(['message' => 'Student profile not found'], 404);
        }

        if (!$accessService->canEditProfile($actor, $profile)) {
            return $this->json(['message' => 'Forbidden'], 403);
        }

        $file = $request->files->get('file');
        if ($file === null) {
            return $this->json(['message' => 'file field is required'], 400);
        }

        $type = (string) ($request->request->get('type') ?? 'other');
        $title = (string) ($request->request->get('title') ?? $file->getClientOriginalName());
        $isPublic = filter_var($request->request->get('isPublic', false), FILTER_VALIDATE_BOOL);

        try {
            $storageService->assertDocumentType($type);
            $stored = $storageService->storeUploadedFile($file);

            $document = new StudentDocuments();
            $document->setStudentProfileId($profile);
            $document->setType(strtolower($type));
            $document->setTitle($title);
            $document->setFileName($stored['storedFileName']);
            $document->setFilePath($stored['relativePath']);
            $document->setMimeType($stored['mimeType']);
            $document->setFileSize((string) $stored['fileSize']);
            $document->setIsPublic($isPublic);
            $document->setUploadedAt(new \DateTimeImmutable());
            $document->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($document);
            $entityManager->flush();

            return $this->json([
                'message' => 'Document uploaded successfully',
                'id' => $document->getId(),
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $exception) {
            return $this->json(['message' => $exception->getMessage()], 422);
        } catch (\Throwable $exception) {
            return $this->json(['message' => 'Unable to upload document'], 500);
        }
    }
}
