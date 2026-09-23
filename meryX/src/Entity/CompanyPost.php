<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\CompanyPost\CreateCompanyPost;
use App\Controller\CompanyPost\ListCompanyPost;
use App\Controller\CompanyPost\UpdateCompanyPost;
use App\Repository\CompanyPostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CompanyPostRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['companyPost:read']],
    denormalizationContext: ['groups' => ['companyPost:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/companyPosts',
            controller: ListCompanyPost::class,
            name: 'listCompanyPost'
        ),
        new Post(
            uriTemplate: '/companyPosts',
            controller: CreateCompanyPost::class,
            name: 'createCompanyPost',
            security: "is_granted('ROLE_COMPANY')"
        ),
        new Get(
            uriTemplate: '/companyPosts/{id}',
            name: 'getCompanyPost'
        ),
        new Put(
            uriTemplate: '/companyPosts/{id}',
            controller: UpdateCompanyPost::class,
            name: 'updateCompanyPost'
        ),
        new Delete(
            uriTemplate: '/companyPosts/{id}',
            name: 'deleteCompanyPost'
        )
    ]
)]
class CompanyPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['companyPost:read', 'companyPost:write', 'company:read', 'user:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'companyPosts')]
    #[Groups(['companyPost:read', 'companyPost:write', 'company:read'])]
    private ?Company $companyId = null;

    #[ORM\ManyToOne(inversedBy: 'companyPosts')]
    #[Groups(['companyPost:read', 'companyPost:write', 'user:read'])]
    private ?User $authorId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['companyPost:read', 'companyPost:write'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['companyPost:read', 'companyPost:write'])]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    #[Groups(['companyPost:read', 'companyPost:write'])]
    private ?string $category = null;

    #[ORM\Column(length: 255)]
    #[Groups(['companyPost:read', 'companyPost:write'])]
    private ?string $visibility = null;

    #[ORM\Column(length: 255)]
    #[Groups(['companyPost:read', 'companyPost:write'])]
    private ?string $imageUrl = null;

    #[ORM\Column]
    #[Groups(['companyPost:read', 'companyPost:write'])]
    private ?\DateTimeImmutable $publiedAt = null;

    #[ORM\Column]
    #[Groups(['companyPost:read', 'companyPost:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['companyPost:read', 'companyPost:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, PostComments>
     */
    #[ORM\OneToMany(targetEntity: PostComments::class, mappedBy: 'postId')]
    private Collection $postComments;

    /**
     * @var Collection<int, PostReaction>
     */
    #[ORM\OneToMany(targetEntity: PostReaction::class, mappedBy: 'postId')]
    private Collection $postReactions;

    public function __construct()
    {
        $this->postComments = new ArrayCollection();
        $this->postReactions = new ArrayCollection();
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

    public function getAuthorId(): ?User
    {
        return $this->authorId;
    }

    public function setAuthorId(?User $authorId): static
    {
        $this->authorId = $authorId;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

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

    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    #[Groups(['companyPost:read'])]
    public function getImage(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getPubliedAt(): ?\DateTimeImmutable
    {
        return $this->publiedAt;
    }

    #[Groups(['companyPost:read'])]
    public function getPostedAt(): ?\DateTimeImmutable
    {
        return $this->publiedAt;
    }

    public function setPubliedAt(\DateTimeImmutable $publiedAt): static
    {
        $this->publiedAt = $publiedAt;

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

    #[Groups(['companyPost:read'])]
    public function getCompanyName(): ?string
    {
        return $this->companyId?->getName();
    }

    #[Groups(['companyPost:read'])]
    public function getAuthor(): ?string
    {
        return $this->authorId?->getEmail();
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, PostComments>
     */
    public function getPostComments(): Collection
    {
        return $this->postComments;
    }

    public function addPostComment(PostComments $postComment): static
    {
        if (!$this->postComments->contains($postComment)) {
            $this->postComments->add($postComment);
            $postComment->setPostId($this);
        }

        return $this;
    }

    public function removePostComment(PostComments $postComment): static
    {
        if ($this->postComments->removeElement($postComment)) {
            // set the owning side to null (unless already changed)
            if ($postComment->getPostId() === $this) {
                $postComment->setPostId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PostReaction>
     */
    public function getPostReactions(): Collection
    {
        return $this->postReactions;
    }

    public function addPostReaction(PostReaction $postReaction): static
    {
        if (!$this->postReactions->contains($postReaction)) {
            $this->postReactions->add($postReaction);
            $postReaction->setPostId($this);
        }

        return $this;
    }

    public function removePostReaction(PostReaction $postReaction): static
    {
        if ($this->postReactions->removeElement($postReaction)) {
            // set the owning side to null (unless already changed)
            if ($postReaction->getPostId() === $this) {
                $postReaction->setPostId(null);
            }
        }

        return $this;
    }
}
