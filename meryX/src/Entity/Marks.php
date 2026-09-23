<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Marks\CreateMarks;
use App\Controller\Marks\ListMarks;
use App\Controller\Marks\UpdateMarks;
use App\Repository\MarksRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MarksRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['marks:read']],
    denormalizationContext: ['groups' => ['marks:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/marks',
            controller: ListMarks::class,
            name: 'listMarks'
        ),
        new Post(
            uriTemplate: '/marks',
            controller: CreateMarks::class,
            name: 'createMarks'
        ),
        new Get(
            uriTemplate: '/marks/{id}',
            name: 'getMarks'
        ),
        new Put(
            uriTemplate: '/marks/{id}',
            controller: UpdateMarks::class,
            name: 'updateMarks'
        ),
        new Delete(
            uriTemplate: '/marks/{id}',
            name: 'deleteMarks'
        )
    ]
)]
class Marks
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['marks:read', 'marks:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['marks:read', 'marks:write'])]
    private ?string $value = null;

    #[ORM\Column(length: 255)]
    #[Groups(['marks:read', 'marks:write'])]
    private ?string $semester = null;

    #[ORM\Column(length: 255)]
    #[Groups(['marks:read', 'marks:write'])]
    private ?string $year = null;

    #[ORM\Column]
    #[Groups(['marks:read', 'marks:write'])]
    private ?\DateTimeImmutable $markGiveAt = null;

    #[ORM\Column]
    #[Groups(['marks:read', 'marks:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

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

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(string $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getMarkGiveAt(): ?\DateTimeImmutable
    {
        return $this->markGiveAt;
    }

    public function setMarkGiveAt(\DateTimeImmutable $markGiveAt): static
    {
        $this->markGiveAt = $markGiveAt;

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
