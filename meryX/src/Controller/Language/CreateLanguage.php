<?php

namespace App\Controller\Language;

use App\Entity\Language;
use App\Repository\StudentProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateLanguage extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, StudentProfileRepository $studentProfileRepo)
    {
        $language = new Language();
        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('POST')) {
            if (!empty($data['studentProfileId'])) {
                $studentProfile = $studentProfileRepo->find($data['studentProfileId']);
                if ($studentProfile) {
                    $language->setStudentProfileId($studentProfile);
                } else {
                    return $this->json(['message' => 'Invalid student profile ID'], 400);
                }
            } else {
                return $this->json(['message' => 'Student profile ID is required'], 400);
            }

            $language->setName($data['name'] ?? null);
            $language->setLevel($data['level'] ?? null);
            $language->setCreatedAt(new \DateTimeImmutable());
            $language->setUpdatedAt(new \DateTimeImmutable());

            $em->persist($language);
            $em->flush();

            return $this->json([
                'message' => 'Language created successfully',
                'id' => $language->getId(),
            ], 201);
        }
    }
}
