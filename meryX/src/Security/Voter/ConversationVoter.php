<?php

namespace App\Security\Voter;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\Partnership;
use App\Entity\User;
use App\Repository\CompanyRepository;
use App\Repository\PartnershipRepository;
use App\Repository\StudentProfileRepository;
use App\Repository\UniversityRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ConversationVoter extends Voter
{
    public const READ = 'CONVERSATION_READ';
    public const WRITE = 'CONVERSATION_WRITE';

    public function __construct(
        private readonly PartnershipRepository $partnershipRepository,
        private readonly UniversityRepository $universityRepository,
        private readonly StudentProfileRepository $studentProfileRepository,
        private readonly CompanyRepository $companyRepository,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::READ, self::WRITE], true)
            && ($subject instanceof Conversation || $subject instanceof Message);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $actor = $token->getUser();
        if (!$actor instanceof User) {
            return false;
        }

        if (in_array('ROLE_ADMIN', $actor->getRoles(), true)) {
            return $attribute === self::READ;
        }

        $conversation = $subject instanceof Message ? $subject->getConversationId() : $subject;
        if (!$conversation instanceof Conversation) {
            return false;
        }

        foreach ($conversation->getParticipants() as $participant) {
            if ($participant->getUser()?->getId() === $actor->getId()) {
                return true;
            }
        }

        return $this->enforceTenantRules($actor, $conversation);
    }

    private function enforceTenantRules(User $actor, Conversation $conversation): bool
    {
        $type = strtolower((string) $conversation->getType());
        $actorUniversity = $this->universityRepository->findOneBy(['userId' => $actor]);

        if ($type === 'university_student') {
            if ($actorUniversity === null) {
                return false;
            }

            foreach ($conversation->getParticipants() as $participant) {
                $user = $participant->getUser();
                if ($user === null) {
                    continue;
                }

                $profile = $this->studentProfileRepository->findOneBy(['userId' => $user]);
                if ($profile !== null && $profile->getUniversityId()?->getId() === $actorUniversity->getId()) {
                    return true;
                }
            }

            return false;
        }

        if ($type === 'university_company') {
            if ($actorUniversity === null) {
                return false;
            }

            foreach ($conversation->getParticipants() as $participant) {
                $user = $participant->getUser();
                if ($user === null) {
                    continue;
                }

                $company = $this->companyRepository->findOneBy(['userId' => $user]);
                if ($company === null) {
                    continue;
                }

                $partnership = $this->partnershipRepository->findOneBy([
                    'universityId' => $actorUniversity,
                    'companyId' => $company,
                    'status' => 'approved',
                ]);

                if ($partnership instanceof Partnership) {
                    return true;
                }
            }
        }

        return false;
    }
}
