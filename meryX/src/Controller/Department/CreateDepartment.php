<?php

namespace App\Controller\Department;

use App\Entity\Department;
use App\Repository\UniversityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateDepartment extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, UniversityRepository $universityRepo)
    {
        $department = new Department();
        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('POST')) {
            if (!empty($data['universityId'])) {
                $university = $universityRepo->find($data['universityId']);
                if ($university) {
                    $department->setUniversityId($university);
                } else {
                    return $this->json(['message' => 'Invalid university ID'], 400);
                }
            } else {
                return $this->json(['message' => 'University ID is required'], 400);
            }

            $department->setName($data['name'] ?? null);
            $department->setIsEnabled((bool) ($data['isEnabled'] ?? true));
            $department->setCode($data['code'] ?? null);
            $department->setDescription($data['description'] ?? null);
            $department->setCreatedAt(new \DateTimeImmutable());
            $department->setUpdatedAt(new \DateTimeImmutable());

            $em->persist($department);
            $em->flush();

            return $this->json([
                'message' => 'Department created successfully',
                'id' => $department->getId(),
            ], 201);
        }
    }
}
