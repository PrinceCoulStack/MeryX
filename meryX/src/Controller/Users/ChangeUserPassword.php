<?php

namespace App\Controller\Users;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Api\ApiErrorResponseFactory;
use App\Service\User\PasswordPolicy;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsController]
final class ChangeUserPassword
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ApiErrorResponseFactory $errorResponseFactory,
        private readonly Security $security,
    ) {
    }

    public function __invoke(Request $request, int $id)
    {
        $actor = $this->security->getUser();
        if (!$actor instanceof User) {
            return $this->errorResponseFactory->create(401, 'Unauthorized', 'Missing or invalid JWT token.');
        }

        $user = $this->userRepository->find($id);
        if (!$user instanceof User) {
            return $this->errorResponseFactory->create(404, 'Not Found', 'User not found.');
        }

        $isAdmin = in_array('ROLE_ADMIN', $actor->getRoles(), true);
        $isSelf = $actor->getId() === $user->getId();
        if (!$isAdmin && !$isSelf) {
            return $this->errorResponseFactory->create(403, 'Forbidden', 'You are not allowed to change this user\'s password.');
        }

        $data = json_decode((string) $request->getContent(), true);
        if (!is_array($data)) {
            return $this->errorResponseFactory->create(400, 'Bad Request', 'Invalid payload.');
        }

        $newPassword = (string) ($data['newPassword'] ?? '');
        $currentPassword = (string) ($data['currentPassword'] ?? '');

        // self-service changes must prove knowledge of the current password; admins resetting another user's password do not need it
        if ($isSelf) {
            if ($currentPassword === '' || !$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
                return $this->errorResponseFactory->create(422, 'Unprocessable Entity', 'Current password is incorrect.', [
                    'errors' => ['currentPassword' => ['Current password is incorrect.']],
                ]);
            }
        }

        $policyErrors = PasswordPolicy::validate($newPassword);
        if ($policyErrors !== []) {
            return $this->errorResponseFactory->create(422, 'Unprocessable Entity', 'Password does not meet requirements.', [
                'errors' => ['newPassword' => $policyErrors],
            ]);
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
        $user->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
