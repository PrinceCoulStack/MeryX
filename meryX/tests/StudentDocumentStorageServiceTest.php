<?php

namespace App\Tests;

use App\Entity\StudentDocuments;
use App\Service\StudentDocuments\StudentDocumentStorageService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class StudentDocumentStorageServiceTest extends TestCase
{
    private string $storageDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storageDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'student_doc_test_' . bin2hex(random_bytes(4));
        mkdir($this->storageDir, 0775, true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (is_dir($this->storageDir)) {
            $files = glob($this->storageDir . DIRECTORY_SEPARATOR . '*');
            if (is_array($files)) {
                foreach ($files as $file) {
                    @unlink($file);
                }
            }
            @rmdir($this->storageDir);
        }
    }

    public function testStoreAndResolveDocumentPath(): void
    {
        $params = $this->createMock(ParameterBagInterface::class);
        $params->method('get')->with('student_documents_directory')->willReturn($this->storageDir);

        $service = new StudentDocumentStorageService($params);

        $tmpFile = tempnam(sys_get_temp_dir(), 'upload_');
        file_put_contents($tmpFile, '%PDF-1.4 test content');

        $uploadedFile = new UploadedFile(
            $tmpFile,
            'resume.pdf',
            'application/pdf',
            null,
            true
        );

        $stored = $service->storeUploadedFile($uploadedFile);

        $this->assertArrayHasKey('storedFileName', $stored);
        $this->assertArrayHasKey('relativePath', $stored);
        $this->assertArrayHasKey('mimeType', $stored);
        $this->assertArrayHasKey('fileSize', $stored);

        $document = new StudentDocuments();
        $document->setFileName($stored['storedFileName']);

        $resolved = $service->resolveAbsolutePath($document);
        $this->assertFileExists($resolved);
    }

    public function testRejectUnsupportedType(): void
    {
        $params = $this->createMock(ParameterBagInterface::class);
        $params->method('get')->with('student_documents_directory')->willReturn($this->storageDir);

        $service = new StudentDocumentStorageService($params);

        $this->expectException(\InvalidArgumentException::class);
        $service->assertDocumentType('malware');
    }
}
