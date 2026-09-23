<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Department\CreateDepartment;
use App\Controller\Department\ListDepartment;
use App\Controller\Department\UpdateDepartment;
use App\Repository\DepartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['department:read']],
    denormalizationContext: ['groups' => ['department:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/departments',
            controller: ListDepartment::class,
            name: 'listDepartment'
        ),
        new Post(
            uriTemplate: '/departments',
            controller: CreateDepartment::class,
            name: 'createDepartment'
        ),
        new Get(
            uriTemplate: '/departments/{id}',
            name: 'getDepartment'
        ),
        new Put(
            uriTemplate: '/departments/{id}',
            controller: UpdateDepartment::class,
            name: 'updateDepartment'
        ),
        new Delete(
            uriTemplate: '/departments/{id}',
            name: 'deleteDepartment'
        )
    ]
)]
class Department
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['department:read', 'department:write', 'university:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['department:read', 'department:write'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['department:read', 'department:write'])]
    private ?bool $isEnabled = null;

    #[ORM\Column]
    #[Groups(['department:read', 'department:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['department:read', 'department:write'])]
    private ?\DateTimeImmutable $UpdatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'departments')]
    #[Groups(['department:read', 'department:write', 'university:read'])]
    private ?University $universityId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['department:read', 'department:write'])]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    #[Groups(['department:read', 'department:write'])]
    private ?string $description = null;

    /**
     * @var Collection<int, AcademicClass>
     */
    #[ORM\OneToMany(targetEntity: AcademicClass::class, mappedBy: 'departmentId')]
    private Collection $academicClasses;

    /**
     * @var Collection<int, AcademicProgram>
     */
    #[ORM\OneToMany(targetEntity: AcademicProgram::class, mappedBy: 'departmentId')]
    private Collection $academicPrograms;

    public function __construct()
    {
        $this->academicClasses = new ArrayCollection();
        $this->academicPrograms = new ArrayCollection();
    }

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->UpdatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $UpdatedAt): static
    {
        $this->UpdatedAt = $UpdatedAt;

        return $this;
    }

    public function getUniversityId(): ?University
    {
        return $this->universityId;
    }

    public function setUniversityId(?University $universityId): static
    {
        $this->universityId = $universityId;

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

    /**
     * @return Collection<int, AcademicClass>
     */
    public function getAcademicClasses(): Collection
    {
        return $this->academicClasses;
    }

    public function addAcademicClass(AcademicClass $academicClass): static
    {
        if (!$this->academicClasses->contains($academicClass)) {
            $this->academicClasses->add($academicClass);
            $academicClass->setDepartmentId($this);
        }

        return $this;
    }

    public function removeAcademicClass(AcademicClass $academicClass): static
    {
        if ($this->academicClasses->removeElement($academicClass)) {
            // set the owning side to null (unless already changed)
            if ($academicClass->getDepartmentId() === $this) {
                $academicClass->setDepartmentId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AcademicProgram>
     */
    public function getAcademicPrograms(): Collection
    {
        return $this->academicPrograms;
    }

    public function addAcademicProgram(AcademicProgram $academicProgram): static
    {
        if (!$this->academicPrograms->contains($academicProgram)) {
            $this->academicPrograms->add($academicProgram);
            $academicProgram->setDepartmentId($this);
        }

        return $this;
    }

    public function removeAcademicProgram(AcademicProgram $academicProgram): static
    {
        if ($this->academicPrograms->removeElement($academicProgram)) {
            // set the owning side to null (unless already changed)
            if ($academicProgram->getDepartmentId() === $this) {
                $academicProgram->setDepartmentId(null);
            }
        }

        return $this;
    }
}
