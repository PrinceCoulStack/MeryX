<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\OpportunityRequirement\CreateOpportunityRequirement;
use App\Controller\OpportunityRequirement\ListOpportunityRequirement;
use App\Controller\OpportunityRequirement\UpdateOpportunityRequirement;
use App\Repository\OpportunityRequirementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: OpportunityRequirementRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['opportunityRequirement:read']],
    denormalizationContext: ['groups' => ['opportunityRequirement:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/opportunityRequirements',
            controller: ListOpportunityRequirement::class,
            name: 'listOpportunityRequirement'
        ),
        new Post(
            uriTemplate: '/opportunityRequirements',
            controller: CreateOpportunityRequirement::class,
            name: 'createOpportunityRequirement'
        ),
        new Get(
            uriTemplate: '/opportunityRequirements/{id}',
            name: 'getOpportunityRequirement'
        ),
        new Put(
            uriTemplate: '/opportunityRequirements/{id}',
            controller: UpdateOpportunityRequirement::class,
            name: 'updateOpportunityRequirement'
        ),
        new Delete(
            uriTemplate: '/opportunityRequirements/{id}',
            name: 'deleteOpportunityRequirement'
        )
    ]
)]
class OpportunityRequirement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['opportunityRequirement:read', 'opportunityRequirement:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['opportunityRequirement:read', 'opportunityRequirement:write'])]
    private ?string $label = null;

    #[ORM\Column(length: 255)]
    #[Groups(['opportunityRequirement:read', 'opportunityRequirement:write'])]
    private ?string $type = null;

    #[ORM\Column]
    #[Groups(['opportunityRequirement:read', 'opportunityRequirement:write'])]
    private ?bool $isRequired = null;

    #[ORM\Column]
    #[Groups(['opportunityRequirement:read', 'opportunityRequirement:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
