<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\StudentProjectRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: StudentProjectRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['studentProject:read']],
    denormalizationContext: ['groups' => ['studentProject:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/studentProjects',
            name: 'listStudentProject'
        ),
        new Post(
            uriTemplate: '/studentProjects',
            name: 'createStudentProject'
        ),
        new Get(
            uriTemplate: '/studentProjects/{id}',
            name: 'getStudentProject'
        ),
        new Put(
            uriTemplate: '/studentProjects/{id}',
            name: 'updateStudentProject'
        ),
        new Delete(
            uriTemplate: '/studentProjects/{id}',
            name: 'deleteStudentProject'
        )
    ]
)]
class StudentProject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['studentProject:read', 'student:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'studentProjects')]
    #[Groups(['studentProject:read', 'studentProject:write'])]
    private ?StudentProfile $studentProfileId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['studentProject:read', 'studentProject:write', 'student:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['studentProject:read', 'studentProject:write', 'student:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['studentProject:read', 'studentProject:write', 'student:read'])]
    private ?string $role = null;

    #[ORM\Column]
    #[Groups(['studentProject:read', 'studentProject:write', 'student:read'])]
    private array $technologies = [];

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['studentProject:read', 'studentProject:write', 'student:read'])]
    private ?string $projectUrl = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['studentProject:read', 'studentProject:write', 'student:read'])]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['studentProject:read', 'studentProject:write', 'student:read'])]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column]
    #[Groups(['studentProject:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['studentProject:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getTechnologies(): array
    {
        return $this->technologies;
    }

    public function setTechnologies(array $technologies): static
    {
        $this->technologies = $technologies;

        return $this;
    }

    public function getProjectUrl(): ?string
    {
        return $this->projectUrl;
    }

    public function setProjectUrl(?string $projectUrl): static
    {
        $this->projectUrl = $projectUrl;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): static
    {
        $this->endedAt = $endedAt;

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
