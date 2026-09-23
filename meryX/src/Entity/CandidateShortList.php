<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\CandidateShortList\CreateCandidateShortList;
use App\Controller\CandidateShortList\ListCandidateShortList;
use App\Controller\CandidateShortList\UpdateCandidateShortList;
use App\Repository\CandidateShortListRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CandidateShortListRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['candidateShortList:read']],
    denormalizationContext: ['groups' => ['candidateShortList:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/candidateShortLists',
            controller: ListCandidateShortList::class,
            name: 'listCandidateShortList'
        ),
        new Post(
            uriTemplate: '/candidateShortLists',
            controller: CreateCandidateShortList::class,
            name: 'createCandidateShortList'
        ),
        new Get(
            uriTemplate: '/candidateShortLists/{id}',
            name: 'getCandidateShortList'
        ),
        new Put(
            uriTemplate: '/candidateShortLists/{id}',
            controller: UpdateCandidateShortList::class,
            name: 'updateCandidateShortList'
        ),
        new Delete(
            uriTemplate: '/candidateShortLists/{id}',
            name: 'deleteCandidateShortList'
        )
    ]
)]
class CandidateShortList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['candidateShortList:read', 'candidateShortList:write', 'company:read', 'student:read', 'opportunities:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'candidateShortLists')]
    #[Groups(['candidateShortList:read', 'candidateShortList:write', 'company:read'])]
    private ?Company $companyId = null;

    #[ORM\ManyToOne(inversedBy: 'candidateShortLists')]
    #[Groups(['candidateShortList:read', 'candidateShortList:write', 'student:read'])]
    private ?StudentProfile $studentProfileId = null;

    #[ORM\ManyToOne(inversedBy: 'candidateShortLists')]
    #[Groups(['candidateShortList:read', 'candidateShortList:write', 'opportunities:read'])]
    private ?Opportunities $opportunityId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['candidateShortList:read', 'candidateShortList:write'])]
    private ?string $note = null;

    #[ORM\Column]
    #[Groups(['candidateShortList:read', 'candidateShortList:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyId(): ?Company
    {
        return $this->companyId;
    }

    public function setCompanyId(?Company $companyId): static
    {
        $this->companyId = $companyId;

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

    public function getOpportunityId(): ?Opportunities
    {
        return $this->opportunityId;
    }

    public function setOpportunityId(?Opportunities $opportunityId): static
    {
        $this->opportunityId = $opportunityId;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(string $note): static
    {
        $this->note = $note;

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
}
