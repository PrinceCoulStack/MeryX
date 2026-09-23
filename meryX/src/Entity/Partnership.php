<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Partnership\CreatePartnership;
use App\Controller\Partnership\ListPartnership;
use App\Controller\Partnership\UpdatePartnership;
use App\Repository\PartnershipRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PartnershipRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['partnership:read']],
    denormalizationContext: ['groups' => ['partnership:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/partnerships',
            controller: ListPartnership::class,
            name: 'listPartnership'
        ),
        new Post(
            uriTemplate: '/partnerships',
            controller: CreatePartnership::class,
            name: 'createPartnership'
        ),
        new Get(
            uriTemplate: '/partnerships/{id}',
            name: 'getPartnership'
        ),
        new Put(
            uriTemplate: '/partnerships/{id}',
            controller: UpdatePartnership::class,
            name: 'updatePartnership'
        ),
        new Delete(
            uriTemplate: '/partnerships/{id}',
            name: 'deletePartnership'
        )
    ]
)]
class Partnership
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['partnership:read', 'partnership:write', 'company:read', 'university:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'partnerships')]
    #[Groups(['partnership:read', 'partnership:write', 'university:read'])]
    private ?Company $companyId = null;

    #[ORM\ManyToOne(inversedBy: 'partnerships')]
    #[Groups(['partnership:read', 'partnership:write', 'company:read'])]
    private ?University $universityId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['partnership:read', 'partnership:write'])]
    private ?string $status = null;

    #[ORM\Column]
    #[Groups(['partnership:read', 'partnership:write'])]
    private ?\DateTimeImmutable $startAt = null;

    #[ORM\Column]
    #[Groups(['partnership:read', 'partnership:write'])]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['partnership:read', 'partnership:write'])]
    private ?string $notes = null;

    #[ORM\Column]
    #[Groups(['partnership:read', 'partnership:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['partnership:read', 'partnership:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

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

    public function getUniversityId(): ?University
    {
        return $this->universityId;
    }

    public function setUniversityId(?University $universityId): static
    {
        $this->universityId = $universityId;

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

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(\DateTimeImmutable $endedAt): static
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): static
    {
        $this->notes = $notes;

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
