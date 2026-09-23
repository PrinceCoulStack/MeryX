<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\AuditLog\CreateAuditLog;
use App\Controller\AuditLog\ListAuditLog;
use App\Controller\AuditLog\UpdateAuditLog;
use App\Repository\AuditLogRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['auditLog:read']],
    denormalizationContext: ['groups' => ['auditLog:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/audit-logs',
            controller: ListAuditLog::class,
            name: 'listAuditLog'
        ),
        new Post(
            uriTemplate: '/audit-logs',
            controller: CreateAuditLog::class,
            name: 'createAuditLog'
        ),
        new Get(
            uriTemplate: '/audit-logs/{id}',
            name: 'getAuditLog'
        ),
        new Put(
            uriTemplate: '/audit-logs/{id}',
            controller: UpdateAuditLog::class,
            name: 'updateAuditLog'
        ),
        new Delete(
            uriTemplate: '/audit-logs/{id}',
            name: 'deleteAuditLog'
        )
    ]
)]
class AuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['auditLog:read', 'auditLog:write', 'user:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'action')]
    #[Groups(['auditLog:read', 'auditLog:write', 'user:read'])]
    private ?User $actorId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['auditLog:read', 'auditLog:write'])]
    private ?string $action = null;

    #[ORM\Column(length: 255)]
    #[Groups(['auditLog:read', 'auditLog:write'])]
    private ?string $entityType = null;

    #[ORM\Column(length: 255)]
    #[Groups(['auditLog:read', 'auditLog:write'])]
    private ?string $entityId = null;

    #[ORM\Column(type: 'json')]
    #[Groups(['auditLog:read', 'auditLog:write'])]
    private array $payload = [];

    #[ORM\Column]
    #[Groups(['auditLog:read', 'auditLog:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['auditLog:read', 'auditLog:write'])]
    private ?string $ipAddress = null;

    #[ORM\Column(length: 255)]
    #[Groups(['auditLog:read', 'auditLog:write'])]
    private ?string $userAgent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActorId(): ?User
    {
        return $this->actorId;
    }

    public function setActorId(?User $actorId): static
    {
        $this->actorId = $actorId;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function setEntityType(string $entityType): static
    {
        $this->entityType = $entityType;

        return $this;
    }

    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    public function setEntityId(string $entityId): static
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): static
    {
        $this->payload = $payload;

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

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): static
    {
        $this->userAgent = $userAgent;

        return $this;
    }
}
