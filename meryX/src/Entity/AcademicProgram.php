<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\AcademicProgram\CreateAcademicProgram;
use App\Controller\AcademicProgram\ListAcademicProgram;
use App\Controller\AcademicProgram\UpdateAcademicProgram;
use App\Repository\AcademicProgramRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AcademicProgramRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['academicProgram:read']],
    denormalizationContext: ['groups' => ['academicProgram:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/academic-programs',
            controller: ListAcademicProgram::class,
            name: 'listAcademicProgram'
        ),
        new Post(
            uriTemplate: '/academic-programs',
            controller: CreateAcademicProgram::class,
            name: 'createAcademicProgram'
        ),
        new Get(
            uriTemplate: '/academic-programs/{id}',
            name: 'getAcademicProgram'
        ),
        new Put(
            uriTemplate: '/academic-programs/{id}',
            controller: UpdateAcademicProgram::class,
            name: 'updateAcademicProgram'
        ),
        new Delete(
            uriTemplate: '/academic-programs/{id}',
            name: 'deleteAcademicProgram'
        )
    ]
)]
class AcademicProgram
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['academicProgram:read', 'academicProgram:write', 'department:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'academicPrograms')]
    #[Groups(['academicProgram:read', 'academicProgram:write', 'department:read'])]
    private ?Department $departmentId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['academicProgram:read', 'academicProgram:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['academicProgram:read', 'academicProgram:write'])]
    private ?string $code = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['academicProgram:read', 'academicProgram:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(['academicProgram:read', 'academicProgram:write'])]
    private ?string $duration = null;

    #[ORM\Column(length: 255)]
    #[Groups(['academicProgram:read', 'academicProgram:write'])]
    private ?string $degree = null;

    #[ORM\Column]
    #[Groups(['academicProgram:read', 'academicProgram:write'])]
    private ?bool $isActive = null;

    #[ORM\Column]
    #[Groups(['academicProgram:read', 'academicProgram:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['academicProgram:read', 'academicProgram:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepartmentId(): ?Department
    {
        return $this->departmentId;
    }

    public function setDepartmentId(?Department $departmentId): static
    {
        $this->departmentId = $departmentId;

        return $this;
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(string $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDegree(): ?string
    {
        return $this->degree;
    }

    public function setDegree(string $degree): static
    {
        $this->degree = $degree;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

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
