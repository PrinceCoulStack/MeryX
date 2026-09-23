<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\Candidature\CreateCandidature;
use App\Controller\Candidature\ListCandidature;
use App\Controller\Candidature\PatchCandidature;
use App\Repository\CandidatureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandidatureRepository::class)]
#[ORM\Table(name: 'candidatures')]
#[ORM\UniqueConstraint(name: 'uniq_candidature_opportunity_student', columns: ['opportunity_id', 'student_id'])]
#[ORM\Index(name: 'idx_candidatures_student_id', columns: ['student_id'])]
#[ORM\Index(name: 'idx_candidatures_status', columns: ['status'])]
#[ORM\Index(name: 'idx_candidatures_created_at', columns: ['created_at'])]
#[ORM\Index(name: 'idx_candidatures_student_status', columns: ['student_id', 'status'])]
#[ApiResource(operations: [
    new GetCollection(
        uriTemplate: '/candidatures',
        controller: ListCandidature::class,
        name: 'listCandidature',
        read: false,
        deserialize: false,
        security: "is_granted('IS_AUTHENTICATED_FULLY')"
    ),
    new Post(
        uriTemplate: '/candidatures',
        controller: CreateCandidature::class,
        name: 'createCandidature',
        read: false,
        deserialize: false,
        security: "is_granted('IS_AUTHENTICATED_FULLY')"
    ),
    new Patch(
        uriTemplate: '/candidatures/{id}',
        controller: PatchCandidature::class,
        name: 'patchCandidature',
        read: false,
        deserialize: false,
        security: "is_granted('IS_AUTHENTICATED_FULLY')"
    ),
])]
class Candidature
{
    public const STATUS_APPLIED = 'applied';
    public const STATUS_INTERVIEW = 'interview';
    public const STATUS_OFFER = 'offer';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'opportunity_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Opportunities $opportunity = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'student_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?StudentProfile $student = null;

    #[ORM\Column(length: 50, options: ['default' => self::STATUS_APPLIED])]
    private string $status = self::STATUS_APPLIED;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $appliedDate = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $lastUpdated = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $feedback = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $interviewDate = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $score = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOpportunity(): ?Opportunities
    {
        return $this->opportunity;
    }

    public function setOpportunity(?Opportunities $opportunity): static
    {
        $this->opportunity = $opportunity;

        return $this;
    }

    public function getStudent(): ?StudentProfile
    {
        return $this->student;
    }

    public function setStudent(?StudentProfile $student): static
    {
        $this->student = $student;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = strtolower(trim($status));

        return $this;
    }

    public function getAppliedDate(): ?\DateTimeImmutable
    {
        return $this->appliedDate;
    }

    public function setAppliedDate(?\DateTimeImmutable $appliedDate): static
    {
        $this->appliedDate = $appliedDate;

        return $this;
    }

    public function getLastUpdated(): ?\DateTimeImmutable
    {
        return $this->lastUpdated;
    }

    public function setLastUpdated(?\DateTimeImmutable $lastUpdated): static
    {
        $this->lastUpdated = $lastUpdated;

        return $this;
    }

    public function getFeedback(): ?string
    {
        return $this->feedback;
    }

    public function setFeedback(?string $feedback): static
    {
        $this->feedback = $feedback;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getInterviewDate(): ?\DateTimeImmutable
    {
        return $this->interviewDate;
    }

    public function setInterviewDate(?\DateTimeImmutable $interviewDate): static
    {
        $this->interviewDate = $interviewDate;

        return $this;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
