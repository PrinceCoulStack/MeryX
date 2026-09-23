<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\TrainingEnrollment\CreateTrainingEnrollment;
use App\Controller\TrainingEnrollment\ListTrainingEnrollment;
use App\Controller\TrainingEnrollment\UpdateTrainingEnrollment;
use App\Repository\TrainingEnrollmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TrainingEnrollmentRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['trainingEnrollment:read']],
    denormalizationContext: ['groups' => ['trainingEnrollment:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/trainingEnrollments',
            controller: ListTrainingEnrollment::class,
            name: 'listTrainingEnrollment'
        ),
        new Post(
            uriTemplate: '/trainingEnrollments',
            controller: CreateTrainingEnrollment::class,
            name: 'createTrainingEnrollment'
        ),
        new Get(
            uriTemplate: '/trainingEnrollments/{id}',
            name: 'getTrainingEnrollment'
        ),
        new Put(
            uriTemplate: '/trainingEnrollments/{id}',
            controller: UpdateTrainingEnrollment::class,
            name: 'updateTrainingEnrollment'
        ),
        new Delete(
            uriTemplate: '/trainingEnrollments/{id}',
            name: 'deleteTrainingEnrollment'
        )
    ]
)]
class TrainingEnrollment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['trainingEnrollment:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'trainingEnrollments')]
    #[Groups(['trainingEnrollment:read', 'trainingEnrollment:write', 'training:read'])]
    private ?Training $trainingId = null;

    #[ORM\ManyToOne(inversedBy: 'trainingEnrollments')]
    #[Groups(['trainingEnrollment:read', 'trainingEnrollment:write', 'student:read'])]
    private ?StudentProfile $studentProfileId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['trainingEnrollment:read', 'trainingEnrollment:write'])]
    private ?string $status = null;

    #[ORM\Column]
    #[Groups(['trainingEnrollment:read', 'trainingEnrollment:write'])]
    private ?\DateTimeImmutable $enrolledAt = null;

    #[ORM\Column]
    #[Groups(['trainingEnrollment:read', 'trainingEnrollment:write'])]
    private ?\DateTimeImmutable $completedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTrainingId(): ?Training
    {
        return $this->trainingId;
    }

    public function setTrainingId(?Training $trainingId): static
    {
        $this->trainingId = $trainingId;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getEnrolledAt(): ?\DateTimeImmutable
    {
        return $this->enrolledAt;
    }

    public function setEnrolledAt(\DateTimeImmutable $enrolledAt): static
    {
        $this->enrolledAt = $enrolledAt;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;

        return $this;
    }
}
