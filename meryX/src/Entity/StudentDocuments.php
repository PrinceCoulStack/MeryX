<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\StudentDocuments\DownloadStudentDocument;
use App\Controller\StudentDocuments\PreviewStudentDocument;
use App\Controller\StudentDocuments\CreateStudentDocuments;
use App\Controller\StudentDocuments\ListStudentDocuments;
use App\Controller\StudentDocuments\UploadStudentDocument;
use App\Controller\StudentDocuments\UpdateStudentDocuments;
use App\Repository\StudentDocumentsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StudentDocumentsRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['studentDocument:read']],
    denormalizationContext: ['groups' => ['studentDocument:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/studentDocuments',
            controller: ListStudentDocuments::class,
            name: 'listStudentDocument',
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_UNIVERSITY') or is_granted('ROLE_STUDENT')"
        ),
        new Post(
            uriTemplate: '/studentDocuments',
            controller: CreateStudentDocuments::class,
            name: 'createStudentDocument',
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_UNIVERSITY') or is_granted('ROLE_STUDENT')"
        ),
        new Get(
            uriTemplate: '/studentDocuments/{id}',
            name: 'getStudentDocument',
            security: "is_granted('STUDENT_DOCUMENT_VIEW', object)"
        ),
        new Put(
            uriTemplate: '/studentDocuments/{id}',
            controller: UpdateStudentDocuments::class,
            name: 'updateStudentDocument',
            security: "is_granted('STUDENT_DOCUMENT_EDIT', object)"
        ),
        new Delete(
            uriTemplate: '/studentDocuments/{id}',
            name: 'deleteStudentDocument',
            security: "is_granted('STUDENT_DOCUMENT_DELETE', object)"
        ),
        new Post(
            uriTemplate: '/studentProfiles/{id}/documents/upload',
            controller: UploadStudentDocument::class,
            deserialize: false,
            output: false,
            name: 'uploadStudentDocument',
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_UNIVERSITY') or is_granted('ROLE_STUDENT')"
        ),
        new Get(
            uriTemplate: '/studentDocuments/{id}/download',
            controller: DownloadStudentDocument::class,
            read: true,
            output: false,
            name: 'downloadStudentDocument',
            security: "is_granted('STUDENT_DOCUMENT_VIEW', object)"
        ),
        new Get(
            uriTemplate: '/studentDocuments/{id}/preview',
            controller: PreviewStudentDocument::class,
            read: true,
            output: false,
            name: 'previewStudentDocument',
            security: "is_granted('STUDENT_DOCUMENT_VIEW', object)"
        )
    ]
)]
class StudentDocuments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['studentDocument:read', 'studentDocument:write', 'student:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Choice(choices: ['cv', 'resume', 'transcript', 'certificate', 'portfolio', 'other'])]
    #[Groups(['studentDocument:read', 'studentDocument:write'])]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['studentDocument:read', 'studentDocument:write'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['studentDocument:read', 'studentDocument:write'])]
    private ?string $fileName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['studentDocument:read', 'studentDocument:write'])]
    private ?string $filePath = null;

    #[ORM\Column(length: 255)]
    #[Groups(['studentDocument:read', 'studentDocument:write'])]
    private ?string $mimeType = null;

    #[ORM\Column(length: 255)]
    #[Groups(['studentDocument:read', 'studentDocument:write'])]
    private ?string $fileSize = null;

    #[ORM\Column]
    #[Groups(['studentDocument:read', 'studentDocument:write'])]
    private ?bool $isPublic = null;

    #[ORM\Column]
    #[Groups(['studentDocument:read', 'studentDocument:write'])]
    private ?\DateTimeImmutable $uploadedAt = null;

    #[ORM\Column]
    #[Groups(['studentDocument:read', 'studentDocument:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'studentDocuments')]
    #[Groups(['studentDocument:read', 'studentDocument:write', 'student:read'])]
    private ?StudentProfile $studentProfileId = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getFileSize(): ?string
    {
        return $this->fileSize;
    }

    public function setFileSize(string $fileSize): static
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeImmutable $uploadedAt): static
    {
        $this->uploadedAt = $uploadedAt;

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

    public function getStudentProfileId(): ?StudentProfile
    {
        return $this->studentProfileId;
    }

    public function setStudentProfileId(?StudentProfile $studentProfileId): static
    {
        $this->studentProfileId = $studentProfileId;

        return $this;
    }
}
