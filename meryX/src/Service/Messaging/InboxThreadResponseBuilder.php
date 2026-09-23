<?php

namespace App\Service\Messaging;

use App\Entity\Candidature;
use App\Entity\Company;
use App\Entity\Conversation;
use App\Entity\ConversationParticipant;
use App\Entity\Message;
use App\Entity\StudentProfile;
use App\Entity\University;
use App\Entity\User;
use App\Repository\CandidatureRepository;
use App\Repository\CompanyRepository;
use App\Repository\StudentProfileRepository;
use App\Repository\UniversityRepository;

final class InboxThreadResponseBuilder
{
    public function __construct(
        private readonly StudentProfileRepository $studentProfileRepository,
        private readonly CompanyRepository $companyRepository,
        private readonly UniversityRepository $universityRepository,
        private readonly CandidatureRepository $candidatureRepository,
    ) {
    }

    /**
     * @param array<int, Message> $messages
     * @return array<string, mixed>
     */
    public function buildConversationPayload(Conversation $conversation, User $actor, array $messages = []): array
    {
        [$studentProfile, $company, $university] = $this->resolveThreadProfiles($conversation);
        $candidature = $this->resolveCandidatureContext($studentProfile, $company);

        $threadMessages = [];
        foreach ($messages as $message) {
            if (!$message instanceof Message) {
                continue;
            }

            $threadMessages[] = $this->buildMessagePayload($message);
        }

        if ($threadMessages === []) {
            $latest = $this->resolveLatestMessage($conversation);
            if ($latest instanceof Message) {
                $threadMessages[] = $this->buildMessagePayload($latest);
            }
        }

        $participants = [];
        foreach ($conversation->getParticipants() as $participant) {
            if (!$participant instanceof ConversationParticipant) {
                continue;
            }

            $participants[] = $this->buildParticipantPayload($participant);
        }

        return [
            'id' => $conversation->getId(),
            '@id' => '/api/conversations/' . $conversation->getId(),
            'subject' => $conversation->getSubject(),
            'type' => $conversation->getType(),
            'createdAt' => $conversation->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'updatedAt' => $conversation->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
            'student' => $this->buildStudentPayload($studentProfile),
            'company' => $this->buildCompanyPayload($company),
            'university' => $this->buildUniversityPayload($university),
            'candidatureStatus' => $candidature?->getStatus(),
            'interviewDate' => $candidature?->getInterviewDate()?->format(\DateTimeInterface::ATOM),
            'appliedDate' => $candidature?->getAppliedDate()?->format(\DateTimeInterface::ATOM),
            'lastUpdated' => $candidature?->getLastUpdated()?->format(\DateTimeInterface::ATOM),
            'thread' => [
                'participants' => $participants,
                'messages' => $threadMessages,
                'lastMessage' => $threadMessages !== [] ? $threadMessages[count($threadMessages) - 1] : null,
                'unreadCountForCurrentUser' => 0,
            ],
            'viewer' => [
                'id' => $actor->getId(),
                'email' => $actor->getEmail(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildMessagePayload(Message $message): array
    {
        $conversation = $message->getConversationId();
        $sender = $message->getSenderId();

        return [
            'id' => $message->getId(),
            '@id' => '/api/messages/' . $message->getId(),
            'conversationId' => $conversation?->getId(),
            'conversation' => $conversation ? [
                'id' => $conversation->getId(),
                '@id' => '/api/conversations/' . $conversation->getId(),
                'type' => $conversation->getType(),
            ] : null,
            'senderId' => $sender?->getId(),
            'sender' => $sender ? [
                'id' => $sender->getId(),
                '@id' => '/api/users/' . $sender->getId(),
                'email' => $sender->getEmail(),
            ] : null,
            'channelType' => $conversation?->getType() ?? 'direct',
            'encrypted' => !empty($message->getCiphertext()),
            'contentPreview' => $message->getContentPreview() ?: '[encrypted content]',
            'content' => $this->safeReadBody($message),
            'createdAt' => $message->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            'deliveryState' => $message->getDeliveryState() ?? 'sent',
            'unreadCountForCurrentUser' => 0,
        ];
    }

    /**
     * @return array{0:?StudentProfile,1:?Company,2:?University}
     */
    private function resolveThreadProfiles(Conversation $conversation): array
    {
        $studentProfile = null;
        $company = null;
        $university = null;

        foreach ($conversation->getParticipants() as $participant) {
            $user = $participant->getUser();
            if (!$user instanceof User) {
                continue;
            }

            $studentProfile ??= $this->studentProfileRepository->findOneBy(['userId' => $user]);
            $company ??= $this->companyRepository->findOneBy(['userId' => $user]);
            $university ??= $this->universityRepository->findOneBy(['userId' => $user]);
        }

        return [$studentProfile, $company, $university];
    }

    private function resolveCandidatureContext(?StudentProfile $studentProfile, ?Company $company): ?Candidature
    {
        $studentId = $studentProfile?->getId();
        if ($studentId === null) {
            return null;
        }

        $companyId = $company?->getId();
        if ($companyId !== null) {
            return $this->candidatureRepository->findLatestForStudentAndCompany($studentId, $companyId);
        }

        return $this->candidatureRepository->findLatestForStudent($studentId);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildStudentPayload(?StudentProfile $profile): ?array
    {
        if (!$profile instanceof StudentProfile) {
            return null;
        }

        return [
            'id' => $profile->getId(),
            '@id' => '/api/studentProfiles/' . $profile->getId(),
            'userId' => $profile->getUserId()?->getId(),
            'fullName' => $profile->getFullName(),
            'email' => $profile->getUserId()?->getEmail(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildCompanyPayload(?Company $company): ?array
    {
        if (!$company instanceof Company) {
            return null;
        }

        return [
            'id' => $company->getId(),
            '@id' => '/api/companies/' . $company->getId(),
            'userId' => $company->getUserId()?->getId(),
            'name' => $company->getName(),
            'email' => $company->getEmail(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildUniversityPayload(?University $university): ?array
    {
        if (!$university instanceof University) {
            return null;
        }

        return [
            'id' => $university->getId(),
            '@id' => '/api/university/' . $university->getId(),
            'userId' => $university->getUserId()?->getId(),
            'name' => $university->getName(),
            'email' => $university->getEmail(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildParticipantPayload(ConversationParticipant $participant): array
    {
        $user = $participant->getUser();

        return [
            'id' => $participant->getId(),
            'userId' => $user?->getId(),
            'user' => $user ? [
                'id' => $user->getId(),
                '@id' => '/api/users/' . $user->getId(),
                'email' => $user->getEmail(),
            ] : null,
            'joinedAt' => $participant->getJoinedAt()?->format(\DateTimeInterface::ATOM),
            'lastReadAt' => $participant->getLastReadAt()?->format(\DateTimeInterface::ATOM),
        ];
    }

    private function resolveLatestMessage(Conversation $conversation): ?Message
    {
        $messages = $conversation->getMessages();
        if ($messages->isEmpty()) {
            return null;
        }

        $latest = null;
        foreach ($messages as $message) {
            if (!$message instanceof Message) {
                continue;
            }

            if ($latest === null || ($message->getCreatedAt() !== null && $latest->getCreatedAt() !== null && $message->getCreatedAt() > $latest->getCreatedAt())) {
                $latest = $message;
            }
        }

        return $latest;
    }

    private function safeReadBody(Message $message): ?string
    {
        try {
            return $message->getBody();
        } catch (\Throwable) {
            return null;
        }
    }
}
