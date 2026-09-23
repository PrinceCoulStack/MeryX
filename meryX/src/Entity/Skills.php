<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Skill\CreateSkills;
use App\Controller\Skill\ListSkills;
use App\Controller\Skill\UpdateSkill;
use App\Repository\SkillsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SkillsRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['skill:read']],
    denormalizationContext: ['groups' => ['skill:write']],
    operations:[
        new GetCollection(
            uriTemplate:'/skills',
            controller: ListSkills::class,
            name: 'listSkill'
        ),
        new Post(
            uriTemplate: "/skills",
            controller: CreateSkills::class,
            name: 'createSkill'
        ),
        new Get(
            uriTemplate: '/skills/{id}',
            name: 'getSkill'
        ),
        new Put(
            uriTemplate: '/skills/{id}',
            controller: UpdateSkill::class,
            name: 'updateSkill'
        ),
        new Delete(
            uriTemplate: '/skills/{id}',
            name: 'deleteSkill'
        ),
        new Get(
            uriTemplate: '/skill/{id}',
            name: 'getSkillLegacy'
        ),
        new Put(
            uriTemplate: '/skill/{id}',
            controller: UpdateSkill::class,
            name: 'updateSkillLegacy'
        ),
        new Delete(
            uriTemplate: '/skill/{id}',
            name: 'deleteSkillLegacy'
        )
    ]
)]
class Skills
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['skill:read', 'skill:write', 'student:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['skill:read', 'skill:write', 'student:read'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['skill:read', 'skill:write'])]
    private ?bool $isEnabled = null;

    #[ORM\Column]
    #[Groups(['skill:read', 'skill:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    #[Assert\Choice(choices: ['beginner', 'intermediate', 'advanced', 'expert', 'native'])]
    #[Groups(['skill:read', 'skill:write', 'student:read'])]
    private ?string $level = null;

    #[ORM\Column]
    #[Groups(['skill:read', 'skill:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'skill')]
    #[Groups(['skill:read', 'skill:write', 'student:read'])]
    private ?StudentProfile $studentProfileId = null;

    #[ORM\Column]
    #[Groups(['skill:read', 'skill:write'])]
    private ?bool $isDeleted = null;

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

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): static
    {
        $this->level = $level;

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

    public function isDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): static
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }
}
