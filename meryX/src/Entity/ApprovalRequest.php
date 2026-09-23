<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\ApprovalRequest\CreateApprovalRequest;
use App\Controller\ApprovalRequest\ListApprovalRequest;
use App\Controller\ApprovalRequest\UpdateApprovalRequest;
use App\Repository\ApprovalRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ApprovalRequestRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['approvalRequest:read']],
    denormalizationContext: ['groups' => ['approvalRequest:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/approval-requests',
            controller: ListApprovalRequest::class,
            name: 'listApprovalRequest'
        ),
        new Post(
            uriTemplate: '/approval-requests',
            controller: CreateApprovalRequest::class,
            name: 'createApprovalRequest'
        ),
        new Get(
            uriTemplate: '/approval-requests/{id}',
            name: 'getApprovalRequest'
        ),
        new Put(
            uriTemplate: '/approval-requests/{id}',
            controller: UpdateApprovalRequest::class,
            name: 'updateApprovalRequest'
        ),
        new Delete(
            uriTemplate: '/approval-requests/{id}',
            name: 'deleteApprovalRequest'
        )
    ]
)]
class ApprovalRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['approvalRequest:read', 'approvalRequest:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['approvalRequest:read', 'approvalRequest:write'])]
    private ?string $type = null;

    #[ORM\Column(type: 'json')]
    #[Groups(['approvalRequest:read', 'approvalRequest:write'])]
    private array $status = [];

    #[ORM\Column(length: 255)]
    #[Groups(['approvalRequest:read', 'approvalRequest:write'])]
    private ?string $reviewedBy = null;

    #[ORM\Column]
    #[Groups(['approvalRequest:read', 'approvalRequest:write'])]
    private ?\DateTimeImmutable $reviewedAt = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatus(): array
    {
        return $this->status;
    }

    public function setStatus(array $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getReviewedBy(): ?string
    {
        return $this->reviewedBy;
    }

    public function setReviewedBy(string $reviewedBy): static
    {
        $this->reviewedBy = $reviewedBy;

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
}
