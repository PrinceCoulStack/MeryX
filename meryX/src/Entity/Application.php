<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Application\CreateApplication;
use App\Controller\Application\ListApplication;
use App\Controller\Application\UpdateApplication;
use App\Repository\ApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['application:read']],
    denormalizationContext: ['groups' => ['application:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/applications',
            controller: ListApplication::class,
            name: 'listApplication'
        ),
        new Post(
            uriTemplate: '/applications',
            controller: CreateApplication::class,
            name: 'createApplication'
        ),
        new Get(
            uriTemplate: '/applications/{id}',
            name: 'getApplication'
        ),
        new Put(
            uriTemplate: '/applications/{id}',
            controller: UpdateApplication::class,
            name: 'updateApplication'
        ),
        new Delete(
            uriTemplate: '/applications/{id}',
            name: 'deleteApplication'
        )
    ]
)]
class Application
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['application:read', 'application:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['application:read', 'application:write'])]
    private ?string $status = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['application:read', 'application:write'])]
    private ?string $coverLetter = null;

    #[ORM\Column(length: 255)]
    #[Groups(['application:read', 'application:write'])]
    private ?string $reviewNote = null;

    #[ORM\Column]
    #[Groups(['application:read', 'application:write'])]
    private ?\DateTimeImmutable $appliedAt = null;

    #[ORM\Column]
    #[Groups(['application:read', 'application:write'])]
    private ?\DateTimeImmutable $reviewedAt = null;

    #[ORM\Column]
    #[Groups(['application:read', 'application:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(['application:read', 'application:write'])]
    private ?\DateTimeImmutable $withdrawnAt = null;

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[Groups(['application:read', 'application:write'])]
    private ?StudentProfile $studentProfile = null;

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[Groups(['application:read', 'application:write'])]
    private ?Opportunities $opportunityId = null;

    /**
     * @var Collection<int, ApplicationStatusHistory>
     */
    #[ORM\OneToMany(targetEntity: ApplicationStatusHistory::class, mappedBy: 'applicationId')]
    private Collection $applicationStatusHistories;

    public function __construct()
    {
        $this->applicationStatusHistories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCoverLetter(): ?string
    {
        return $this->coverLetter;
    }

    public function setCoverLetter(string $coverLetter): static
    {
        $this->coverLetter = $coverLetter;

        return $this;
    }

    public function getReviewNote(): ?string
    {
        return $this->reviewNote;
    }

    public function setReviewNote(string $reviewNote): static
    {
        $this->reviewNote = $reviewNote;

        return $this;
    }

    public function getAppliedAt(): ?\DateTimeImmutable
    {
        return $this->appliedAt;
    }

    public function setAppliedAt(\DateTimeImmutable $appliedAt): static
    {
        $this->appliedAt = $appliedAt;

        return $this;
    }

    public function getReviewedAt(): ?\DateTimeImmutable
    {
        return $this->reviewedAt;
    }

    public function setReviewedAt(\DateTimeImmutable $reviewedAt): static
    {
        $this->reviewedAt = $reviewedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getWithdrawnAt(): ?\DateTimeImmutable
    {
        return $this->withdrawnAt;
    }

    public function setWithdrawnAt(\DateTimeImmutable $withdrawnAt): static
    {
        $this->withdrawnAt = $withdrawnAt;

        return $this;
    }

    public function getStudentProfile(): ?StudentProfile
    {
        return $this->studentProfile;
    }

    public function setStudentProfile(?StudentProfile $studentProfile): static
    {
        $this->studentProfile = $studentProfile;

        return $this;
    }

    public function getOpportunityId(): ?Opportunities
    {
        return $this->opportunityId;
    }

    public function setOpportunityId(?Opportunities $opportunityId): static
    {
        $this->opportunityId = $opportunityId;

        return $this;
    }

    /**
     * @return Collection<int, ApplicationStatusHistory>
     */
    public function getApplicationStatusHistories(): Collection
    {
        return $this->applicationStatusHistories;
    }

    public function addApplicationStatusHistory(ApplicationStatusHistory $applicationStatusHistory): static
    {
        if (!$this->applicationStatusHistories->contains($applicationStatusHistory)) {
            $this->applicationStatusHistories->add($applicationStatusHistory);
            $applicationStatusHistory->setApplicationId($this);
        }

        return $this;
    }

    public function removeApplicationStatusHistory(ApplicationStatusHistory $applicationStatusHistory): static
    {
        if ($this->applicationStatusHistories->removeElement($applicationStatusHistory)) {
            if ($applicationStatusHistory->getApplicationId() === $this) {
                $applicationStatusHistory->setApplicationId(null);
            }
        }

        return $this;
    }
}
