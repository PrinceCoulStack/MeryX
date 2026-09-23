<?php
namespace App\Controller\StudentProfile;

use App\Entity\User;
use App\Repository\StudentProfileRepository;
use App\Security\StudentProfileAccessService;
use App\Service\Api\ApiErrorResponseFactory;
use App\Service\StudentProfile\StudentProfileResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class ListStudentProfile extends AbstractController
{
    public function __invoke(
        Request $request,
        StudentProfileRepository $studentProfileRepository,
        StudentProfileAccessService $accessService,
        StudentProfileResponseBuilder $responseBuilder,
        ?ApiErrorResponseFactory $errorResponseFactory = null,
    )
    {
        $errorResponseFactory ??= new ApiErrorResponseFactory();

        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $errorResponseFactory->create(401, 'Unauthorized', 'Missing or invalid JWT token.');
        }

        if ($accessService->hasRole($actor, 'ROLE_ADMIN')) {
            $visibleProfiles = $studentProfileRepository->findAll();
        } elseif ($accessService->hasRole($actor, 'ROLE_UNIVERSITY')) {
            $actorUniversity = $accessService->resolveUniversityForActor($actor);
            $visibleProfiles = $actorUniversity === null
                ? []
                : $studentProfileRepository->findBy(['universityId' => $actorUniversity], ['id' => 'DESC']);
        } elseif ($accessService->hasRole($actor, 'ROLE_STUDENT')) {
            $visibleProfiles = $studentProfileRepository->findBy([
                'userId' => $actor,
                'status' => 'approved',
                'isApproved' => true,
            ], ['id' => 'DESC']);
        } else {
            $visibleProfiles = [];
        }

        $userIdFilter = $this->resolveEntityId($request->query->get('userId'));
        $emailFilter = trim((string) $request->query->get('email', ''));

        if ($userIdFilter !== null || $emailFilter !== '') {
            $visibleProfiles = array_values(array_filter(
                $visibleProfiles,
                static function ($profile) use ($userIdFilter, $emailFilter): bool {
                    if (!$profile instanceof \App\Entity\StudentProfile) {
                        return false;
                    }

                    if ($userIdFilter !== null && $profile->getUserId()?->getId() !== $userIdFilter) {
                        return false;
                    }

                    if ($emailFilter !== '' && strtolower((string) $profile->getUserId()?->getEmail()) !== strtolower($emailFilter)) {
                        return false;
                    }

                    return true;
                }
            ));
        }

        $members = $responseBuilder->buildCollection($visibleProfiles);
        $hydra = filter_var((string) $request->query->get('hydra', 'false'), FILTER_VALIDATE_BOOL);
        if (!$hydra) {
            return $this->json($members);
        }

        return $this->json([
            '@context' => '/api/contexts/StudentProfile',
            '@id' => '/api/studentProfiles',
            '@type' => 'hydra:Collection',
            'hydra:member' => $members,
            'hydra:totalItems' => count($members),
        ]);
    }

    private function resolveEntityId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value) && isset($value['id'])) {
            $value = $value['id'];
        }

        if (is_string($value) && preg_match('/\/(\d+)$/', $value, $matches) === 1) {
            return (int) $matches[1];
        }

        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        if (is_string($value) && ctype_digit($value)) {
            $id = (int) $value;

            return $id > 0 ? $id : null;
        }

        return null;
    }
}
