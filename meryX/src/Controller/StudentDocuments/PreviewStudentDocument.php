<?php

namespace App\Controller\StudentDocuments;

use App\Entity\StudentDocuments;
use App\Entity\User;
use App\Security\StudentProfileAccessService;
use App\Service\StudentDocuments\StudentDocumentStorageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class PreviewStudentDocument extends AbstractController
{
    public function __invoke(
        StudentDocuments $data,
        StudentProfileAccessService $accessService,
        StudentDocumentStorageService $storageService
    ) {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        if (!$accessService->canViewDocument($actor, $data)) {
            return $this->json(['message' => 'Forbidden'], 403);
        }

        try {
            $path = $storageService->resolveAbsolutePath($data);

            $response = new BinaryFileResponse($path);
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_INLINE,
                (string) $data->getTitle()
            );
            $response->headers->set('Content-Type', (string) $data->getMimeType());

            return $response;
        } catch (\Throwable) {
            return $this->json(['message' => 'Document unavailable'], 404);
        }
    }
}
