<?php

namespace App\Controller\Company;

use App\Entity\User;
use App\Repository\CompanyRepository;
use App\Repository\UserRepository;
use App\Service\Api\ApiErrorResponseFactory;
use App\Service\Workflow\StatusNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateCompany extends AbstractController
{
    public function __invoke(
        EntityManagerInterface $em,
        Request $request,
        CompanyRepository $repository,
        UserRepository $userRepository,
        StatusNormalizer $statusNormalizer,
        ApiErrorResponseFactory $errorResponseFactory,
        int $id
    )
    {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $errorResponseFactory->create(401, 'Unauthorized', 'Missing or invalid JWT token.');
        }

        $company = $repository->find($id);
        if (!$company) {
            return $errorResponseFactory->create(404, 'Not Found', 'Company not found.');
        }

        $isAdmin = in_array('ROLE_ADMIN', $actor->getRoles(), true);
        $isCompanyUser = in_array('ROLE_COMPANY', $actor->getRoles(), true);
        $actorCompanyId = $repository->findOneBy(['userId' => $actor])?->getId();

        if (!$isAdmin && (!$isCompanyUser || $actorCompanyId !== $company->getId())) {
            return $errorResponseFactory->create(403, 'Forbidden', 'You are not allowed to update this company.');
        }

        $data = json_decode((string) $request->getContent(), true);
        if (!is_array($data)) {
            return $errorResponseFactory->create(400, 'Bad Request', 'Invalid company payload.');
        }

        foreach (['userId' => 'user_id', 'isApproved' => 'is_approved', 'verifiedAt' => 'verified_at', 'taxId' => 'tax_id'] as $camel => $snake) {
            if (!array_key_exists($camel, $data) && array_key_exists($snake, $data)) {
                $data[$camel] = $data[$snake];
            }
        }

        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            foreach (['name', 'logo', 'sector', 'rankingScore', 'description', 'websiteUrl', 'status', 'registrationNumber', 'taxId'] as $field) {
                if (array_key_exists($field, $data)) {
                    $setter = 'set' . ucfirst($field);
                    $company->$setter($data[$field]);
                }
            }

            if (!empty($data['userId'])) {
                $user = $userRepository->find((int) $data['userId']);
                if ($user) {
                    $company->setUserId($user);
                    $company->setEmail($user->getEmail());
                } else {
                    return $errorResponseFactory->create(400, 'Bad Request', 'Invalid user ID.');
                }
            } elseif (!empty($data['email'])) {
                $user = $userRepository->findOneBy(['email' => $data['email']]);
                if ($user) {
                    $company->setUserId($user);
                    $company->setEmail($user->getEmail());
                }
            }

            if (isset($data['status'])) {
                $status = $statusNormalizer->normalizeReviewStatus($data['status'], (string) $company->getStatus());
                $company->setStatus($status);
                if ($statusNormalizer->isApprovedStatus($status)) {
                    $company->setIsApproved(true);
                }
            }

            if (isset($data['isApproved'])) {
                $company->setIsApproved((bool) $data['isApproved']);
                if ((bool) $data['isApproved']) {
                    $company->setStatus('approved');
                }
            }

            if (isset($data['verifiedAt'])) {
                try {
                    $company->setVerifiedAt(new \DateTimeImmutable((string) $data['verifiedAt']));
                } catch (\Throwable) {
                    return $errorResponseFactory->create(400, 'Bad Request', 'Invalid verifiedAt value.');
                }
            }

            $company->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->json([
                'id' => $company->getId(),
                'message' => 'Company updated successfully',
                'status' => $company->getStatus(),
                'isApproved' => $company->isApproved(),
            ], 200);
        }

        return $errorResponseFactory->create(405, 'Method Not Allowed', 'Unsupported request method.');
    }
}
