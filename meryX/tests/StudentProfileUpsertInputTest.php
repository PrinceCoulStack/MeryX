<?php

namespace App\Tests;

use App\Dto\StudentProfileUpsertInput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class StudentProfileUpsertInputTest extends TestCase
{
    public function testSupportsStudentProfilesWrappedJsonPayload(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode([
            'studentProfiles' => [
                'fullName' => 'Jane Student',
                'gpa' => '3.90',
                'gender' => 'female',
                'skills' => ['PHP', 'Vue'],
                'bio' => [
                    'personal' => ['city' => 'Paris'],
                    'academic' => ['profileCompletion' => 80],
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        $input = StudentProfileUpsertInput::fromRequest($request);

        $this->assertSame('Jane Student', $input->fullName);
        $this->assertSame('3.90', $input->gpa);
        $this->assertSame('female', $input->gender);
        $this->assertSame(['PHP', 'Vue'], $input->skills);
        $this->assertIsArray($input->bio);
        $this->assertSame('Paris', $input->bio['personal']['city']);
    }

    public function testDecodesMultipartJsonStringFields(): void
    {
        $request = new Request([], [
            'studentProfiles' => json_encode([
                'fullName' => 'Wrapped Name',
                'skills' => json_encode([
                    ['name' => 'Symfony', 'level' => 'advanced'],
                ], JSON_THROW_ON_ERROR),
                'bio' => json_encode([
                    'radar' => [
                        'academic' => [
                            ['title' => 'Math'],
                        ],
                    ],
                ], JSON_THROW_ON_ERROR),
            ], JSON_THROW_ON_ERROR),
        ]);

        $input = StudentProfileUpsertInput::fromRequest($request);

        $this->assertSame('Wrapped Name', $input->fullName);
        $this->assertIsArray($input->skills);
        $this->assertSame('Symfony', $input->skills[0]['name']);
        $this->assertIsArray($input->bio);
        $this->assertSame('Math', $input->bio['radar']['academic'][0]['title']);
    }

    public function testParsesNormalizedSettingsFields(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode([
            'fullName' => 'Normalized Student',
            'email' => 'normalized@student.test',
            'phone' => '+212600000077',
            'program' => 'Computer Science',
            'level' => 'M2',
            'academicYear' => '2026/2027',
            'faculty' => 'Engineering',
            'department' => 'Software',
            'studentId' => 'ST-900',
            'nationality' => 'Moroccan',
            'address' => [
                'city' => 'Rabat',
                'state' => 'Rabat-Sale-Kenitra',
                'country' => 'Morocco',
            ],
            'summary' => 'Interested in backend and API design.',
        ], JSON_THROW_ON_ERROR));

        $input = StudentProfileUpsertInput::fromRequest($request);

        $this->assertSame('normalized@student.test', $input->email);
        $this->assertSame('+212600000077', $input->phone);
        $this->assertSame('Computer Science', $input->program);
        $this->assertSame('M2', $input->level);
        $this->assertSame('2026/2027', $input->academicYear);
        $this->assertSame('Engineering', $input->faculty);
        $this->assertSame('Software', $input->department);
        $this->assertSame('ST-900', $input->studentId);
        $this->assertSame('Moroccan', $input->nationality);
        $this->assertSame('Interested in backend and API design.', $input->summary);
        $this->assertSame('Rabat', $input->address['city']);
        $this->assertSame('Morocco', $input->address['country']);
    }
}
