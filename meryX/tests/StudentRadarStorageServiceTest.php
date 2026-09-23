<?php

namespace App\Tests;

use App\Service\StudentProfile\StudentRadarStorageService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

final class StudentRadarStorageServiceTest extends TestCase
{
    private string $storageDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storageDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'student_radar_test_' . bin2hex(random_bytes(4));
        mkdir($this->storageDir, 0775, true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->removeDirectory($this->storageDir);
    }

    public function testIntegratesNestedRadarProofUpload(): void
    {
        $params = $this->createMock(ParameterBagInterface::class);
        $params->method('get')->with('student_radar_directory')->willReturn($this->storageDir);

        $service = new StudentRadarStorageService($params);

        $tmpFile = tempnam(sys_get_temp_dir(), 'radar_');
        file_put_contents($tmpFile, '%PDF-1.4 proof content');

        $uploadedFile = new UploadedFile(
            $tmpFile,
            'proof.pdf',
            'application/pdf',
            null,
            true
        );

        $request = new Request(
            [],
            [],
            [],
            [],
            [
                'studentProfiles' => [
                    'bio' => [
                        'radar' => [
                            'academic' => [
                                [
                                    'proofFile' => $uploadedFile,
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $bio = [
            'personal' => [],
            'academic' => [],
            'radar' => [
                'academic' => [['title' => 'Linear Algebra']],
                'certificate' => [],
                'numerique' => [],
                'langue' => [],
                'stage' => [],
            ],
        ];

        $updated = $service->integrateRadarProofFiles($bio, $request);

        $this->assertArrayHasKey('proofUrl', $updated['radar']['academic'][0]);
        $this->assertStringStartsWith('/uploads/student/radar/academic/', $updated['radar']['academic'][0]['proofUrl']);

        $relativePath = str_replace('/uploads/student/radar/', '', $updated['radar']['academic'][0]['proofUrl']);
        $this->assertFileExists($this->storageDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($directory);
    }
}
