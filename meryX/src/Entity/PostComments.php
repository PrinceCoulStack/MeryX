<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\PostComments\CreatePostComments;
use App\Controller\PostComments\ListPostComments;
use App\Controller\PostComments\UpdatePostComments;
use App\Repository\PostCommentsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PostCommentsRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['postComments:read']],
    denormalizationContext: ['groups' => ['postComments:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/postComments',
            controller: ListPostComments::class,
            name: 'listPostComments'
        ),
        new Post(
            uriTemplate: '/postComments',
            controller: CreatePostComments::class,
            name: 'createPostComments'
        ),
        new Get(
            uriTemplate: '/postComments/{id}',
            name: 'getPostComments'
        ),
        new Put(
            uriTemplate: '/postComments/{id}',
            controller: UpdatePostComments::class,
            name: 'updatePostComments'
        ),
        new Delete(
            uriTemplate: '/postComments/{id}',
            name: 'deletePostComments'
        )
    ]
)]
class PostComments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['postComments:read', 'postComments:write', 'user:read', 'companyPost:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'postComments')]
    #[Groups(['postComments:read', 'postComments:write', 'companyPost:read'])]
    private ?CompanyPost $postId = null;

    #[ORM\ManyToOne(inversedBy: 'postComments')]
    #[Groups(['postComments:read', 'postComments:write', 'user:read'])]
    private ?User $authorId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['postComments:read', 'postComments:write'])]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups(['postComments:read', 'postComments:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['postComments:read', 'postComments:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

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

    public function getAuthorId(): ?User
    {
        return $this->authorId;
    }

    public function setAuthorId(?User $authorId): static
    {
        $this->authorId = $authorId;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

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
