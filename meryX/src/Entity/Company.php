<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Company\CreateCompany;
use App\Controller\Company\ListCompany;
use App\Controller\Company\UpdateCompany;
use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['company:read']],
    denormalizationContext: ['groups' => ['company:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/companies',
            controller: ListCompany::class,
            name: 'listCompany'
        ),
        new Post(
            uriTemplate: '/companies',
            controller: CreateCompany::class,
            name: 'createCompany'
        ),
        new Get(
            uriTemplate: '/companies/{id}',
            name: 'getCompany'
        ),
        new Patch(
            uriTemplate: '/companies/{id}',
            controller: UpdateCompany::class,
            name: 'patchCompany'
        ),
        new Put(
            uriTemplate: '/companies/{id}',
            controller: UpdateCompany::class,
            name: 'updateCompany'
        ),
        new Delete(
            uriTemplate: '/companies/{id}',
            name: 'deleteCompany'
        )
    ]
)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['company:read', 'company:write', 'university:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['company:read', 'company:write'])]
    private ?string $name = null;

    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    #[Groups(['company:read', 'company:write', 'user:read'])]
    private ?User $userId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['company:read', 'company:write'])]
    private ?string $logo = null;

    #[ORM\Column(length: 255)]
    #[Groups(['company:read', 'company:write'])]
    private ?string $sector = null;

    // #[ORM\Column(length: 255)]
    // #[Groups(['company:read', 'company:write'])]
    // private ?string $subscriptionType = null;

    #[ORM\Column(length: 255)]
    #[Groups(['company:read', 'company:write'])]
    private ?string $rankingScore = null;

    #[ORM\Column]
    #[Groups(['company:read', 'company:write'])]
    private ?bool $isApproved = null;

    // This field is intentionally not mapped to a database column because the current schema
    // does not contain a verification_documents table column. It can still be exposed/used
    // in the API payload without causing Doctrine to emit an invalid SQL INSERT/UPDATE.
    #[Groups(['company:read', 'company:write'])]
    private ?array $verificationDocuments = [];

    /**
     * @var Collection<int, University>
     */
    #[ORM\ManyToMany(targetEntity: University::class, mappedBy: 'companyId')]
    private Collection $universities;

    #[ORM\Column(length: 255)]
    #[Groups(['company:read', 'company:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['company:read', 'company:write'])]
    private ?string $websiteUrl = null;

    #[ORM\Column(length: 255)]
    #[Groups(['company:read', 'company:write'])]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    #[Groups(['company:read', 'company:write'])]
    private ?string $registrationNumber = null;

    #[ORM\Column(length: 255)]
    #[Groups(['company:read', 'company:write'])]
    private ?string $taxId = null;

    #[ORM\Column]
    #[Groups(['company:read', 'company:write'])]
    private ?\DateTimeImmutable $verifiedAt = null;

    #[ORM\Column]
    #[Groups(['company:read', 'company:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['company:read', 'company:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Opportunities>
     */
    #[ORM\OneToMany(targetEntity: Opportunities::class, mappedBy: 'companyId')]
    private Collection $opportunities;

    /**
     * @var Collection<int, CandidateShortList>
     */
    #[ORM\OneToMany(targetEntity: CandidateShortList::class, mappedBy: 'companyId')]
    private Collection $candidateShortLists;

    /**
     * @var Collection<int, Training>
     */
    #[ORM\OneToMany(targetEntity: Training::class, mappedBy: 'companyId')]
    private Collection $trainings;

    /**
     * @var Collection<int, CompanyPost>
     */
    #[ORM\OneToMany(targetEntity: CompanyPost::class, mappedBy: 'companyId')]
    private Collection $companyPosts;

    /**
     * @var Collection<int, Partnership>
     */
    #[ORM\OneToMany(targetEntity: Partnership::class, mappedBy: 'companyId')]
    private Collection $partnerships;

    public function __construct()
    {
        $this->universities = new ArrayCollection();
        $this->opportunities = new ArrayCollection();
        $this->candidateShortLists = new ArrayCollection();
        $this->trainings = new ArrayCollection();
        $this->companyPosts = new ArrayCollection();
        $this->partnerships = new ArrayCollection();
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

    #[Groups(['company:read', 'company:write'])]
    public function getEmail(): ?string
    {
        return $this->userId?->getEmail();
    }

    public function setEmail(?string $email): static
    {
        if ($this->userId !== null && $email !== null) {
            $this->userId->setEmail($email);
        }

        return $this;
    }

    #[Groups(['company:read', 'company:write'])]
    public function getCountry(): ?string
    {
        return $this->userId?->getAddressId()?->getCountry();
    }

    public function setCountry(?string $country): static
    {
        if ($this->userId !== null && $this->userId->getAddressId() !== null && $country !== null) {
            $this->userId->getAddressId()->setCountry($country);
        }

        return $this;
    }

    #[Groups(['company:read', 'company:write'])]
    public function getAddressId(): ?Address
    {
        return $this->userId?->getAddressId();
    }

    public function setAddressId(?Address $address): static
    {
        if ($this->userId !== null) {
            $this->userId->setAddressId($address);
        }

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getSector(): ?string
    {
        return $this->sector;
    }

    public function setSector(string $sector): static
    {
        $this->sector = $sector;

        return $this;
    }

    // public function getSubscriptionType(): ?string
    // {
    //     return $this->subscriptionType;
    // }

    // public function setSubscriptionType(string $subscriptionType): static
    // {
    //     $this->subscriptionType = $subscriptionType;

    //     return $this;
    // }

    public function getRankingScore(): ?string
    {
        return $this->rankingScore;
    }

    public function setRankingScore(string $rankingScore): static
    {
        $this->rankingScore = $rankingScore;

        return $this;
    }

    public function isApproved(): ?bool
    {
        return $this->isApproved;
    }

    public function setIsApproved(bool $isApproved): static
    {
        $this->isApproved = $isApproved;

        return $this;
    }

    public function getVerificationDocuments(): ?array
    {
        return $this->verificationDocuments ?? [];
    }

    public function setVerificationDocuments(?array $verificationDocuments): static
    {
        $this->verificationDocuments = $verificationDocuments ?? [];

        return $this;
    }

    /**
     * @return Collection<int, University>
     */
    public function getUniversities(): Collection
    {
        return $this->universities;
    }

    public function addUniversity(University $university): static
    {
        if (!$this->universities->contains($university)) {
            $this->universities->add($university);
            $university->addCompanyId($this);
        }

        return $this;
    }

    public function removeUniversity(University $university): static
    {
        if ($this->universities->removeElement($university)) {
            $university->removeCompanyId($this);
        }

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

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl(?string $websiteUrl): static
    {
        $this->websiteUrl = $websiteUrl;

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

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(string $registrationNumber): static
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getTaxId(): ?string
    {
        return $this->taxId;
    }

    public function setTaxId(string $taxId): static
    {
        $this->taxId = $taxId;

        return $this;
    }

    public function getVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->verifiedAt;
    }

    public function setVerifiedAt(\DateTimeImmutable $verifiedAt): static
    {
        $this->verifiedAt = $verifiedAt;

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

    /**
     * @return Collection<int, Opportunities>
     */
    public function getOpportunities(): Collection
    {
        return $this->opportunities;
    }

    public function addOpportunity(Opportunities $opportunity): static
    {
        if (!$this->opportunities->contains($opportunity)) {
            $this->opportunities->add($opportunity);
            $opportunity->setCompanyId($this);
        }

        return $this;
    }

    public function removeOpportunity(Opportunities $opportunity): static
    {
        if ($this->opportunities->removeElement($opportunity)) {
            // set the owning side to null (unless already changed)
            if ($opportunity->getCompanyId() === $this) {
                $opportunity->setCompanyId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CandidateShortList>
     */
    public function getCandidateShortLists(): Collection
    {
        return $this->candidateShortLists;
    }

    public function addCandidateShortList(CandidateShortList $candidateShortList): static
    {
        if (!$this->candidateShortLists->contains($candidateShortList)) {
            $this->candidateShortLists->add($candidateShortList);
            $candidateShortList->setCompanyId($this);
        }

        return $this;
    }

    public function removeCandidateShortList(CandidateShortList $candidateShortList): static
    {
        if ($this->candidateShortLists->removeElement($candidateShortList)) {
            // set the owning side to null (unless already changed)
            if ($candidateShortList->getCompanyId() === $this) {
                $candidateShortList->setCompanyId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Training>
     */
    public function getTrainings(): Collection
    {
        return $this->trainings;
    }

    public function addTraining(Training $training): static
    {
        if (!$this->trainings->contains($training)) {
            $this->trainings->add($training);
            $training->setCompanyId($this);
        }

        return $this;
    }

    public function removeTraining(Training $training): static
    {
        if ($this->trainings->removeElement($training)) {
            // set the owning side to null (unless already changed)
            if ($training->getCompanyId() === $this) {
                $training->setCompanyId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CompanyPost>
     */
    public function getCompanyPosts(): Collection
    {
        return $this->companyPosts;
    }

    public function addCompanyPost(CompanyPost $companyPost): static
    {
        if (!$this->companyPosts->contains($companyPost)) {
            $this->companyPosts->add($companyPost);
            $companyPost->setCompanyId($this);
        }

        return $this;
    }

    public function removeCompanyPost(CompanyPost $companyPost): static
    {
        if ($this->companyPosts->removeElement($companyPost)) {
            // set the owning side to null (unless already changed)
            if ($companyPost->getCompanyId() === $this) {
                $companyPost->setCompanyId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Partnership>
     */
    public function getPartnerships(): Collection
    {
        return $this->partnerships;
    }

    public function addPartnership(Partnership $partnership): static
    {
        if (!$this->partnerships->contains($partnership)) {
            $this->partnerships->add($partnership);
            $partnership->setCompanyId($this);
        }

        return $this;
    }

    public function removePartnership(Partnership $partnership): static
    {
        if ($this->partnerships->removeElement($partnership)) {
            // set the owning side to null (unless already changed)
            if ($partnership->getCompanyId() === $this) {
                $partnership->setCompanyId(null);
            }
        }

        return $this;
    }
}
