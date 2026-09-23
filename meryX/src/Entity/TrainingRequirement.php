<?php

namespace App\Entity;

use Symfony\Component\Serializer\Attribute\Groups;
use App\Repository\TrainingRequirementRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Controller\TrainingRequirement\ListTrainingRequirement;
use App\Controller\TrainingRequirement\CreateTrainingRequirement;
use App\Controller\TrainingRequirement\UpdateTrainingRequirement;

#[ORM\Entity(repositoryClass: TrainingRequirementRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['trainingRequirement:read']],
    denormalizationContext: ['groups' => ['trainingRequirement:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/trainingRequirements',
            controller: ListTrainingRequirement::class,
            name: 'listTrainingRequirement'
        ),
        new Post(
            uriTemplate: '/trainingRequirements',
            controller: CreateTrainingRequirement::class,
            name: 'createTrainingRequirement'
        ),
        new Get(
            uriTemplate: '/trainingRequirements/{id}',
            name: 'getTrainingRequirement'
        ),
        new Put(
            uriTemplate: '/trainingRequirements/{id}',
            controller: UpdateTrainingRequirement::class,
            name: 'updateTrainingRequirement'
        ),
        new Delete(
            uriTemplate: '/trainingRequirements/{id}',
            name: 'deleteTrainingRequirement'
        )
    ]
)]
class TrainingRequirement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'trainingRequirements')]
    #[Groups(['trainingRequirement:read', 'trainingRequirement:write', 'training:read'])]
    private ?Training $trainingId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['trainingRequirement:read', 'trainingRequirement:write', 'training:read'])]
    private ?string $label = null;

    #[ORM\Column]
    #[Groups(['trainingRequirement:read', 'trainingRequirement:write'])]
    private ?bool $isRequired = null;

    #[ORM\Column]
    #[Groups(['trainingRequirement:read', 'trainingRequirement:write'])]
    private ?bool $isDeleted = null;

    #[ORM\Column]
    #[Groups(['trainingRequirement:read', 'trainingRequirement:write'])]
    private ?bool $isEnabled = null;

    #[ORM\Column]
    #[Groups(['trainingRequirement:read', 'trainingRequirement:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['trainingRequirement:read', 'trainingRequirement:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTrainingId(): ?Training
    {
        return $this->trainingId;
    }

    public function setTrainingId(?Training $trainingId): static
    {
        $this->trainingId = $trainingId;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function isRequired(): ?bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): static
    {
        $this->isRequired = $isRequired;

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

    public function isEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): static
    {
        $this->isEnabled = $isEnabled;

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
}
