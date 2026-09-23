<?php
namespace App\Controller\StudentProfile;

use App\Dto\StudentProfileUpsertInput;
use App\Entity\User;
use App\Exception\DomainValidationException;
use App\Repository\StudentProfileRepository;
use App\Security\StudentProfileAccessService;
use App\Service\StudentProfile\StudentProfileResponseBuilder;
use App\Service\StudentProfile\StudentRadarStorageService;
use App\Service\StudentProfile\StudentProfileUpsertService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateStudentProfile extends AbstractController
{
    public function __invoke(
        Request $request,
        StudentProfileRepository $studentProfileRepository,
        ParameterBagInterface $params,
        StudentProfileUpsertService $upsertService,
        StudentProfileAccessService $accessService,
        StudentRadarStorageService $radarStorageService,
        StudentProfileResponseBuilder $responseBuilder,
        int $id
    )
    {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $studentProfile = $studentProfileRepository->find($id);
        if (!$studentProfile) {
            return $this->json(['message' => 'Student profile not found'], 404);
        }

        if (!$accessService->canEditProfile($actor, $studentProfile)) {
            return $this->json([
                'message' => 'Forbidden',
                'reason' => $accessService->explainEditDenial($actor, $studentProfile),
            ], 403);
        }

        $input = StudentProfileUpsertInput::fromRequest($request);
        $file = $this->extractProfileImage($request);

        if (!($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST'))) {
            return $this->json(['message' => 'Invalid request method'], 400);
        }

        if ($file instanceof UploadedFile) {
            $input->profileUrl = $this->storeProfileImage($file, $params);
        }

        $hasRadarProofFiles = $radarStorageService->hasRadarProofFiles($request);
        if ($input->bio !== null || $hasRadarProofFiles) {
            $currentBio = $radarStorageService->normalizeBioPayload($studentProfile->getBio());
            $incomingBio = $radarStorageService->normalizeBioPayload($input->bio);
            $mergedBio = $radarStorageService->mergeBio($currentBio, $incomingBio);
            $input->bio = $radarStorageService->integrateRadarProofFiles($mergedBio, $request);
        }

        if ($accessService->hasRole($actor, 'ROLE_STUDENT')) {
            if ($input->userId !== null || $input->universityId !== null || ($input->academic !== null && array_key_exists('universityId', $input->academic))) {
                return $this->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'academic.universityId' => ['Students cannot change university scope'],
                        'userId' => ['Students cannot reassign profile ownership'],
                    ],
                ], 422);
            }
        }

        if ($accessService->hasRole($actor, 'ROLE_UNIVERSITY')) {
            $actorUniversity = $accessService->resolveUniversityForActor($actor);
            if ($actorUniversity === null) {
                return $this->json(['message' => 'Forbidden'], 403);
            }

            $requestedUniversityId = $input->universityId;
            if ($requestedUniversityId === null && $input->academic !== null && array_key_exists('universityId', $input->academic)) {
                $requestedUniversityId = $input->academic['universityId'];
            }

            $resolvedRequestedUniversityId = StudentProfileUpsertInput::resolveEntityId($requestedUniversityId);

            if ($requestedUniversityId !== null && $resolvedRequestedUniversityId !== null && $resolvedRequestedUniversityId !== $actorUniversity->getId()) {
                return $this->json(['message' => 'Forbidden'], 403);
            }

            if ($requestedUniversityId !== null) {
                $input->universityId = $actorUniversity->getId();
                $input->academic = $input->academic ?? [];
                $input->academic['universityId'] = $actorUniversity->getId();
            }
        }

        try {
            if ($input->status !== null && in_array(strtolower(trim($input->status)), ['pending', 'approved', 'rejected'], true)) {
                $studentProfile->setStatus(strtolower(trim($input->status)));
            }

            if ($input->isApproved !== null) {
                $studentProfile->setIsApproved((bool) $input->isApproved);
                if ((bool) $input->isApproved) {
                    $studentProfile->setStatus('approved');
                }
            }

            if ($input->reviewReason !== null) {
                $studentProfile->setReviewReason(trim($input->reviewReason) !== '' ? trim($input->reviewReason) : null);
            }

            if ($input->reviewNote !== null) {
                $studentProfile->setReviewNote(trim($input->reviewNote) !== '' ? trim($input->reviewNote) : null);
            }

            $upsertService->update($studentProfile, $input);

            return $this->json($responseBuilder->buildItem($studentProfile), 200);
        } catch (DomainValidationException $exception) {
            return $this->json([
                'message' => $exception->getMessage(),
                'errors' => $exception->getErrors(),
            ], 422);
        } catch (\InvalidArgumentException $exception) {
            return $this->json(['message' => $exception->getMessage()], 400);
        } catch (\Throwable $exception) {
            return $this->json(['message' => 'Unable to update student profile'], 500);
        }
    }

    private function storeProfileImage(UploadedFile $file, ParameterBagInterface $params): string
    {
        $extension = strtolower((string) $file->guessExtension());
        if ($extension === '') {
            $extension = 'bin';
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
        $directory = (string) $params->get('student_profile_directory');

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create student profile image directory');
        }

        $file->move($directory, $fileName);

        return '/uploads/student_profiles/' . $fileName;
    }

    private function extractProfileImage(Request $request): ?UploadedFile
    {
        $directFile = $request->files->get('profileUrl');
        if ($directFile instanceof UploadedFile) {
            return $directFile;
        }

        $walker = function (mixed $node, array $path) use (&$walker): ?UploadedFile {
            if ($node instanceof UploadedFile) {
                $lastSegment = $path[count($path) - 1] ?? null;

                return $lastSegment === 'profileUrl' ? $node : null;
            }

            if (!is_array($node)) {
                return null;
            }

            foreach ($node as $key => $value) {
                $found = $walker($value, [...$path, (string) $key]);
                if ($found instanceof UploadedFile) {
                    return $found;
                }
            }

            return null;
        };

        return $walker($request->files->all(), []);
    }
}
