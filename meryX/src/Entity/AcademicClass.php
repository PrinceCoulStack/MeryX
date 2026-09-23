<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\AcademicClass\CreateAcademicClass;
use App\Controller\AcademicClass\ListAcademicClass;
use App\Controller\AcademicClass\UpdateAcademicClass;
use App\Repository\AcademicClassRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AcademicClassRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['academicClass:read']],
    denormalizationContext: ['groups' => ['academicClass:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/academic-classes',
            controller: ListAcademicClass::class,
            name: 'listAcademicClass'
        ),
        new Post(
            uriTemplate: '/academic-classes',
            controller: CreateAcademicClass::class,
            name: 'createAcademicClass'
        ),
        new Get(
            uriTemplate: '/academic-classes/{id}',
            name: 'getAcademicClass'
        ),
        new Put(
            uriTemplate: '/academic-classes/{id}',
            controller: UpdateAcademicClass::class,
            name: 'updateAcademicClass'
        ),
        new Delete(
            uriTemplate: '/academic-classes/{id}',
            name: 'deleteAcademicClass'
        )
    ]
)]
class AcademicClass
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['academicClass:read', 'academicClass:write', 'department:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'academicClasses')]
    #[Groups(['academicClass:read', 'academicClass:write', 'department:read'])]
    private ?Department $departmentId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['academicClass:read', 'academicClass:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['academicClass:read', 'academicClass:write'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Groups(['academicClass:read', 'academicClass:write'])]
    private ?string $level = null;

    #[ORM\Column(length: 255)]
    #[Groups(['academicClass:read', 'academicClass:write'])]
    private ?string $academicYear = null;

    #[ORM\Column(length: 255)]
    #[Groups(['academicClass:read', 'academicClass:write'])]
    private ?string $semester = null;

    #[ORM\Column(length: 255)]
    #[Groups(['academicClass:read', 'academicClass:write'])]
    private ?string $capacity = null;

    #[ORM\Column]
    #[Groups(['academicClass:read', 'academicClass:write'])]
    private ?bool $isActive = null;

    #[ORM\Column]
    #[Groups(['academicClass:read', 'academicClass:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['academicClass:read', 'academicClass:write'])]
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

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getAcademicYear(): ?string
    {
        return $this->academicYear;
    }

    public function setAcademicYear(string $academicYear): static
    {
        $this->academicYear = $academicYear;

        return $this;
    }

    public function getSemester(): ?string
    {
        return $this->semester;
    }

    public function setSemester(string $semester): static
    {
        $this->semester = $semester;

        return $this;
    }

    public function getCapacity(): ?string
    {
        return $this->capacity;
    }

    public function setCapacity(string $capacity): static
    {
        $this->capacity = $capacity;

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
