<?php

namespace App\Controller\Company;

use App\Entity\Address;
use App\Entity\Company;
use App\Entity\User;
use App\Repository\AddressRepository;
use App\Repository\UserRepository;
use App\Repository\UserTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsController]
class CreateCompany extends AbstractController
{
    public function __invoke(
        EntityManagerInterface $em,
        Request $request,
        UserRepository $userRepository,
        UserTypeRepository $userTypeRepository,
        AddressRepository $addressRepository,
        UserPasswordHasherInterface $passwordHasher
    )
    {
        $data = json_decode($request->getContent(), true);
        if (!$data && $request->request->all()) {
            $data = $request->request->all();
        }

        if (!is_array($data)) {
            return $this->json(['message' => 'Invalid company payload'], 400);
        }

        if (!$request->isMethod('POST')) {
            return $this->json(['message' => 'Invalid request method'], 400);
        }

        // 1) Address first
        $address = null;
        $addressData = $data['address'] ?? null;
        if (is_array($addressData) && !empty($addressData)) {
            $address = new Address();
            $address->setCity((string) ($addressData['city'] ?? ''));
            $address->setState((string) ($addressData['state'] ?? ''));
            $address->setCountry((string) ($addressData['country'] ?? ''));
            $address->setLatitude((string) ($addressData['latitude'] ?? '0'));
            $address->setLongitude((string) ($addressData['longitude'] ?? '0'));
            $address->setCreateAt(new \DateTimeImmutable());
            $address->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($address);
        } elseif (!empty($data['addressId'])) {
            $address = $addressRepository->find((int) $data['addressId']);
        }

        if (!$address) {
            return $this->json(['message' => 'Address data is required'], 400);
        }

        // 2) User second
        $user = null;
        if (!empty($data['userId'])) {
            $user = $userRepository->find((int) $data['userId']);
            if ($user) {
                $user->setAddressId($address);
            }
        }

        if (!$user) {
            if (empty($data['email'])) {
                return $this->json(['message' => 'Email is required to create a company user'], 400);
            }

            $existingUser = $userRepository->findOneBy(['email' => (string) $data['email']]);
            if ($existingUser) {
                $user = $existingUser;
                $user->setAddressId($address);
            } else {
                $companyUserType = $userTypeRepository->findOneBy(['name' => 'COMPANY'])
                    ?? $userTypeRepository->findOneBy(['name' => 'Company']);

                if (!$companyUserType) {
                    return $this->json(['message' => 'COMPANY user type not found'], 400);
                }

                $user = new User();
                $user->setEmail((string) $data['email']);
                $user->setPassword($passwordHasher->hashPassword($user, (string) ($data['password'] ?? 'Password123!')));
                $user->setPhone((string) ($data['phone'] ?? ''));
                $user->setStatus((string) ($data['userStatus'] ?? 'active'));
                $user->setIsActived(true);
                $user->setCreateAt(new \DateTimeImmutable());
                $user->setUpdatedAt(new \DateTimeImmutable());
                $user->setLastLoginAt(new \DateTimeImmutable());
                $user->setUserTypeId($companyUserType);
                $user->setAddressId($address);
                $em->persist($user);
            }
        }

        // 3) Company third
        $company = new Company();
        $status = strtolower((string) ($data['status'] ?? 'pending'));
        $company->setName((string) ($data['name'] ?? ''));
        $company->setLogo((string) ($data['logo'] ?? ''));
        $company->setSector((string) ($data['sector'] ?? ''));
        $company->setRankingScore((string) ($data['rankingScore'] ?? '0'));
        $company->setDescription((string) ($data['description'] ?? ''));
        $company->setWebsiteUrl((string) ($data['websiteUrl'] ?? ''));
        $company->setStatus(in_array($status, ['pending', 'approved', 'rejected'], true) ? $status : 'pending');
        $company->setIsApproved($status === 'approved');
        $company->setRegistrationNumber((string) ($data['registrationNumber'] ?? ''));
        $company->setTaxId((string) ($data['taxId'] ?? ''));
        $company->setVerifiedAt(new \DateTimeImmutable($data['verifiedAt'] ?? 'now'));
        $company->setCreatedAt(new \DateTimeImmutable());
        $company->setUpdatedAt(new \DateTimeImmutable());
        $company->setUserId($user);
        $company->setAddressId($address);

        $em->persist($company);
        $em->flush();

        return $this->json([
            'message' => 'Company created successfully',
            'id' => $company->getId(),
            'userId' => $company->getUserId()?->getId(),
            'addressId' => $company->getAddressId()?->getId(),
            'email' => $company->getEmail(),
            'country' => $company->getCountry(),
            'status' => $company->getStatus(),
            'isApproved' => $company->isApproved(),
        ], 201);
    }
}
