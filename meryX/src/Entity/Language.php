<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Language\CreateLanguage;
use App\Controller\Language\ListLanguage;
use App\Controller\Language\UpdateLanguage;
use App\Repository\LanguageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LanguageRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['language:read']],
    denormalizationContext: ['groups' => ['language:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/languages',
            controller: ListLanguage::class,
            name: 'listLanguage'
        ),
        new Post(
            uriTemplate: '/languages',
            controller: CreateLanguage::class,
            name: 'createLanguage'
        ),
        new Get(
            uriTemplate: '/languages/{id}',
            name: 'getLanguage'
        ),
        new Put(
            uriTemplate: '/languages/{id}',
            controller: UpdateLanguage::class,
            name: 'updateLanguage'
        ),
        new Delete(
            uriTemplate: '/languages/{id}',
            name: 'deleteLanguage'
        )
    ]
)]
class Language
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['language:read', 'language:write', 'student:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['language:read', 'language:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\Choice(choices: ['beginner', 'intermediate', 'advanced', 'expert', 'native'])]
    #[Groups(['language:read', 'language:write'])]
    private ?string $level = null;

    #[ORM\Column]
    #[Groups(['language:read', 'language:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['language:read', 'language:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'languages')]
    #[Groups(['language:read', 'language:write', 'student:read'])]
    private ?StudentProfile $studentProfileId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): static
    {
        $this->level = $level;

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

    public function getStudentProfileId(): ?StudentProfile
    {
        return $this->studentProfileId;
    }

    public function setStudentProfileId(?StudentProfile $studentProfileId): static
    {
        $this->studentProfileId = $studentProfileId;

        return $this;
    }
}
