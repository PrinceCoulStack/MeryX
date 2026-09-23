<?php

namespace App\Controller\Language;

use App\Repository\LanguageRepository;
use App\Repository\StudentProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateLanguage extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, LanguageRepository $repository, StudentProfileRepository $studentProfileRepo, int $id)
    {
        $language = $repository->find($id);
        if (!$language) {
            return $this->json(['message' => 'Language not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            if (!empty($data['studentProfileId'])) {
                $studentProfile = $studentProfileRepo->find($data['studentProfileId']);
                if ($studentProfile) {
                    $language->setStudentProfileId($studentProfile);
                } else {
                    return $this->json(['message' => 'Invalid student profile ID'], 400);
                }
            }

            if (isset($data['name'])) {
                $language->setName($data['name']);
            }

            if (isset($data['level'])) {
                $language->setLevel($data['level']);
            }

            $language->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->json(['message' => 'Language updated successfully'], 200);
        }
    }
}
