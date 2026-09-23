<?php
namespace App\Entity;

use Symfony\Component\Serializer\Attribute\Groups;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\University\CreateUniversity;
use App\Controller\University\ListUniversity;
use App\Controller\University\UpdateUniversity;
use App\Repository\UniversityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: UniversityRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['university:read:list']],
    denormalizationContext: ['groups' => ['university:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/university',
            controller: ListUniversity::class,
            name: 'list_university',
            normalizationContext: ['groups' => ['university:read:list']]
        ),
        new Post(
            uriTemplate: '/university',
            controller: CreateUniversity::class,
            name: 'create_university',
            normalizationContext: ['groups' => ['university:read:item']],
            denormalizationContext: ['groups' => ['university:write']]
        ),
        new Get(
            uriTemplate: '/university/{id}',
            name: 'get_university',
            normalizationContext: ['groups' => ['university:read:item']]
        ),
        new Patch(
            uriTemplate: '/university/{id}',
            controller: UpdateUniversity::class,
            name: 'patch_university',
            normalizationContext: ['groups' => ['university:read:item']],
            denormalizationContext: ['groups' => ['university:write']]
        ),
        new Put(
            uriTemplate: '/university/{id}',
            controller: UpdateUniversity::class,
            name: 'update_university',
            normalizationContext: ['groups' => ['university:read:item']],
            denormalizationContext: ['groups' => ['university:write']]
        ),
        new Delete(
            uriTemplate: '/university/{id}',
            name: 'delete_university'
        )
    ]
)]
class University
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['university:read:list', 'university:read:item', 'university:read:settings'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['university:read:list', 'university:read:item', 'university:read:settings', 'university:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['university:read:list', 'university:read:item', 'university:read:settings', 'university:write'])]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Groups(['university:read:list', 'university:read:item', 'university:read:settings', 'university:write'])]
    private ?string $accreditationNumber = null;

    #[ORM\Column(length: 255)]
    #[Groups(['university:read:list', 'university:read:item', 'university:read:settings', 'university:write'])]
    private ?string $rankingScore = '0';

    #[ORM\Column]
    #[Groups(['university:read:list', 'university:read:item', 'university:read:settings', 'university:write'])]
    private ?bool $isApproved = false;

    #[ORM\Column]
    #[Groups(['university:read:list', 'university:read:item', 'university:read:settings', 'university:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['university:read:item', 'university:read:settings', 'university:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['university:read:item', 'university:read:settings', 'university:write'])]
    private ?string $websiteUrl = null;

    #[ORM\Column(length: 100)]
    #[Groups(['university:read:list', 'university:read:item', 'university:read:settings', 'university:write'])]
    private ?string $status = 'pending';

    #[ORM\Column(length: 255)]
    #[Groups(['university:read:item', 'university:read:settings', 'university:write'])]
    private ?string $registrationNumber = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['university:read:item', 'university:read:settings', 'university:write'])]
    private ?\DateTimeImmutable $verifiedAt = null;

    #[ORM\Column]
    #[Groups(['university:read:item', 'university:read:settings', 'university:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Company>
     */
    #[ORM\ManyToMany(targetEntity: Company::class, inversedBy: 'universities')]
    #[Groups(['company:read'])]
    #[MaxDepth(1)]
    private Collection $companyId;

    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    #[Groups(['university:read:settings', 'university:write'])]
    #[MaxDepth(1)]
    private ?User $userId = null;

    /**
     * @var Collection<int, StudentProfile>
     */
    #[ORM\OneToMany(targetEntity: StudentProfile::class, mappedBy: 'universityId')]
    #[Groups(['student:read'])]
    #[MaxDepth(1)]
    private Collection $studentProfiles;

    /**
     * @var Collection<int, Partnership>
     */
    #[ORM\OneToMany(targetEntity: Partnership::class, mappedBy: 'universityId')]
    #[Groups(['partner:read'])]
    #[MaxDepth(1)]
    private Collection $partnerships;

    /**
     * @var Collection<int, Department>
     */
    #[ORM\OneToMany(targetEntity: Department::class, mappedBy: 'universityId')]
    #[Groups(['department:read'])]
    #[MaxDepth(1)]
    private Collection $departments;

    #[ORM\Column(length: 255)]
    #[Groups(['university:read:item', 'university:read:settings', 'university:write'])]
    private ?string $logoUrl = null;

    #[ORM\Column]
    #[Groups(['university:read:list', 'university:read:item', 'university:read:settings', 'university:write'])]
    private ?bool $isDeleted = false;

    public function __construct()
    {
        $this->companyId = new ArrayCollection();
        $this->studentProfiles = new ArrayCollection();
        $this->partnerships = new ArrayCollection();
        $this->departments = new ArrayCollection();
        $this->rankingScore = '0';
        $this->isApproved = false;
        $this->status = 'pending';
        $this->isDeleted = false;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->verifiedAt = null;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getAccreditationNumber(): ?string
    {
        return $this->accreditationNumber;
    }

    public function setAccreditationNumber(string $accreditationNumber): static
    {
        $this->accreditationNumber = $accreditationNumber;

        return $this;
    }

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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

    public function getVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->verifiedAt;
    }

    public function setVerifiedAt(?\DateTimeImmutable $verifiedAt): static
    {
        $this->verifiedAt = $verifiedAt;

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
     * @return Collection<int, company>
     */
    public function getCompanyId(): Collection
    {
        return $this->companyId;
    }

    public function addCompanyId(company $companyId): static
    {
        if (!$this->companyId->contains($companyId)) {
            $this->companyId->add($companyId);
        }

        return $this;
    }

    public function removeCompanyId(company $companyId): static
    {
        $this->companyId->removeElement($companyId);

        return $this;
    }

    #[Groups(['university:read', 'university:write'])]
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

    #[Groups(['university:read', 'university:write'])]
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

    #[Groups(['university:read', 'university:write'])]
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

    /**
     * @return Collection<int, StudentProfile>
     */
    public function getStudentProfiles(): Collection
    {
        return $this->studentProfiles;
    }

    public function addStudentProfile(StudentProfile $studentProfile): static
    {
        if (!$this->studentProfiles->contains($studentProfile)) {
            $this->studentProfiles->add($studentProfile);
            $studentProfile->setUniversityId($this);
        }

        return $this;
    }

    public function removeStudentProfile(StudentProfile $studentProfile): static
    {
        if ($this->studentProfiles->removeElement($studentProfile)) {
            // set the owning side to null (unless already changed)
            if ($studentProfile->getUniversityId() === $this) {
                $studentProfile->setUniversityId(null);
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
            $partnership->setUniversityId($this);
        }

        return $this;
    }

    public function removePartnership(Partnership $partnership): static
    {
        if ($this->partnerships->removeElement($partnership)) {
            // set the owning side to null (unless already changed)
            if ($partnership->getUniversityId() === $this) {
                $partnership->setUniversityId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Department>
     */
    public function getDepartments(): Collection
    {
        return $this->departments;
    }

    public function addDepartment(Department $department): static
    {
        if (!$this->departments->contains($department)) {
            $this->departments->add($department);
            $department->setUniversityId($this);
        }

        return $this;
    }

    public function removeDepartment(Department $department): static
    {
        if ($this->departments->removeElement($department)) {
            // set the owning side to null (unless already changed)
            if ($department->getUniversityId() === $this) {
                $department->setUniversityId(null);
            }
        }

        return $this;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function setLogoUrl(string $logoUrl): static
    {
        $this->logoUrl = $logoUrl;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): static
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }
}
