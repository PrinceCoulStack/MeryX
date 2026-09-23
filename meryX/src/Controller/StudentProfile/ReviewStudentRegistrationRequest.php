<?php

namespace App\Controller\StudentProfile;

use App\Entity\StudentProfile;
use App\Entity\User;
use App\Security\StudentProfileAccessService;
use App\Service\Api\ApiErrorResponseFactory;
use App\Service\StudentProfile\StudentProfileResponseBuilder;
use App\Service\Workflow\StatusNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class ReviewStudentRegistrationRequest extends AbstractController
{
    public function __invoke(
        StudentProfile $data,
        Request $request,
        StudentProfileAccessService $accessService,
        StudentProfileResponseBuilder $responseBuilder,
        EntityManagerInterface $entityManager,
        ?StatusNormalizer $statusNormalizer = null,
        ?ApiErrorResponseFactory $errorResponseFactory = null,
    ) {
        $statusNormalizer ??= new StatusNormalizer();
        $errorResponseFactory ??= new ApiErrorResponseFactory();

        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $errorResponseFactory->create(401, 'Unauthorized', 'Missing or invalid JWT token.');
        }

        if (!$request->isMethod('PATCH')) {
            return $errorResponseFactory->create(405, 'Method Not Allowed', 'Unsupported request method.');
        }

        if ($accessService->hasRole($actor, 'ROLE_UNIVERSITY') && !$accessService->canEditProfile($actor, $data)) {
            return $errorResponseFactory->create(403, 'Forbidden', 'You are not allowed to review this profile.');
        }

        if (!$accessService->hasRole($actor, 'ROLE_ADMIN') && !$accessService->hasRole($actor, 'ROLE_UNIVERSITY')) {
            return $errorResponseFactory->create(403, 'Forbidden', 'Only admin and university users can review profiles.');
        }

        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload) && $request->request->all() !== []) {
            $payload = $request->request->all();
        }

        if (!is_array($payload)) {
            return $errorResponseFactory->create(400, 'Bad Request', 'Invalid review payload.');
        }

        if (!array_key_exists('reviewReason', $payload) && array_key_exists('review_reason', $payload)) {
            $payload['reviewReason'] = $payload['review_reason'];
        }

        if (!array_key_exists('reviewNote', $payload) && array_key_exists('review_note', $payload)) {
            $payload['reviewNote'] = $payload['review_note'];
        }

        $status = $statusNormalizer->normalizeReviewStatus($payload['status'] ?? '', '');
        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            return $errorResponseFactory->create(422, 'Validation Failed', 'Status must be pending, approved or rejected.', [
                'errors' => ['status' => ['Status must be pending, approved or rejected']],
            ]);
        }

        $reviewReason = isset($payload['reviewReason']) ? trim((string) $payload['reviewReason']) : null;
        $reviewNote = isset($payload['reviewNote']) ? trim((string) $payload['reviewNote']) : null;

        if ($status === 'rejected' && ($reviewReason === null || $reviewReason === '')) {
            return $errorResponseFactory->create(422, 'Validation Failed', 'reviewReason is required when status is rejected.', [
                'errors' => ['reviewReason' => ['reviewReason is required when status is rejected']],
            ]);
        }

        $data->setStatus($status);
        $data->setIsApproved($status === 'approved');
        $data->setReviewReason($status === 'rejected' ? $reviewReason : null);
        $data->setReviewNote($reviewNote !== '' ? $reviewNote : null);
        $data->setReviewedAt(new \DateTimeImmutable());
        $data->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return $this->json($responseBuilder->buildItem($data), 200);
    }
}
