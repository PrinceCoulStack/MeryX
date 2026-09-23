<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Training\CreateTraining;
use App\Controller\Training\ListTraining;
use App\Controller\Training\UpdateTraining;
use App\Repository\TrainingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TrainingRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['training:read']],
    denormalizationContext: ['groups' => ['training:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/trainings',
            controller: ListTraining::class,
            name: 'listTraining'
        ),
        new Post(
            uriTemplate: '/trainings',
            controller: CreateTraining::class,
            name: 'createTraining',
            security: "is_granted('ROLE_COMPANY')"
        ),
        new Get(
            uriTemplate: '/trainings/{id}',
            name: 'getTraining'
        ),
        new Put(
            uriTemplate: '/trainings/{id}',
            controller: UpdateTraining::class,
            name: 'updateTraining'
        ),
        new Delete(
            uriTemplate: '/trainings/{id}',
            name: 'deleteTraining'
        )
    ]
)]
class Training
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'trainings')]
    #[Groups(['training:read', 'training:write', 'company:read'])]
    private ?Company $companyId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['training:read', 'training:write', 'company:read'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['training:read', 'training:write'])]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Groups(['training:read', 'training:write'])]
    private ?string $mode = null;

    #[ORM\Column(length: 255)]
    #[Groups(['training:read', 'training:write'])]
    private ?string $location = null;

    #[ORM\Column(length: 255)]
    #[Groups(['training:read', 'training:write'])]
    private ?string $durationLabel = null;

    #[ORM\Column(length: 255)]
    #[Groups(['training:read', 'training:write'])]
    private ?string $seatCount = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['training:read', 'training:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(['training:read', 'training:write'])]
    private ?string $status = null;

    #[ORM\Column]
    #[Groups(['training:read', 'training:write'])]
    private ?\DateTimeImmutable $startAt = null;

    #[ORM\Column]
    #[Groups(['training:read', 'training:write'])]
    private ?\DateTimeImmutable $endAt = null;

    #[ORM\Column]
    #[Groups(['training:read', 'training:write'])]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column]
    #[Groups(['training:read', 'training:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['training:read', 'training:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, TrainingRequirement>
     */
    #[ORM\OneToMany(targetEntity: TrainingRequirement::class, mappedBy: 'trainingId')]
    private Collection $trainingRequirements;

    /**
     * @var Collection<int, TrainingEnrollment>
     */
    #[ORM\OneToMany(targetEntity: TrainingEnrollment::class, mappedBy: 'trainingId')]
    private Collection $trainingEnrollments;

    public function __construct()
    {
        $this->trainingRequirements = new ArrayCollection();
        $this->trainingEnrollments = new ArrayCollection();
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

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(string $mode): static
    {
        $this->mode = $mode;

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

    public function getDurationLabel(): ?string
    {
        return $this->durationLabel;
    }

    public function setDurationLabel(string $durationLabel): static
    {
        $this->durationLabel = $durationLabel;

        return $this;
    }

    public function getSeatCount(): ?string
    {
        return $this->seatCount;
    }

    public function setSeatCount(string $seatCount): static
    {
        $this->seatCount = $seatCount;

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

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeImmutable $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeImmutable $endAt): static
    {
        $this->endAt = $endAt;

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

    /**
     * @return Collection<int, TrainingRequirement>
     */
    public function getTrainingRequirements(): Collection
    {
        return $this->trainingRequirements;
    }

    public function addTrainingRequirement(TrainingRequirement $trainingRequirement): static
    {
        if (!$this->trainingRequirements->contains($trainingRequirement)) {
            $this->trainingRequirements->add($trainingRequirement);
            $trainingRequirement->setTrainingId($this);
        }

        return $this;
    }

    public function removeTrainingRequirement(TrainingRequirement $trainingRequirement): static
    {
        if ($this->trainingRequirements->removeElement($trainingRequirement)) {
            // set the owning side to null (unless already changed)
            if ($trainingRequirement->getTrainingId() === $this) {
                $trainingRequirement->setTrainingId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TrainingEnrollment>
     */
    public function getTrainingEnrollments(): Collection
    {
        return $this->trainingEnrollments;
    }

    public function addTrainingEnrollment(TrainingEnrollment $trainingEnrollment): static
    {
        if (!$this->trainingEnrollments->contains($trainingEnrollment)) {
            $this->trainingEnrollments->add($trainingEnrollment);
            $trainingEnrollment->setTrainingId($this);
        }

        return $this;
    }

    public function removeTrainingEnrollment(TrainingEnrollment $trainingEnrollment): static
    {
        if ($this->trainingEnrollments->removeElement($trainingEnrollment)) {
            // set the owning side to null (unless already changed)
            if ($trainingEnrollment->getTrainingId() === $this) {
                $trainingEnrollment->setTrainingId(null);
            }
        }

        return $this;
    }
}
