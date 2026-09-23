<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\AcademicRecordRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AcademicRecordRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['academicRecord:read']],
    denormalizationContext: ['groups' => ['academicRecord:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/academicRecords',
            name: 'listAcademicRecord'
        ),
        new Post(
            uriTemplate: '/academicRecords',
            name: 'createAcademicRecord'
        ),
        new Get(
            uriTemplate: '/academicRecords/{id}',
            name: 'getAcademicRecord'
        ),
        new Put(
            uriTemplate: '/academicRecords/{id}',
            name: 'updateAcademicRecord'
        ),
        new Delete(
            uriTemplate: '/academicRecords/{id}',
            name: 'deleteAcademicRecord'
        )
    ]
)]
class AcademicRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['academicRecord:read', 'student:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'academicRecords')]
    #[Groups(['academicRecord:read', 'academicRecord:write'])]
    private ?StudentProfile $studentProfileId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['academicRecord:read', 'academicRecord:write', 'student:read'])]
    private ?string $institutionName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['academicRecord:read', 'academicRecord:write', 'student:read'])]
    private ?string $degree = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['academicRecord:read', 'academicRecord:write', 'student:read'])]
    private ?string $fieldOfStudy = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Groups(['academicRecord:read', 'academicRecord:write', 'student:read'])]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['academicRecord:read', 'academicRecord:write', 'student:read'])]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[Groups(['academicRecord:read', 'academicRecord:write', 'student:read'])]
    private ?string $gpa = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['academicRecord:read', 'academicRecord:write', 'student:read'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['academicRecord:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['academicRecord:read'])]
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

    public function getInstitutionName(): ?string
    {
        return $this->institutionName;
    }

    public function setInstitutionName(string $institutionName): static
    {
        $this->institutionName = $institutionName;

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

    public function getFieldOfStudy(): ?string
    {
        return $this->fieldOfStudy;
    }

    public function setFieldOfStudy(?string $fieldOfStudy): static
    {
        $this->fieldOfStudy = $fieldOfStudy;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getGpa(): ?string
    {
        return $this->gpa;
    }

    public function setGpa(?string $gpa): static
    {
        $this->gpa = $gpa;

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
