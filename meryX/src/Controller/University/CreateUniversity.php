<?php

namespace App\Controller\University;

use App\Entity\Address;
use App\Entity\University;
use App\Entity\User;
use App\Repository\AddressRepository;
use App\Repository\UserRepository;
use App\Repository\UserTypeRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsController]
class CreateUniversity extends AbstractController
{
    public function __invoke(
        EntityManagerInterface $em,
        Request $request,
        UserRepository $userRepository,
        UserTypeRepository $userTypeRepository,
        AddressRepository $addressRepository,
        UserPasswordHasherInterface $passwordHasher,
        ParameterBagInterface $params
    ) {
        $data = json_decode($request->getContent(), true);
        if (!$data && $request->request->all()) {
            $data = $request->request->all();
        }

        if (!is_array($data)) {
            return $this->json(['message' => 'Invalid university payload'], 400);
        }

        $resolveEntityId = static function ($value): ?int {
            if ($value === null || $value === '' || $value === []) {
                return null;
            }

            if (is_array($value) && isset($value['id'])) {
                $value = $value['id'];
            }

            if (is_string($value) && preg_match('/\/(\d+)$/', $value, $matches)) {
                return (int) $matches[1];
            }

            if (is_scalar($value)) {
                $intValue = (int) trim((string) $value);
                return $intValue > 0 ? $intValue : null;
            }

            return null;
        };

        $addressData = $data['address'] ?? null;
        $address = null;
        $user = null;

        $userIdRaw = $data['userId'] ?? null;
        $userIdEmail = '';
        if (is_array($userIdRaw)) {
            $userIdEmail = trim((string) ($userIdRaw['email'] ?? ''));
            if ($userIdEmail === '' && isset($userIdRaw['user']) && is_array($userIdRaw['user'])) {
                $userIdEmail = trim((string) ($userIdRaw['user']['email'] ?? ''));
            }
        } elseif (is_string($userIdRaw) && filter_var(trim($userIdRaw), FILTER_VALIDATE_EMAIL)) {
            $userIdEmail = trim($userIdRaw);
        }

        // Support form-data dot keys like userId.email and user.email
        if ($userIdEmail === '') {
            $userIdEmail = trim((string) ($data['userId.email'] ?? $data['user.email'] ?? ''));
        }

        $requestedUserId = $resolveEntityId($data['userId'] ?? $data['userId.id'] ?? $data['user.id'] ?? null);
        if ($requestedUserId !== null) {
            $user = $userRepository->find($requestedUserId);
            if (!$user) {
                return $this->json(['message' => 'Invalid userId'], 400);
            }
            $address = $user->getAddressId();
        } elseif ($userIdEmail !== '') {
            $existingUser = $userRepository->findOneBy(['email' => $userIdEmail]);
            if ($existingUser) {
                $user = $existingUser;
                $address = $user->getAddressId();
            }
        } elseif (!empty($data['email'])) {
            $existingUser = $userRepository->findOneBy(['email' => (string) $data['email']]);
            if ($existingUser) {
                $user = $existingUser;
                $address = $user->getAddressId();
            }
        }

        if ($address === null && is_array($addressData) && !empty($addressData)) {
            $address = new Address();
            $address->setCity($addressData['city'] ?? '');
            $address->setState($addressData['state'] ?? '');
            $address->setCountry($addressData['country'] ?? '');
            $address->setLatitude($addressData['latitude'] ?? '0');
            $address->setLongitude($addressData['longitude'] ?? '0');
            $address->setCreateAt(new DateTimeImmutable());
            $address->setUpdatedAt(new DateTimeImmutable());
            $em->persist($address);
        } elseif ($address === null) {
            $requestedAddressId = $resolveEntityId($data['addressId'] ?? null);
            if ($requestedAddressId !== null) {
                $address = $addressRepository->find($requestedAddressId);
            }
        }

        if (!$address && $user === null) {
            return $this->json(['message' => 'Address data is required'], 400);
        }

        $userType = $userTypeRepository->findOneBy(['name' => 'UNIVERSITY'])
            ?? $userTypeRepository->findOneBy(['name' => 'University']);

        if (!$userType) {
            return $this->json(['message' => 'UNIVERSITY user type not found'], 400);
        }

        $email = trim((string) ($data['email'] ?? $data['user.email'] ?? ''));
        if ($email === '' && $userIdEmail !== '') {
            $email = $userIdEmail;
        }

        if ($user === null) {
            if ($email === '') {
                return $this->json(['message' => 'Email is required to create a university user'], 400);
            }

            $existingEmailUser = $userRepository->findOneBy(['email' => $email]);
            if ($existingEmailUser) {
                $user = $existingEmailUser;
                if ($user->getAddressId() === null && $address !== null) {
                    $user->setAddressId($address);
                }
                if ($user->getUserTypeId() === null) {
                    $user->setUserTypeId($userType);
                }
                $user->setUpdatedAt(new DateTimeImmutable());
            }
        }

        if ($user === null) {
            $user = new User();
            $user->setEmail($email);
            $user->setPassword($passwordHasher->hashPassword($user, (string) ($data['password'] ?? 'Password123!')));
            $user->setPhone((string) ($data['phone'] ?? ''));
            $user->setStatus((string) ($data['status'] ?? 'active'));
            $user->setIsActived(true);
            $user->setCreateAt(new DateTimeImmutable());
            $user->setUpdatedAt(new DateTimeImmutable());
            $user->setLastLoginAt(new DateTimeImmutable());
            $user->setUserTypeId($userType);
            $user->setAddressId($address);
            $em->persist($user);
        } else {
            if ($user->getAddressId() === null && $address !== null) {
                $user->setAddressId($address);
            }
            if ($user->getUserTypeId() === null) {
                $user->setUserTypeId($userType);
            }
            $user->setUpdatedAt(new DateTimeImmutable());
        }

        $university = new University();
        $university->setName((string) ($data['name'] ?? ''));
        $university->setType((string) ($data['type'] ?? ''));
        $university->setAccreditationNumber((string) ($data['accreditationNumber'] ?? ''));
        $university->setRankingScore((string) ($data['rankingScore'] ?? '0'));
        $university->setDescription((string) ($data['description'] ?? ''));
        $university->setWebsiteUrl((string) ($data['websiteUrl'] ?? ''));
        $university->setStatus((string) strtolower((string) ($data['status'] ?? 'pending')));
        $university->setRegistrationNumber((string) ($data['registrationNumber'] ?? ''));
        $university->setIsApproved(false);
        $university->setIsDeleted(false);
        $university->setCreatedAt(new DateTimeImmutable());
        $university->setUpdatedAt(new DateTimeImmutable());
        $university->setVerifiedAt(null);
        $university->setUserId($user);
        $university->setAddressId($address);

        $logoUrl = $data['logoUrl'] ?? null;
        if (is_string($logoUrl) && $logoUrl !== '') {
            $university->setLogoUrl($logoUrl);
        }

        $em->persist($university);
        $em->flush();

        return $this->json([
            'message' => 'University registered successfully',
            'id' => $university->getId(),
            'status' => $university->getStatus(),
            'isApproved' => $university->isApproved(),
            'userId' => $user->getId(),
            'addressId' => $user->getAddressId()?->getId(),
            'email' => $user->getEmail(),
            'country' => $user->getAddressId()?->getCountry(),
        ], 201);
    }
}
