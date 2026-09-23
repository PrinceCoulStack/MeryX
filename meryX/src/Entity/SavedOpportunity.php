<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\SavedOpportunity\CreateSavedOpportunity;
use App\Controller\SavedOpportunity\DeleteSavedOpportunity;
use App\Controller\SavedOpportunity\ListSavedOpportunity;
use App\Repository\SavedOpportunityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SavedOpportunityRepository::class)]
#[ORM\Table(name: 'saved_opportunities')]
#[ORM\UniqueConstraint(name: 'uniq_saved_opportunity_student', columns: ['opportunity_id', 'student_id'])]
#[ORM\Index(name: 'idx_saved_opportunities_student_id', columns: ['student_id'])]
#[ORM\Index(name: 'idx_saved_opportunities_created_at', columns: ['created_at'])]
#[ApiResource(operations: [
    new GetCollection(
        uriTemplate: '/savedOpportunities',
        controller: ListSavedOpportunity::class,
        name: 'listSavedOpportunity',
        read: false,
        deserialize: false,
        security: "is_granted('IS_AUTHENTICATED_FULLY')"
    ),
    new Post(
        uriTemplate: '/savedOpportunities',
        controller: CreateSavedOpportunity::class,
        name: 'createSavedOpportunity',
        read: false,
        deserialize: false,
        security: "is_granted('IS_AUTHENTICATED_FULLY')"
    ),
    new Delete(
        uriTemplate: '/savedOpportunities/{id}',
        controller: DeleteSavedOpportunity::class,
        name: 'deleteSavedOpportunity',
        read: false,
        deserialize: false,
        security: "is_granted('IS_AUTHENTICATED_FULLY')"
    ),
])]
class SavedOpportunity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'opportunity_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Opportunities $opportunity = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'student_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?StudentProfile $student = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $savedDate = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOpportunity(): ?Opportunities
    {
        return $this->opportunity;
    }

    public function setOpportunity(?Opportunities $opportunity): static
    {
        $this->opportunity = $opportunity;

        return $this;
    }

    public function getStudent(): ?StudentProfile
    {
        return $this->student;
    }

    public function setStudent(?StudentProfile $student): static
    {
        $this->student = $student;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getSavedDate(): ?\DateTimeImmutable
    {
        return $this->savedDate;
    }

    public function setSavedDate(?\DateTimeImmutable $savedDate): static
    {
        $this->savedDate = $savedDate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
