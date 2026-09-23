<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\ApplicationStatusHistory\CreateApplicationStatusHistory;
use App\Controller\ApplicationStatusHistory\ListApplicationStatusHistory;
use App\Controller\ApplicationStatusHistory\UpdateApplicationStatusHistory;
use App\Repository\ApplicationStatusHistoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ApplicationStatusHistoryRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['applicationStatusHistory:read']],
    denormalizationContext: ['groups' => ['applicationStatusHistory:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/application-status-histories',
            controller: ListApplicationStatusHistory::class,
            name: 'listApplicationStatusHistory'
        ),
        new Post(
            uriTemplate: '/application-status-histories',
            controller: CreateApplicationStatusHistory::class,
            name: 'createApplicationStatusHistory'
        ),
        new Get(
            uriTemplate: '/application-status-histories/{id}',
            name: 'getApplicationStatusHistory'
        ),
        new Put(
            uriTemplate: '/application-status-histories/{id}',
            controller: UpdateApplicationStatusHistory::class,
            name: 'updateApplicationStatusHistory'
        ),
        new Delete(
            uriTemplate: '/application-status-histories/{id}',
            name: 'deleteApplicationStatusHistory'
        )
    ]
)]
class ApplicationStatusHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['applicationStatusHistory:read', 'applicationStatusHistory:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['applicationStatusHistory:read', 'applicationStatusHistory:write'])]
    private ?string $oldStatus = null;

    #[ORM\Column(length: 255)]
    #[Groups(['applicationStatusHistory:read', 'applicationStatusHistory:write'])]
    private ?string $newStatus = null;

    #[ORM\ManyToOne(inversedBy: 'applicationStatusHistories')]
    #[Groups(['applicationStatusHistory:read', 'applicationStatusHistory:write'])]
    private ?User $changedById = null;

    #[ORM\Column(length: 255)]
    #[Groups(['applicationStatusHistory:read', 'applicationStatusHistory:write'])]
    private ?string $note = null;

    #[ORM\Column]
    #[Groups(['applicationStatusHistory:read', 'applicationStatusHistory:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'applicationStatusHistories')]
    #[Groups(['applicationStatusHistory:read', 'applicationStatusHistory:write'])]
    private ?Application $applicationId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOldStatus(): ?string
    {
        return $this->oldStatus;
    }

    public function setOldStatus(string $oldStatus): static
    {
        $this->oldStatus = $oldStatus;

        return $this;
    }

    public function getNewStatus(): ?string
    {
        return $this->newStatus;
    }

    public function setNewStatus(string $newStatus): static
    {
        $this->newStatus = $newStatus;

        return $this;
    }

    public function getChangedById(): ?User
    {
        return $this->changedById;
    }

    public function setChangedById(?User $changedById): static
    {
        $this->changedById = $changedById;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(string $note): static
    {
        $this->note = $note;

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

    public function getApplicationId(): ?Application
    {
        return $this->applicationId;
    }

    public function setApplicationId(?Application $applicationId): static
    {
        $this->applicationId = $applicationId;

        return $this;
    }
}
