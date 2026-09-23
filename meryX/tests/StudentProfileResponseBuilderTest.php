<?php

namespace App\Tests;

use App\Entity\Address;
use App\Entity\StudentProfile;
use App\Entity\University;
use App\Entity\User;
use App\Service\StudentProfile\StudentProfileResponseBuilder;
use PHPUnit\Framework\TestCase;

final class StudentProfileResponseBuilderTest extends TestCase
{
    public function testHydratesBioFromStoredProfileFields(): void
    {
        $profile = new StudentProfile();
        $profile->setFullName('Hydrated Name');
        $profile->setGpa('3.10');
        $profile->setGender('female');
        $profile->setProfileUrl('avatar.png');
        $profile->setProfileCompletion(68);
        $profile->setProgram('Computer Science');
        $profile->setAcademicYear('M2');
        $profile->setFaculty('Engineering');
        $profile->setInternshipCycle('advanced');
        $profile->setVerificationData([
            'studentId' => 'ST-42',
            'nationality' => 'Moroccan',
        ]);
        $profile->setBio(json_encode([
            'personal' => ['city' => 'Lyon', 'summary' => 'Backend-focused profile'],
            'academic' => ['department' => 'Software'],
            'radar' => [
                'academic' => [],
                'certificate' => [],
                'numerique' => [],
                'langue' => [],
                'stage' => [],
            ],
        ], JSON_THROW_ON_ERROR));

        $address = new Address();
        $address->setCity('Lyon');
        $address->setState('ARA');
        $address->setCountry('France');
        $address->setLatitude('45.76');
        $address->setLongitude('4.84');

        $user = new User();
        $user->setEmail('student@example.test');
        $user->setPhone('+3300000000');
        $user->setAddressId($address);
        $profile->setUserId($user);

        $university = new University();
        $university->setName('Uni Name');
        $university->setType('Public');
        $university->setAccreditationNumber('ACC');
        $university->setRankingScore('1');
        $university->setIsApproved(true);
        $university->setDescription('desc');
        $university->setStatus('approved');
        $university->setRegistrationNumber('REG');
        $university->setUpdatedAt(new \DateTimeImmutable());
        $university->setLogoUrl('logo.png');
        $profile->setUniversityId($university);

        $builder = new StudentProfileResponseBuilder();
        $payload = $builder->buildItem($profile);

        $this->assertSame('Hydrated Name', $payload['bio']['personal']['fullName']);
        $this->assertSame('Lyon', $payload['bio']['personal']['city']);
        $this->assertSame('3.10', $payload['bio']['academic']['gpa']);
        $this->assertSame(68, $payload['bio']['academic']['profileCompletion']);
        $this->assertSame('/uploads/student_profiles/avatar.png', $payload['bio']['personal']['profileUrl']);
        $this->assertSame('student@example.test', $payload['email']);
        $this->assertSame('+3300000000', $payload['phone']);
        $this->assertSame('Computer Science', $payload['program']);
        $this->assertSame('advanced', $payload['level']);
        $this->assertSame('M2', $payload['academicYear']);
        $this->assertSame('Engineering', $payload['faculty']);
        $this->assertSame('Software', $payload['department']);
        $this->assertSame('ST-42', $payload['studentId']);
        $this->assertSame('Moroccan', $payload['nationality']);
        $this->assertSame('Backend-focused profile', $payload['summary']);
        $this->assertSame('Lyon', $payload['address']['city']);
        $this->assertSame('student@example.test', $payload['user']['email']);
        $this->assertSame('Uni Name', $payload['university']['name']);
        $this->assertSame('approved', $payload['university']['status']);
    }
}
