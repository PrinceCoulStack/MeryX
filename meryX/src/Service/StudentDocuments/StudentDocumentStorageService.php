<?php

namespace App\Service\StudentDocuments;

use App\Entity\StudentDocuments;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class StudentDocumentStorageService
{
    private const MAX_SIZE_BYTES = 10485760;

    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png',
    ];

    private const ALLOWED_TYPES = [
        'cv',
        'resume',
        'transcript',
        'certificate',
        'portfolio',
        'other',
    ];

    public function __construct(private readonly ParameterBagInterface $parameterBag)
    {
    }

    /**
     * @return array{storedFileName: string, relativePath: string, mimeType: string, fileSize: int}
     */
    public function storeUploadedFile(UploadedFile $file): array
    {
        $size = (int) $file->getSize();
        if ($size <= 0 || $size > self::MAX_SIZE_BYTES) {
            throw new \InvalidArgumentException('File size must be between 1 byte and 10MB');
        }

        $mimeType = (string) $file->getMimeType();
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new \InvalidArgumentException('Unsupported file type');
        }

        $extension = strtolower((string) $file->guessExtension());
        if ($extension === '') {
            $extension = 'bin';
        }

        $storedFileName = bin2hex(random_bytes(16)) . '.' . $extension;
        $directory = $this->getStorageDirectory();

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create document storage directory');
        }

        $file->move($directory, $storedFileName);

        return [
            'storedFileName' => $storedFileName,
            'relativePath' => 'uploads/student_documents/' . $storedFileName,
            'mimeType' => $mimeType,
            'fileSize' => $size,
        ];
    }

    public function resolveAbsolutePath(StudentDocuments $document): string
    {
        $baseDirectory = realpath($this->getStorageDirectory());
        if ($baseDirectory === false) {
            throw new \RuntimeException('Document storage directory is missing');
        }

        $candidate = realpath($baseDirectory . DIRECTORY_SEPARATOR . basename((string) $document->getFileName()));
        if ($candidate === false || !str_starts_with($candidate, $baseDirectory . DIRECTORY_SEPARATOR) && $candidate !== $baseDirectory) {
            throw new \RuntimeException('Invalid document path');
        }

        if (!is_file($candidate)) {
            throw new \RuntimeException('Document file not found');
        }

        return $candidate;
    }

    public function assertDocumentType(string $type): void
    {
        if (!in_array(strtolower($type), self::ALLOWED_TYPES, true)) {
            throw new \InvalidArgumentException('Unsupported document type');
        }
    }

    private function getStorageDirectory(): string
    {
        return (string) $this->parameterBag->get('student_documents_directory');
    }
}
