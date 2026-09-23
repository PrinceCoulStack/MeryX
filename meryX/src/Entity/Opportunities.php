<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Opportunities\CreateOpportunities;
use App\Controller\Opportunities\ListOpportunities;
use App\Controller\Opportunities\UpdateOpportunities;
use App\Repository\OpportunitiesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: OpportunitiesRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['opportunities:read']],
    denormalizationContext: ['groups' => ['opportunities:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/opportunities',
            controller: ListOpportunities::class,
            name: 'listOpportunities'
        ),
        new Post(
            uriTemplate: '/opportunities',
            controller: CreateOpportunities::class,
            name: 'createOpportunities',
            security: "is_granted('ROLE_COMPANY')"
        ),
        new Get(
            uriTemplate: '/opportunities/{id}',
            name: 'getOpportunities'
        ),
        new Put(
            uriTemplate: '/opportunities/{id}',
            controller: UpdateOpportunities::class,
            name: 'updateOpportunities'
        ),
        new Delete(
            uriTemplate: '/opportunities/{id}',
            name: 'deleteOpportunities'
        )
    ]
)]
class Opportunities
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['opportunities:read', 'opportunities:write'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'opportunities')]
    #[Groups(['opportunities:read', 'opportunities:write', 'company:read'])]
    private ?Company $companyId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['opportunities:read', 'opportunities:write'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['opportunities:read', 'opportunities:write'])]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Groups(['opportunities:read', 'opportunities:write'])]
    private ?string $department = null;

    #[ORM\Column(length: 255)]
    #[Groups(['opportunities:read', 'opportunities:write'])]
    private ?string $location = null;

    #[ORM\Column(length: 255)]
    #[Groups(['opportunities:read', 'opportunities:write'])]
    private ?string $remoteType = null;

    #[ORM\Column(length: 255)]
    #[Groups(['opportunities:read', 'opportunities:write'])]
    private ?string $salaryLabel = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['opportunities:read', 'opportunities:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(['opportunities:read', 'opportunities:write'])]
    private ?string $status = null;

    #[ORM\Column]
    #[Groups(['opportunities:read', 'opportunities:write'])]
    private ?bool $isEnabled = null;

    #[ORM\Column]
    #[Groups(['opportunities:read', 'opportunities:write'])]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column]
    #[Groups(['opportunities:read', 'opportunities:write'])]
    private ?\DateTimeImmutable $applicationDeadLine = null;

    #[ORM\Column]
    #[Groups(['opportunities:read', 'opportunities:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['opportunities:read', 'opportunities:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(['opportunities:read', 'opportunities:write'])]
    private ?bool $isDeleted = null;

    #[ORM\Column(length: 255)]
    #[Groups(['opportunities:read', 'opportunities:write'])]
    private ?string $category = null;

    #[ORM\Column(length: 255)]
    #[Groups(['opportunities:read', 'opportunities:write'])]
    private ?string $experienceLevel = null;

    #[ORM\Column(length: 255)]
    #[Groups(['opportunities:read', 'opportunities:write'])]
    private ?string $numberOfPositions = null;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['opportunities:read', 'opportunities:write'])]
    private ?array $requirements = [];

    /**
     * @var Collection<int, Application>
     */
    #[ORM\OneToMany(targetEntity: Application::class, mappedBy: 'opportunityId')]
    private Collection $applications;

    /**
     * @var Collection<int, CandidateShortList>
     */
    #[ORM\OneToMany(targetEntity: CandidateShortList::class, mappedBy: 'opportunityId')]
    private Collection $candidateShortLists;

    public function __construct()
    {
        $this->applications = new ArrayCollection();
        $this->candidateShortLists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyId(): ?Company
    {
        return $this->companyId;
    }

    public function setCompanyId(?Company $companyId): static
    {
        $this->companyId = $companyId;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(string $department): static
    {
        $this->department = $department;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getRemoteType(): ?string
    {
        return $this->remoteType;
    }

    public function setRemoteType(string $remoteType): static
    {
        $this->remoteType = $remoteType;

        return $this;
    }

    public function getSalaryLabel(): ?string
    {
        return $this->salaryLabel;
    }

    public function setSalaryLabel(string $salaryLabel): static
    {
        $this->salaryLabel = $salaryLabel;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
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

    public function isEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): static
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getApplicationDeadLine(): ?\DateTimeImmutable
    {
        return $this->applicationDeadLine;
    }

    public function setApplicationDeadLine(\DateTimeImmutable $applicationDeadLine): static
    {
        $this->applicationDeadLine = $applicationDeadLine;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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

    public function isDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): static
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getExperienceLevel(): ?string
    {
        return $this->experienceLevel;
    }

    public function setExperienceLevel(string $experienceLevel): static
    {
        $this->experienceLevel = $experienceLevel;

        return $this;
    }

    public function getNumberOfPositions(): ?string
    {
        return $this->numberOfPositions;
    }

    public function setNumberOfPositions(string $numberOfPositions): static
    {
        $this->numberOfPositions = $numberOfPositions;

        return $this;
    }

    public function getRequirements(): ?array
    {
        return $this->requirements ?? [];
    }

    public function setRequirements(?array $requirements): static
    {
        $this->requirements = $requirements ?? [];

        return $this;
    }

    /**
     * @return Collection<int, Application>
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(Application $application): static
    {
        if (!$this->applications->contains($application)) {
            $this->applications->add($application);
            $application->setOpportunityId($this);
        }

        return $this;
    }

    public function removeApplication(Application $application): static
    {
        if ($this->applications->removeElement($application)) {
            // set the owning side to null (unless already changed)
            if ($application->getOpportunityId() === $this) {
                $application->setOpportunityId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CandidateShortList>
     */
    public function getCandidateShortLists(): Collection
    {
        return $this->candidateShortLists;
    }

    public function addCandidateShortList(CandidateShortList $candidateShortList): static
    {
        if (!$this->candidateShortLists->contains($candidateShortList)) {
            $this->candidateShortLists->add($candidateShortList);
            $candidateShortList->setOpportunityId($this);
        }

        return $this;
    }

    public function removeCandidateShortList(CandidateShortList $candidateShortList): static
    {
        if ($this->candidateShortLists->removeElement($candidateShortList)) {
            // set the owning side to null (unless already changed)
            if ($candidateShortList->getOpportunityId() === $this) {
                $candidateShortList->setOpportunityId(null);
            }
        }

        return $this;
    }
}
