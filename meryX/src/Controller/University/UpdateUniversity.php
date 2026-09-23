<?php

namespace App\Controller\University;

use App\Entity\User;
use App\Repository\UniversityRepository;
use App\Repository\UserRepository;
use App\Service\Api\ApiErrorResponseFactory;
use App\Service\Workflow\StatusNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class UpdateUniversity extends AbstractController
{
    public function __invoke(
        EntityManagerInterface $em,
        Request $request,
        UniversityRepository $universityRepo,
        UserRepository $userRepo,
        StatusNormalizer $statusNormalizer,
        ApiErrorResponseFactory $errorResponseFactory,
        int $id
    )
    {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $errorResponseFactory->create(401, 'Unauthorized', 'Missing or invalid JWT token.');
        }

        $university = $universityRepo->find($id);
        if (!$university) {
            return $errorResponseFactory->create(404, 'Not Found', 'University not found.');
        }

        $isAdmin = in_array('ROLE_ADMIN', $actor->getRoles(), true);
        $isUniversityUser = in_array('ROLE_UNIVERSITY', $actor->getRoles(), true);
        $actorUniversityId = $universityRepo->findOneBy(['userId' => $actor])?->getId();

        if (!$isAdmin && (!$isUniversityUser || $actorUniversityId !== $university->getId())) {
            return $errorResponseFactory->create(403, 'Forbidden', 'You are not allowed to update this university.');
        }

        $data = json_decode((string) $request->getContent(), true);
        if (!$data && $request->request->all()) {
            $data = $request->request->all();
        }

        if (!is_array($data)) {
            return $errorResponseFactory->create(400, 'Bad Request', 'Invalid university payload.');
        }

        foreach (['userId' => 'user_id', 'isApproved' => 'is_approved', 'logoUrl' => 'logo_url'] as $camel => $snake) {
            if (!array_key_exists($camel, $data) && array_key_exists($snake, $data)) {
                $data[$camel] = $data[$snake];
            }
        }

        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            foreach (['name', 'type', 'accreditationNumber', 'rankingScore', 'description', 'websiteUrl', 'registrationNumber', 'logoUrl'] as $field) {
                if (array_key_exists($field, $data)) {
                    $setter = 'set' . ucfirst($field);
                    $university->$setter($data[$field]);
                }
            }

            if (!empty($data['userId'])) {
                $user = $userRepo->find((int) $data['userId']);
                if ($user) {
                    $university->setUserId($user);
                } else {
                    return $errorResponseFactory->create(400, 'Bad Request', 'Invalid user ID.');
                }
            }

            if (isset($data['status'])) {
                $status = $statusNormalizer->normalizeReviewStatus($data['status'], (string) $university->getStatus());
                $university->setStatus($status);
                $university->setIsApproved($statusNormalizer->isApprovedStatus($status));
                $university->setVerifiedAt($statusNormalizer->isApprovedStatus($status) ? new \DateTimeImmutable() : null);
            }

            if (array_key_exists('isApproved', $data)) {
                $university->setIsApproved((bool) $data['isApproved']);
                if ((bool) $data['isApproved']) {
                    $university->setVerifiedAt(new \DateTimeImmutable());
                    $university->setStatus('approved');
                }
            }

            if (array_key_exists('isDeleted', $data)) {
                $university->setIsDeleted((bool) $data['isDeleted']);
            }

            $university->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->json([
                'id' => $university->getId(),
                'message' => 'University updated successfully',
                'status' => $university->getStatus(),
                'isApproved' => $university->isApproved(),
                'verifiedAt' => $university->getVerifiedAt()?->format(DATE_ATOM),
            ], 200);
        }

        return $errorResponseFactory->create(405, 'Method Not Allowed', 'Unsupported request method.');
    }
}
