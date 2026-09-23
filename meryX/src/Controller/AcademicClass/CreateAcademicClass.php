<?php

namespace App\Controller\AcademicClass;

use App\Entity\AcademicClass;
use App\Repository\DepartmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateAcademicClass extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, DepartmentRepository $departmentRepo)
    {
        $academicClass = new AcademicClass();
        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('POST')) {
            if (!empty($data['departmentId'])) {
                $department = $departmentRepo->find($data['departmentId']);
                if ($department) {
                    $academicClass->setDepartmentId($department);
                } else {
                    return $this->json(['message' => 'Invalid department ID'], 400);
                }
            }

            $academicClass->setName($data['name'] ?? '');
            $academicClass->setCode($data['code'] ?? '');
            $academicClass->setLevel($data['level'] ?? '');
            $academicClass->setAcademicYear($data['academicYear'] ?? '');
            $academicClass->setSemester($data['semester'] ?? '');
            $academicClass->setCapacity($data['capacity'] ?? '');
            $academicClass->setIsActive((bool) ($data['isActive'] ?? true));
            $academicClass->setCreatedAt(new \DateTimeImmutable());
            $academicClass->setUpdatedAt(new \DateTimeImmutable());

            $em->persist($academicClass);
            $em->flush();

            return $this->json([
                'message' => 'Academic class created successfully',
                'id' => $academicClass->getId(),
            ], 201);
        }
    }
}
