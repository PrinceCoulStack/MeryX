<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\PostReaction\CreatePostReaction;
use App\Controller\PostReaction\ListPostReaction;
use App\Controller\PostReaction\UpdatePostReaction;
use App\Repository\PostReactionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PostReactionRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['postReaction:read']],
    denormalizationContext: ['groups' => ['postReaction:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/postReactions',
            controller: ListPostReaction::class,
            name: 'listPostReaction'
        ),
        new Post(
            uriTemplate: '/postReactions',
            controller: CreatePostReaction::class,
            name: 'createPostReaction'
        ),
        new GetCollection(
            uriTemplate: '/postRection',
            controller: ListPostReaction::class,
            name: 'listPostReactionLegacy'
        ),
        new Post(
            uriTemplate: '/postRection',
            controller: CreatePostReaction::class,
            name: 'createPostReactionLegacy'
        ),
        new Get(
            uriTemplate: '/postReactions/{id}',
            name: 'getPostReaction'
        ),
        new Put(
            uriTemplate: '/postReactions/{id}',
            controller: UpdatePostReaction::class,
            name: 'updatePostReaction'
        ),
        new Delete(
            uriTemplate: '/postReactions/{id}',
            name: 'deletePostReaction'
        )
    ]
)]
class PostReaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['postReaction:read', 'postReaction:write', 'user:read', 'companyPost:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'postReactions')]
    #[Groups(['postReaction:read', 'postReaction:write', 'companyPost:read'])]
    private ?CompanyPost $postId = null;

    #[ORM\ManyToOne(inversedBy: 'postReactions')]
    #[Groups(['postReaction:read', 'postReaction:write', 'user:read'])]
    private ?User $userId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['postReaction:read', 'postReaction:write'])]
    private ?string $type = null;

    #[ORM\Column]
    #[Groups(['postReaction:read', 'postReaction:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPostId(): ?CompanyPost
    {
        return $this->postId;
    }

    public function setPostId(?CompanyPost $postId): static
    {
        $this->postId = $postId;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $userId): static
    {
        $this->userId = $userId;

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
