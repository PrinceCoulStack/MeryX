<?php

namespace App\Service\StudentProfile;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class StudentRadarStorageService
{
    private const MAX_SIZE_BYTES = 10485760;

    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png',
    ];

    private const RADAR_CATEGORIES = [
        'academic',
        'certificate',
        'numerique',
        'langue',
        'stage',
    ];

    public function __construct(private readonly ParameterBagInterface $parameterBag)
    {
    }

    public function hasRadarProofFiles(Request $request): bool
    {
        return $this->extractRadarFiles($request) !== [];
    }

    public function normalizeBioPayload(mixed $bio): array
    {
        if (is_string($bio)) {
            $decoded = json_decode($bio, true);
            $bio = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($bio)) {
            $bio = [];
        }

        $bio['personal'] = isset($bio['personal']) && is_array($bio['personal']) ? $bio['personal'] : [];
        $bio['academic'] = isset($bio['academic']) && is_array($bio['academic']) ? $bio['academic'] : [];
        $bio['radar'] = isset($bio['radar']) && is_array($bio['radar']) ? $bio['radar'] : [];

        foreach (self::RADAR_CATEGORIES as $category) {
            if (!isset($bio['radar'][$category]) || !is_array($bio['radar'][$category])) {
                $bio['radar'][$category] = [];
            }
            // tolerate the `{ entries: [...] }` wrapper shape some clients send instead of a flat list
            if (isset($bio['radar'][$category]['entries']) && is_array($bio['radar'][$category]['entries'])) {
                $bio['radar'][$category] = $bio['radar'][$category]['entries'];
            }
        }

        return $bio;
    }

    public function mergeBio(array $baseBio, array $incomingBio): array
    {
        $base = $this->normalizeBioPayload($baseBio);
        $incoming = $this->normalizeBioPayload($incomingBio);

        $merged = $base;
        $merged['personal'] = array_replace($base['personal'], $incoming['personal']);
        $merged['academic'] = array_replace($base['academic'], $incoming['academic']);

        foreach (self::RADAR_CATEGORIES as $category) {
            if ($incoming['radar'][$category] !== []) {
                $merged['radar'][$category] = $incoming['radar'][$category];
            }
        }

        return $merged;
    }

    public function integrateRadarProofFiles(array $bio, Request $request): array
    {
        $bio = $this->normalizeBioPayload($bio);
        $filesByCategory = $this->extractRadarFiles($request);

        foreach ($filesByCategory as $category => $indexedFiles) {
            $entries = $bio['radar'][$category] ?? [];

            foreach ($indexedFiles as $index => $file) {
                if (!$file instanceof UploadedFile) {
                    continue;
                }

                $stored = $this->storeRadarProofFile($category, $file);
                if (!isset($entries[$index]) || !is_array($entries[$index])) {
                    $entries[$index] = [];
                }

                $entries[$index]['proofUrl'] = $stored['url'];
                $entries[$index]['proofName'] = $stored['originalName'];
                $entries[$index]['proofMimeType'] = $stored['mimeType'];
                $entries[$index]['proofSize'] = $stored['fileSize'];
            }

            ksort($entries);
            $bio['radar'][$category] = array_values($entries);
        }

        return $bio;
    }

    /**
     * @return array{url: string, originalName: string, mimeType: string, fileSize: int}
     */
    private function storeRadarProofFile(string $category, UploadedFile $file): array
    {
        if (!in_array($category, self::RADAR_CATEGORIES, true)) {
            throw new \InvalidArgumentException('Unsupported radar category');
        }

        $size = (int) $file->getSize();
        if ($size <= 0 || $size > self::MAX_SIZE_BYTES) {
            throw new \InvalidArgumentException('Radar proof file size must be between 1 byte and 10MB');
        }

        $mimeType = (string) $file->getMimeType();
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new \InvalidArgumentException('Unsupported radar proof file type');
        }

        $extension = strtolower((string) $file->guessExtension());
        if ($extension === '') {
            $extension = 'bin';
        }

        $storedFileName = bin2hex(random_bytes(16)) . '.' . $extension;
        $directory = $this->getRadarStorageDirectory() . DIRECTORY_SEPARATOR . $category;

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create radar proof storage directory');
        }

        $file->move($directory, $storedFileName);

        return [
            'url' => '/uploads/student/radar/' . $category . '/' . $storedFileName,
            'originalName' => (string) $file->getClientOriginalName(),
            'mimeType' => $mimeType,
            'fileSize' => $size,
        ];
    }

    /**
     * @return array<string, array<int, UploadedFile>>
     */
    private function extractRadarFiles(Request $request): array
    {
        $result = [];

        $walker = function (mixed $node, array $path) use (&$walker, &$result): void {
            if ($node instanceof UploadedFile) {
                [$category, $index] = $this->resolveCategoryAndIndex($path);
                if ($category === null || $index === null) {
                    return;
                }

                if (!isset($result[$category])) {
                    $result[$category] = [];
                }

                $result[$category][$index] = $node;
                return;
            }

            if (!is_array($node)) {
                return;
            }

            foreach ($node as $key => $value) {
                $walker($value, [...$path, (string) $key]);
            }
        };

        $walker($request->files->all(), []);

        return $result;
    }

    /**
     * @param array<int, string> $path
     * @return array{0: ?string, 1: ?int}
     */
    private function resolveCategoryAndIndex(array $path): array
    {
        $category = null;
        $index = null;

        foreach ($path as $segment) {
            if ($category === null && in_array($segment, self::RADAR_CATEGORIES, true)) {
                $category = $segment;
                continue;
            }

            if ($category !== null && $index === null && ctype_digit($segment)) {
                $index = (int) $segment;
                break;
            }
        }

        if ($category !== null && $index === null) {
            $index = 0;
        }

        return [$category, $index];
    }

    private function getRadarStorageDirectory(): string
    {
        return (string) $this->parameterBag->get('student_radar_directory');
    }
}
