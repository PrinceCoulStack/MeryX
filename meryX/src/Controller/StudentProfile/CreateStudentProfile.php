<?php
namespace App\Controller\StudentProfile;

use App\Dto\StudentProfileUpsertInput;
use App\Entity\User;
use App\Exception\DomainValidationException;
use App\Security\StudentProfileAccessService;
use App\Service\StudentProfile\StudentProfileResponseBuilder;
use App\Service\StudentProfile\StudentRadarStorageService;
use App\Service\StudentProfile\StudentProfileUpsertService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateStudentProfile extends AbstractController
{
    public function __invoke(
        Request $request,
        ParameterBagInterface $params,
        StudentProfileUpsertService $upsertService,
        StudentProfileAccessService $accessService,
        StudentRadarStorageService $radarStorageService,
        StudentProfileResponseBuilder $responseBuilder
    )
    {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        if (!$accessService->hasRole($actor, 'ROLE_ADMIN')
            && !$accessService->hasRole($actor, 'ROLE_UNIVERSITY')
            && !$accessService->hasRole($actor, 'ROLE_STUDENT')) {
            return $this->json(['message' => 'Forbidden'], 403);
        }

        $input = StudentProfileUpsertInput::fromRequest($request);
        $file = $this->extractProfileImage($request);

        if (!$request->isMethod('POST')) {
            return $this->json(['message' => 'Invalid request method'], 400);
        }

        if ($file instanceof UploadedFile) {
            $input->profileUrl = $this->storeProfileImage($file, $params);
        }

        $bio = $radarStorageService->normalizeBioPayload($input->bio);
        $bio = $radarStorageService->integrateRadarProofFiles($bio, $request);
        $input->bio = $bio;

        if ($accessService->hasRole($actor, 'ROLE_STUDENT')) {
            if ($input->userId !== null && $input->userId !== $actor->getId()) {
                return $this->json([
                    'message' => 'Validation failed',
                    'errors' => ['userId' => ['Students cannot reassign profile ownership']],
                ], 422);
            }

            $input->userId = $actor->getId();
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

            $input->universityId = $actorUniversity->getId();
            $input->academic = $input->academic ?? [];
            $input->academic['universityId'] = $actorUniversity->getId();
        }

        try {
            $studentProfile = $upsertService->create($input);

            return $this->json($responseBuilder->buildItem($studentProfile), Response::HTTP_CREATED);
        } catch (DomainValidationException $exception) {
            return $this->json([
                'message' => $exception->getMessage(),
                'errors' => $exception->getErrors(),
            ], 422);
        } catch (\InvalidArgumentException $exception) {
            return $this->json(['message' => $exception->getMessage()], 400);
        } catch (\Throwable $exception) {
            return $this->json(['message' => 'Unable to create student profile'], 500);
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
