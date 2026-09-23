<?php

namespace App\Command;

use App\Entity\StudentProfile;
use App\Repository\StudentProfileRepository;
use App\Repository\UniversityRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:repair-student-profiles', description: 'Backfills missing student profile relations and profile URLs when possible.')]
class RepairStudentProfilesCommand extends Command
{
    public function __construct(
        private readonly StudentProfileRepository $studentProfileRepository,
        private readonly UserRepository $userRepository,
        private readonly UniversityRepository $universityRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $profiles = $this->studentProfileRepository->findAll();
        $fixed = 0;
        $unresolved = [];

        foreach ($profiles as $profile) {
            if (!$profile instanceof StudentProfile) {
                continue;
            }

            $wasChanged = false;
            $bio = $this->decodeBio($profile);

            if ($profile->getUserId() === null) {
                $resolvedUser = $this->resolveUserFromBio($bio);
                if ($resolvedUser !== null) {
                    $profile->setUserId($resolvedUser);
                    $wasChanged = true;
                } else {
                    $unresolved[] = sprintf('Profile %d is missing user_id', $profile->getId() ?? 0);
                }
            }

            if ($profile->getUniversityId() === null) {
                $resolvedUniversity = $this->resolveUniversityFromBio($bio);
                if ($resolvedUniversity !== null) {
                    $profile->setUniversityId($resolvedUniversity);
                    $wasChanged = true;
                } else {
                    $unresolved[] = sprintf('Profile %d is missing university_id', $profile->getId() ?? 0);
                }
            }

            if ($profile->getProfileUrl() === null || trim((string) $profile->getProfileUrl()) === '') {
                $profile->setProfileUrl($this->generateProfileUrl($profile));
                $wasChanged = true;
            }

            if ($profile->getStatus() === null || trim((string) $profile->getStatus()) === '') {
                $profile->setStatus('approved');
                $wasChanged = true;
            }

            if ($profile->isApproved() === null) {
                $profile->setIsApproved(strtolower((string) $profile->getStatus()) === 'approved');
                $wasChanged = true;
            }

            if ($profile->getVerificationData() === null) {
                $profile->setVerificationData($this->buildVerificationFallback($profile, $bio));
                $wasChanged = true;
            }

            if ($wasChanged) {
                $fixed++;
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf('Processed %d profiles, updated %d.', count($profiles), $fixed));

        if ($unresolved !== []) {
            $io->warning('Some profiles still require manual repair.');
            $io->listing($unresolved);
        }

        return Command::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeBio(StudentProfile $profile): array
    {
        $bio = $profile->getBio();
        if (!is_string($bio) || trim($bio) === '') {
            return [];
        }

        $decoded = json_decode($bio, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function resolveUserFromBio(array $bio): ?\App\Entity\User
    {
        $identifier = $bio['personal']['userId'] ?? $bio['userId'] ?? null;
        if (is_int($identifier) || (is_string($identifier) && ctype_digit($identifier))) {
            return $this->userRepository->find((int) $identifier);
        }

        return null;
    }

    private function resolveUniversityFromBio(array $bio): ?\App\Entity\University
    {
        $identifier = $bio['academic']['universityId'] ?? $bio['universityId'] ?? null;
        if (is_int($identifier) || (is_string($identifier) && ctype_digit($identifier))) {
            return $this->universityRepository->find((int) $identifier);
        }

        return null;
    }

    private function generateProfileUrl(StudentProfile $profile): string
    {
        $fullName = trim((string) $profile->getFullName());
        if ($fullName === '') {
            $fullName = (string) $profile->getUserId()?->getEmail();
        }

        if ($fullName === '') {
            $fullName = 'Student';
        }

        return 'https://ui-avatars.com/api/?background=0D8ABC&color=fff&name=' . rawurlencode($fullName);
    }

    /**
     * @param array<string, mixed> $bio
     * @return array<string, mixed>
     */
    private function buildVerificationFallback(StudentProfile $profile, array $bio): array
    {
        return [
            'email' => $profile->getUserId()?->getEmail(),
            'phone' => $profile->getUserId()?->getPhone(),
            'program' => $bio['academic']['program'] ?? null,
            'level' => $bio['academic']['level'] ?? null,
            'universityName' => $profile->getUniversityId()?->getName(),
            'universityEmail' => $profile->getUniversityId()?->getEmail(),
        ];
    }
}
