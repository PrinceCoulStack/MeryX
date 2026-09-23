<?php

namespace App\Controller\Users;

use App\Entity\User;
use App\Repository\AddressRepository;
use App\Repository\UserRepository;
use App\Repository\UserTypeRepository;
use App\Service\Api\ApiErrorResponseFactory;
use App\Service\User\PasswordPolicy;
use App\Service\Workflow\StatusNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsController]
final class UpdateUser extends AbstractController
{
    public function __invoke(
        UserRepository $repo,
        EntityManagerInterface $em,
        Request $request,
        UserTypeRepository $userTypeRepository,
        AddressRepository $addressRepository,
        StatusNormalizer $statusNormalizer,
        ApiErrorResponseFactory $errorResponseFactory,
        UserPasswordHasherInterface $passwordHasher,
        int $id
    ) {
        $actor = $this->getUser();
        if (!$actor instanceof User) {
            return $errorResponseFactory->create(401, 'Unauthorized', 'Missing or invalid JWT token.');
        }

        $user = $repo->find($id);
        if (!$user instanceof User) {
            return $errorResponseFactory->create(404, 'Not Found', 'User not found.');
        }

        $isAdmin = in_array('ROLE_ADMIN', $actor->getRoles(), true);
        if (!$isAdmin && $actor->getId() !== $user->getId()) {
            return $errorResponseFactory->create(403, 'Forbidden', 'You are not allowed to update this user.');
        }

        if (!$request->isMethod('POST') && !$request->isMethod('PUT') && !$request->isMethod('PATCH')) {
            return $errorResponseFactory->create(405, 'Method Not Allowed', 'Unsupported request method.');
        }

        $data = json_decode((string) $request->getContent(), true);
        if (!is_array($data) && $request->request->all() !== []) {
            $data = $request->request->all();
        }

        if (!is_array($data)) {
            return $errorResponseFactory->create(400, 'Bad Request', 'Invalid user payload.');
        }

        foreach (['userTypeId' => 'user_type_id', 'addressId' => 'address_id', 'isActived' => 'is_actived', 'lastLoginAt' => 'last_login_at'] as $camel => $snake) {
            if (!array_key_exists($camel, $data) && array_key_exists($snake, $data)) {
                $data[$camel] = $data[$snake];
            }
        }

        if (array_key_exists('email', $data)) {
            $user->setEmail((string) $data['email']);
        }

        if (array_key_exists('password', $data)) {
            $password = trim((string) $data['password']);
            if ($password !== '') {
                $passwordPolicyErrors = PasswordPolicy::validate($password);
                if ($passwordPolicyErrors !== []) {
                    return $errorResponseFactory->create(422, 'Unprocessable Entity', 'Password does not meet requirements.', [
                        'errors' => ['password' => $passwordPolicyErrors],
                    ]);
                }
                $user->setPassword($passwordHasher->hashPassword($user, $password));
            }
        }

        if (array_key_exists('phone', $data)) {
            $user->setPhone((string) $data['phone']);
        }

        if (array_key_exists('status', $data)) {
            $status = $statusNormalizer->normalizeAccountStatus($data['status'], (string) $user->getStatus());
            $user->setStatus($status);
            $user->setIsActived($statusNormalizer->isActiveAccount($status));
        }

        if (array_key_exists('isActived', $data)) {
            $user->setIsActived((bool) $data['isActived']);
            if ((bool) $data['isActived']) {
                $user->setStatus('active');
            } elseif (strtolower((string) $user->getStatus()) === 'active') {
                $user->setStatus('inactive');
            }
        }

        if (array_key_exists('userTypeId', $data)) {
            $userTypeId = $this->resolveEntityId($data['userTypeId']);
            if ($userTypeId === null) {
                return $errorResponseFactory->create(400, 'Bad Request', 'Invalid userTypeId.');
            }

            $userType = $userTypeRepository->find($userTypeId);
            if ($userType === null) {
                return $errorResponseFactory->create(400, 'Bad Request', 'Invalid userTypeId.');
            }

            $user->setUserTypeId($userType);
        }

        if (array_key_exists('addressId', $data)) {
            $addressId = $this->resolveEntityId($data['addressId']);
            if ($addressId === null) {
                return $errorResponseFactory->create(400, 'Bad Request', 'Invalid addressId.');
            }

            $address = $addressRepository->find($addressId);
            if ($address === null) {
                return $errorResponseFactory->create(400, 'Bad Request', 'Invalid addressId.');
            }

            $user->setAddressId($address);
        }

        if (array_key_exists('lastLoginAt', $data) && $data['lastLoginAt'] !== null && $data['lastLoginAt'] !== '') {
            try {
                $user->setLastLoginAt(new \DateTimeImmutable((string) $data['lastLoginAt']));
            } catch (\Throwable) {
                return $errorResponseFactory->create(400, 'Bad Request', 'Invalid lastLoginAt value.');
            }
        }

        $user->setUpdatedAt(new \DateTimeImmutable());
        $em->flush();

        return $this->json([
            'message' => 'User updated successfully',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'phone' => $user->getPhone(),
                'status' => $user->getStatus(),
                'isActived' => $user->isActived(),
                'userTypeId' => $user->getUserTypeId()?->getId(),
                'addressId' => $user->getAddressId()?->getId(),
                'updatedAt' => $user->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
            ],
        ], Response::HTTP_OK);
    }

    private function resolveEntityId(mixed $value): ?int
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        if (is_array($value)) {
            if (isset($value['id'])) {
                $value = $value['id'];
            } elseif (isset($value['@id'])) {
                $value = $value['@id'];
            }
        }

        if (is_string($value) && preg_match('/\/(\d+)$/', $value, $matches) === 1) {
            return (int) $matches[1];
        }

        if (is_scalar($value)) {
            $raw = trim((string) $value);
            if ($raw === '' || $raw === 'null' || $raw === 'undefined') {
                return null;
            }

            $id = (int) $raw;

            return $id > 0 ? $id : null;
        }

        return null;
    }
}
