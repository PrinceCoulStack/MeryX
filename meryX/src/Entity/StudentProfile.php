<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\StudentProfile\CreateStudentProfile;
use App\Controller\StudentProfile\DeleteStudentProfile;
use App\Controller\StudentProfile\GetStudentProfile;
use App\Controller\StudentProfile\ListStudentProfile;
use App\Controller\StudentProfile\ListStudentRegistrationRequests;
use App\Controller\StudentProfile\ReviewStudentRegistrationRequest;
use App\Controller\StudentProfile\UpdateStudentProfile;
use App\Repository\StudentProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StudentProfileRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['student:read']],
    denormalizationContext: ['groups' => ['student:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/studentProfiles',
            controller: ListStudentProfile::class,
            name: 'listStudentProfile',
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_UNIVERSITY') or is_granted('ROLE_STUDENT')"
        ),
        new GetCollection(
            uriTemplate: '/studentProfiles/requests',
            controller: ListStudentRegistrationRequests::class,
            name: 'listStudentRegistrationRequests',
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_UNIVERSITY')"
        ),
        new Post(
            uriTemplate: '/studentProfiles',
            controller: CreateStudentProfile::class,
            name: 'createStudentProfile',
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_UNIVERSITY') or is_granted('ROLE_STUDENT')"
        ),
        new Get(
            uriTemplate: '/studentProfiles/{id}',
            controller: GetStudentProfile::class,
            name: 'getStudentProfile',
            security: "is_granted('STUDENT_PROFILE_VIEW', object)"
        ),
        new Put(
            uriTemplate: '/studentProfiles/{id}',
            controller: UpdateStudentProfile::class,
            name: 'updateStudentProfile',
            security: "is_granted('STUDENT_PROFILE_EDIT', object)"
        ),
        new Patch(
            uriTemplate: '/studentProfiles/{id}',
            controller: ReviewStudentRegistrationRequest::class,
            name: 'patchStudentProfileApproval',
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_UNIVERSITY')"
        ),
        new Patch(
            uriTemplate: '/studentProfiles/{id}/review',
            controller: ReviewStudentRegistrationRequest::class,
            name: 'reviewStudentRegistrationRequest',
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_UNIVERSITY')"
        ),
        new Delete(
            uriTemplate: '/studentProfiles/{id}',
            controller: DeleteStudentProfile::class,
            name: 'deleteStudentProfile',
            security: "is_granted('STUDENT_PROFILE_DELETE', object)"
        )
    ]
)]
class StudentProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['student:read', 'student:write'])]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[Groups(['student:read', 'student:write', 'user:read'])]
    // nullable == false
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userId = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['student:read', 'student:write'])]
    private ?string $profileUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['student:read', 'student:write'])]
    private ?string $bio = null;

    #[ORM\Column]
    #[Groups(['student:read', 'student:write'])]
    private array $skills = [];

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Assert\Range(min: 0, max: 100)]
    #[Groups(['student:read', 'student:write'])]
    private ?int $profileCompletion = null;

    #[ORM\Column]
    #[Groups(['student:read', 'student:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['student:read', 'student:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['student:read', 'student:write'])]
    private ?string $fullName = null;

    #[ORM\Column(length: 255)]
    #[Assert\Regex(pattern: '/^(?:[0-3](?:\.\d{1,2})?|4(?:\.0{1,2})?)$/', message: 'GPA must be between 0.00 and 4.00')]
    #[Groups(['student:read', 'student:write'])]
    private ?string $gpa = null;

    #[ORM\ManyToOne(inversedBy: 'studentProfiles')]
    #[Groups(['student:read', 'student:write', 'university:read'])]
    // nullable == false
    #[ORM\JoinColumn(nullable: false)]
    private ?University $universityId = null;

    /**
     * @var Collection<int, Skills>
     */
    #[ORM\OneToMany(targetEntity: Skills::class, mappedBy: 'studentProfileId', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['student:read', 'student:write', 'skill:read'])]
    private Collection $skill;

    /**
     * @var Collection<int, Language>
     */
    #[ORM\OneToMany(targetEntity: Language::class, mappedBy: 'studentProfileId', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['student:read', 'student:write', 'language:read'])]
    private Collection $languages;

    /**
     * @var Collection<int, StudentDocuments>
     */
    #[ORM\OneToMany(targetEntity: StudentDocuments::class, mappedBy: 'studentProfileId', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['student:read', 'student:write', 'studentDocument:read'])]
    private Collection $studentDocuments;

    /**
     * @var Collection<int, AcademicRecord>
     */
    #[ORM\OneToMany(targetEntity: AcademicRecord::class, mappedBy: 'studentProfileId', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['student:read', 'student:write', 'academicRecord:read'])]
    private Collection $academicRecords;

    /**
     * @var Collection<int, StudentProject>
     */
    #[ORM\OneToMany(targetEntity: StudentProject::class, mappedBy: 'studentProfileId', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['student:read', 'student:write', 'studentProject:read'])]
    private Collection $studentProjects;

    /**
     * @var Collection<int, Application>
     */
    #[ORM\OneToMany(targetEntity: Application::class, mappedBy: 'studentProfile')]
    #[Groups(['student:read', 'student:write', 'application:read'])]
    private Collection $applications;

    /**
     * @var Collection<int, CandidateShortList>
     */
    #[ORM\OneToMany(targetEntity: CandidateShortList::class, mappedBy: 'studentProfileId')]
    #[Groups(['student:read', 'student:write', 'candidateShortList:read'])]
    private Collection $candidateShortLists;

    /**
     * @var Collection<int, TrainingEnrollment>
     */
    #[ORM\OneToMany(targetEntity: TrainingEnrollment::class, mappedBy: 'studentProfileId')]
    #[Groups(['student:read', 'student:write', 'trainingEnrollment:read'])]
    private Collection $trainingEnrollments;

    #[ORM\Column(length: 255)]
    #[Assert\Choice(choices: ['male', 'female', 'other', 'prefer_not_to_say'])]
    #[Groups(['student:read', 'student:write'])]
    private ?string $gender = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['student:read', 'student:write'])]
    private ?string $program = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['student:read', 'student:write'])]
    private ?string $faculty = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['student:read', 'student:write'])]
    private ?string $internshipCycle = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['student:read', 'student:write'])]
    private ?string $academicYear = null;

    #[ORM\Column(length: 20, options: ['default' => 'pending'])]
    #[Groups(['student:read', 'student:write'])]
    private ?string $status = 'pending';

    #[ORM\Column(options: ['default' => false])]
    #[Groups(['student:read', 'student:write'])]
    private ?bool $isApproved = false;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['student:read', 'student:write'])]
    private ?array $verificationData = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['student:read', 'student:write'])]
    private ?string $reviewReason = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['student:read', 'student:write'])]
    private ?string $reviewNote = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['student:read', 'student:write'])]
    private ?\DateTimeImmutable $reviewedAt = null;

    public function __construct()
    {
        $this->skill = new ArrayCollection();
        $this->languages = new ArrayCollection();
        $this->studentDocuments = new ArrayCollection();
        $this->academicRecords = new ArrayCollection();
        $this->studentProjects = new ArrayCollection();
        $this->applications = new ArrayCollection();
        $this->candidateShortLists = new ArrayCollection();
        $this->trainingEnrollments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProfileUrl(): ?string
    {
        return $this->profileUrl;
    }

    public function setProfileUrl(?string $profileUrl): static
    {
        $this->profileUrl = $profileUrl;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    public function getSkills(): array
    {
        return $this->skills;
    }

    public function setSkills(array $skills): static
    {
        $this->skills = $skills;

        return $this;
    }

    public function getProfileCompletion(): ?int
    {
        return $this->profileCompletion;
    }

    public function setProfileCompletion(?int $profileCompletion): static
    {
        $this->profileCompletion = $profileCompletion;

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

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getGpa(): ?string
    {
        return $this->gpa;
    }

    public function setGpa(string $gpa): static
    {
        $this->gpa = $gpa;

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

    /**
     * @return Collection<int, Skills>
     */
    public function getSkill(): Collection
    {
        return $this->skill;
    }

    public function addSkill(Skills $skill): static
    {
        if (!$this->skill->contains($skill)) {
            $this->skill->add($skill);
            $skill->setStudentProfileId($this);
        }

        return $this;
    }

    public function removeSkill(Skills $skill): static
    {
        if ($this->skill->removeElement($skill)) {
            // set the owning side to null (unless already changed)
            if ($skill->getStudentProfileId() === $this) {
                $skill->setStudentProfileId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Language>
     */
    public function getLanguages(): Collection
    {
        return $this->languages;
    }

    public function addLanguage(Language $language): static
    {
        if (!$this->languages->contains($language)) {
            $this->languages->add($language);
            $language->setStudentProfileId($this);
        }

        return $this;
    }

    public function removeLanguage(Language $language): static
    {
        if ($this->languages->removeElement($language)) {
            // set the owning side to null (unless already changed)
            if ($language->getStudentProfileId() === $this) {
                $language->setStudentProfileId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StudentDocuments>
     */
    public function getStudentDocuments(): Collection
    {
        return $this->studentDocuments;
    }

    public function addStudentDocument(StudentDocuments $studentDocument): static
    {
        if (!$this->studentDocuments->contains($studentDocument)) {
            $this->studentDocuments->add($studentDocument);
            $studentDocument->setStudentProfileId($this);
        }

        return $this;
    }

    public function removeStudentDocument(StudentDocuments $studentDocument): static
    {
        if ($this->studentDocuments->removeElement($studentDocument)) {
            // set the owning side to null (unless already changed)
            if ($studentDocument->getStudentProfileId() === $this) {
                $studentDocument->setStudentProfileId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AcademicRecord>
     */
    public function getAcademicRecords(): Collection
    {
        return $this->academicRecords;
    }

    public function addAcademicRecord(AcademicRecord $academicRecord): static
    {
        if (!$this->academicRecords->contains($academicRecord)) {
            $this->academicRecords->add($academicRecord);
            $academicRecord->setStudentProfileId($this);
        }

        return $this;
    }

    public function removeAcademicRecord(AcademicRecord $academicRecord): static
    {
        if ($this->academicRecords->removeElement($academicRecord)) {
            // set the owning side to null (unless already changed)
            if ($academicRecord->getStudentProfileId() === $this) {
                $academicRecord->setStudentProfileId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StudentProject>
     */
    public function getStudentProjects(): Collection
    {
        return $this->studentProjects;
    }

    public function addStudentProject(StudentProject $studentProject): static
    {
        if (!$this->studentProjects->contains($studentProject)) {
            $this->studentProjects->add($studentProject);
            $studentProject->setStudentProfileId($this);
        }

        return $this;
    }

    public function removeStudentProject(StudentProject $studentProject): static
    {
        if ($this->studentProjects->removeElement($studentProject)) {
            // set the owning side to null (unless already changed)
            if ($studentProject->getStudentProfileId() === $this) {
                $studentProject->setStudentProfileId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Application>
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(Application $application): static
    {
        if (!$this->applications->contains($application)) {
            $this->applications->add($application);
            $application->setStudentProfile($this);
        }

        return $this;
    }

    public function removeApplication(Application $application): static
    {
        if ($this->applications->removeElement($application)) {
            // set the owning side to null (unless already changed)
            if ($application->getStudentProfile() === $this) {
                $application->setStudentProfile(null);
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
            $candidateShortList->setStudentProfileId($this);
        }

        return $this;
    }

    public function removeCandidateShortList(CandidateShortList $candidateShortList): static
    {
        if ($this->candidateShortLists->removeElement($candidateShortList)) {
            // set the owning side to null (unless already changed)
            if ($candidateShortList->getStudentProfileId() === $this) {
                $candidateShortList->setStudentProfileId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TrainingEnrollment>
     */
    public function getTrainingEnrollments(): Collection
    {
        return $this->trainingEnrollments;
    }

    public function addTrainingEnrollment(TrainingEnrollment $trainingEnrollment): static
    {
        if (!$this->trainingEnrollments->contains($trainingEnrollment)) {
            $this->trainingEnrollments->add($trainingEnrollment);
            $trainingEnrollment->setStudentProfileId($this);
        }

        return $this;
    }

    public function removeTrainingEnrollment(TrainingEnrollment $trainingEnrollment): static
    {
        if ($this->trainingEnrollments->removeElement($trainingEnrollment)) {
            // set the owning side to null (unless already changed)
            if ($trainingEnrollment->getStudentProfileId() === $this) {
                $trainingEnrollment->setStudentProfileId(null);
            }
        }

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getProgram(): ?string
    {
        return $this->program;
    }

    public function setProgram(?string $program): static
    {
        $this->program = $program;

        return $this;
    }

    public function getFaculty(): ?string
    {
        return $this->faculty;
    }

    public function setFaculty(?string $faculty): static
    {
        $this->faculty = $faculty;

        return $this;
    }

    public function getInternshipCycle(): ?string
    {
        return $this->internshipCycle;
    }

    public function setInternshipCycle(?string $internshipCycle): static
    {
        $this->internshipCycle = $internshipCycle;

        return $this;
    }

    public function getAcademicYear(): ?string
    {
        return $this->academicYear;
    }

    public function setAcademicYear(?string $academicYear): static
    {
        $this->academicYear = $academicYear;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = strtolower(trim($status));

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

    public function getVerificationData(): ?array
    {
        return $this->verificationData;
    }

    public function setVerificationData(?array $verificationData): static
    {
        $this->verificationData = $verificationData;

        return $this;
    }

    public function getReviewReason(): ?string
    {
        return $this->reviewReason;
    }

    public function setReviewReason(?string $reviewReason): static
    {
        $this->reviewReason = $reviewReason;

        return $this;
    }

    public function getReviewNote(): ?string
    {
        return $this->reviewNote;
    }

    public function setReviewNote(?string $reviewNote): static
    {
        $this->reviewNote = $reviewNote;

        return $this;
    }

    public function getReviewedAt(): ?\DateTimeImmutable
    {
        return $this->reviewedAt;
    }

    public function setReviewedAt(?\DateTimeImmutable $reviewedAt): static
    {
        $this->reviewedAt = $reviewedAt;

        return $this;
    }
}
