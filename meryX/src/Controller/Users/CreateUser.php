<?php
namespace App\Controller\Users;

use App\Dto\StudentProfileUpsertInput;
use App\Entity\StudentProfile;
use App\Entity\User;
use App\Exception\DomainValidationException;
use App\Repository\AddressRepository;
use App\Repository\StudentProfileRepository;
use App\Repository\UniversityRepository;
use App\Repository\UserRepository;
use App\Repository\UserTypeRepository;
use App\Security\StudentProfileAccessService;
use App\Service\StudentProfile\StudentProfileResponseBuilder;
use App\Service\StudentProfile\StudentProfileUpsertService;
use App\Service\User\PasswordPolicy;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsController]
class CreateUser
{
    public function __invoke(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository, UserTypeRepository $userTypeRepository, AddressRepository $addressRepository, StudentProfileRepository $studentProfileRepository, StudentProfileUpsertService $studentProfileUpsertService, StudentProfileResponseBuilder $studentProfileResponseBuilder, UniversityRepository $universityRepository, ?Security $security = null, ?StudentProfileAccessService $accessService = null)
    {
        $normalizeStudentPayload = static function (mixed $payload): array {
            if (!is_array($payload) || !array_key_exists('studentProfiles', $payload)) {
                return is_array($payload) ? $payload : [];
            }

            $studentProfiles = $payload['studentProfiles'];
            unset($payload['studentProfiles']);

            if (is_array($studentProfiles)) {
                return array_replace($payload, $studentProfiles);
            }

            if (is_string($studentProfiles)) {
                $decoded = json_decode($studentProfiles, true);
                if (is_array($decoded)) {
                    return array_replace($payload, $decoded);
                }
            }

            return $payload;
        };

        $extractBioPayload = static function (mixed $bio): array {
            if (is_array($bio)) {
                return $bio;
            }

            if (is_string($bio)) {
                $decoded = json_decode($bio, true);

                return is_array($decoded) ? $decoded : [];
            }

            return [];
        };

        $extractString = static function (mixed $value): ?string {
            if ($value === null || !is_scalar($value)) {
                return null;
            }

            $normalized = trim((string) $value);

            return $normalized !== '' ? $normalized : null;
        };

        $data = json_decode($request->getContent(), true);
        if (!is_array($data) && $request->request->all() !== []) {
            $data = $request->request->all();
        }

        if (!is_array($data)) {
            return new Response('Invalid user payload', Response::HTTP_BAD_REQUEST);
        }

        $data = $normalizeStudentPayload($data);

        $user = new User();

        $resolveEntityId = static function ($value): ?int {
            if ($value === null || $value === '' || $value === []) {
                return null;
            }

            if (is_array($value) && isset($value['id'])) {
                $value = $value['id'];
            }

            if (is_scalar($value)) {
                $value = trim((string) $value);
                if (preg_match('/\/(\d+)$/', $value, $matches) === 1) {
                    return (int) $matches[1];
                }

                if ($value === '' || $value === 'null' || $value === 'undefined') {
                    return null;
                }

                $intValue = (int) $value;
                return $intValue > 0 ? $intValue : null;
            }

            return null;
        };

        $userTypeId = $resolveEntityId($data['userTypeId'] ?? null);
        $userType = $userTypeId !== null ? $userTypeRepository->find($userTypeId) : null;
        $addressId = $resolveEntityId($data['addressId'] ?? null);
        $address = $addressId !== null ? $addressRepository->find($addressId) : null;
        $isStudentFlow = $userType !== null && in_array(strtoupper(trim((string) $userType->getRoleName())), ['STUDENT', 'ROLE_STUDENT'], true);

        if ($request->isMethod('POST')) {
            $email = trim((string) ($data['email'] ?? ''));
            $password = (string) ($data['password'] ?? '');
            $phone = trim((string) ($data['phone'] ?? ''));

            $verificationPayload = is_array($data['verificationData'] ?? null) ? $data['verificationData'] : [];
            $academicPayload = is_array($data['academic'] ?? null) ? $data['academic'] : [];
            $universityPayload = is_array($data['university'] ?? null) ? $data['university'] : [];
            $bioPayload = $extractBioPayload($data['bio'] ?? null);
            $bioAcademicPayload = is_array($bioPayload['academic'] ?? null) ? $bioPayload['academic'] : [];
            $universityIdRaw = $data['universityId']
                ?? $data['university.id']
                ?? $academicPayload['universityId']
                ?? $universityPayload['id']
                ?? $verificationPayload['universityId']
                ?? null;
            $universityName = trim((string) (
                $data['universityName']
                ?? $data['university.name']
                ?? $academicPayload['universityName']
                ?? $universityPayload['name']
                ?? $verificationPayload['universityName']
                ?? ''
            ));
            $universityEmail = trim((string) (
                $data['universityEmail']
                ?? $data['university.email']
                ?? $academicPayload['universityEmail']
                ?? $universityPayload['email']
                ?? $verificationPayload['universityEmail']
                ?? ''
            ));

            $programValue = $extractString(
                $data['program']
                ?? $academicPayload['program']
                ?? $bioAcademicPayload['program']
                ?? $verificationPayload['program']
                ?? null
            );
            $levelValue = $extractString(
                $data['level']
                ?? $academicPayload['level']
                ?? $bioAcademicPayload['level']
                ?? $verificationPayload['level']
                ?? null
            );

            $verificationData = [
                'email' => $email,
                'phone' => $phone,
                'program' => $programValue,
                'level' => $levelValue,
                'universityName' => $universityName !== '' ? $universityName : null,
                'universityEmail' => $universityEmail !== '' ? $universityEmail : null,
            ];

            $verifiedUniversity = null;
            if ($isStudentFlow) {
                // a university admin adding one of their own students shouldn't need to resend their university's name/email
                $actor = $security?->getUser();
                if ($actor instanceof User && $accessService !== null && $accessService->hasRole($actor, 'ROLE_UNIVERSITY')) {
                    $actorUniversity = $accessService->resolveUniversityForActor($actor);
                    if ($actorUniversity !== null) {
                        $verifiedUniversity = $actorUniversity;
                        $universityName = trim((string) $actorUniversity->getName());
                        $universityEmail = trim((string) ($actorUniversity->getEmail() ?? ''));
                    }
                }

                $resolvedUniversityId = $resolveEntityId($universityIdRaw);
                $universityById = $verifiedUniversity === null && $resolvedUniversityId !== null ? $universityRepository->find($resolvedUniversityId) : null;

                if ($universityById !== null) {
                    if ($universityName === '') {
                        $universityName = trim((string) $universityById->getName());
                    }
                    if ($universityEmail === '') {
                        $universityEmail = trim((string) ($universityById->getEmail() ?? ''));
                    }
                    $verifiedUniversity = $universityById;
                }

                if ($verifiedUniversity === null && ($universityName === '' || $universityEmail === '')) {
                    return new Response('universityName and universityEmail are required for student registration (or provide a valid universityId)', Response::HTTP_BAD_REQUEST);
                }

                if ($verifiedUniversity === null && $universityName !== '' && $universityEmail !== '') {
                    $verifiedUniversity = $universityRepository->findOneByExactNameAndEmail($universityName, $universityEmail);
                    if ($verifiedUniversity === null) {
                        return new Response('University validation failed: name/email pair not found', Response::HTTP_BAD_REQUEST);
                    }
                }

                $verificationData['universityName'] = $universityName !== '' ? $universityName : null;
                $verificationData['universityEmail'] = $universityEmail !== '' ? $universityEmail : null;
            }

            if ($email === '') {
                return new Response('Email is required', Response::HTTP_BAD_REQUEST);
            }

            if ($password === '') {
                return new Response('Password is required', Response::HTTP_BAD_REQUEST);
            }

            $passwordPolicyErrors = PasswordPolicy::validate($password);
            if ($passwordPolicyErrors !== []) {
                return new \Symfony\Component\HttpFoundation\JsonResponse([
                    'message' => 'Password does not meet requirements.',
                    'errors' => ['password' => $passwordPolicyErrors],
                ], 422);
            }

            if ($phone === '') {
                return new Response('Phone is required', Response::HTTP_BAD_REQUEST);
            }

            $existingUser = $userRepository->findOneBy(['email' => $email]);

            if ($existingUser !== null && !$isStudentFlow) {
                return new Response('Email already exists', Response::HTTP_CONFLICT);
            }

            if ($existingUser instanceof User) {
                $user = $existingUser;
            }

            $user->setEmail($email);
            if ($password !== '' && ($existingUser === null || $user->getPassword() === null)) {
                $user->setPassword($passwordHasher->hashPassword($user, $password));
            }
            $user->setPhone($phone);

            $user->setUserTypeId($userType);
            $user->setAddressId($address);

            $user->setStatus($isStudentFlow ? 'pending' : ($data['status'] ?? 'active'));
            $user->setIsActived($data['isActived'] ?? true);

            if ($existingUser === null) {
                $user->setCreateAt(new DateTimeImmutable());
                $em->persist($user);
            }
            $user->setUpdatedAt(new DateTimeImmutable());
            $user->setLastLoginAt(new DateTimeImmutable());

            if (!$isStudentFlow) {
                $em->flush();

                return new Response('User Registered Successfully', Response::HTTP_CREATED);
            }

            $connection = $em->getConnection();
            $studentProfileInput = StudentProfileUpsertInput::fromArray($data);
            $startedTransaction = !$connection->isTransactionActive();

            try {
                if ($startedTransaction) {
                    $em->beginTransaction();
                }

                $em->flush();

                $studentProfileInput->userId = $user->getId();
                if ($verifiedUniversity !== null) {
                    $studentProfileInput->universityId = $verifiedUniversity->getId();
                }

                if ($studentProfileInput->fullName === null || trim($studentProfileInput->fullName) === '') {
                    $studentProfileInput->fullName = $this->fallbackFullName($email);
                }

                if ($studentProfileInput->gpa === null || trim($studentProfileInput->gpa) === '') {
                    $studentProfileInput->gpa = '0.00';
                }

                if ($studentProfileInput->gender === null || trim($studentProfileInput->gender) === '') {
                    $studentProfileInput->gender = 'prefer_not_to_say';
                }

                if ($studentProfileInput->program === null && $programValue !== null) {
                    $studentProfileInput->program = $programValue;
                }

                $existingStudentProfile = $studentProfileRepository->findOneBy(['userId' => $user]);

                if ($existingStudentProfile instanceof StudentProfile) {
                    $studentProfile = $studentProfileUpsertService->update($existingStudentProfile, $studentProfileInput);
                } else {
                    $studentProfile = $studentProfileUpsertService->create($studentProfileInput);
                }

                $studentProfile->setStatus('pending');
                $studentProfile->setIsApproved(false);
                $studentProfile->setReviewReason(null);
                $studentProfile->setReviewNote(null);
                $studentProfile->setReviewedAt(null);
                $studentProfile->setVerificationData($verificationData);
                $studentProfile->setUpdatedAt(new DateTimeImmutable());
                $em->flush();

                if ($startedTransaction) {
                    $em->commit();
                }

                return new \Symfony\Component\HttpFoundation\JsonResponse([
                    'message' => 'User Registered Successfully',
                    'userId' => $user->getId(),
                    'studentProfile' => $studentProfileResponseBuilder->buildItem($studentProfile),
                ], Response::HTTP_CREATED);
            } catch (DomainValidationException $exception) {
                if ($startedTransaction) {
                    $em->rollback();
                }

                return new \Symfony\Component\HttpFoundation\JsonResponse([
                    'message' => $exception->getMessage(),
                    'errors' => $exception->getErrors(),
                ], 422);
            } catch (\InvalidArgumentException $exception) {
                if ($startedTransaction) {
                    $em->rollback();
                }

                return new \Symfony\Component\HttpFoundation\JsonResponse([
                    'message' => $exception->getMessage(),
                ], Response::HTTP_BAD_REQUEST);
            } catch (\Throwable) {
                if ($startedTransaction) {
                    $em->rollback();
                }

                return new \Symfony\Component\HttpFoundation\JsonResponse([
                    'message' => 'Unable to register student user',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return new Response('Invalid request method', Response::HTTP_BAD_REQUEST);
    }

    private function fallbackFullName(string $email): string
    {
        $localPart = explode('@', $email)[0] ?? '';
        $clean = trim(str_replace(['.', '_', '-'], ' ', $localPart));

        return $clean !== '' ? ucwords($clean) : 'Student';
    }
}
