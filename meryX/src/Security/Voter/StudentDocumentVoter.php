<?php

namespace App\Security\Voter;

use App\Entity\StudentDocuments;
use App\Entity\User;
use App\Security\StudentProfileAccessService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class StudentDocumentVoter extends Voter
{
    public const VIEW = 'STUDENT_DOCUMENT_VIEW';
    public const EDIT = 'STUDENT_DOCUMENT_EDIT';
    public const DELETE = 'STUDENT_DOCUMENT_DELETE';

    public function __construct(private readonly StudentProfileAccessService $accessService)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)
            && $subject instanceof StudentDocuments;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var StudentDocuments $document */
        $document = $subject;

        return match ($attribute) {
            self::VIEW => $this->accessService->canViewDocument($user, $document),
            self::EDIT => $this->accessService->canEditDocument($user, $document),
            self::DELETE => $this->accessService->canEditDocument($user, $document),
            default => false,
        };
    }
}
