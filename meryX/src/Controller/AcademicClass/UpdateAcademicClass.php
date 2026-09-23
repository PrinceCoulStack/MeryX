<?php

namespace App\Controller\AcademicClass;

use App\Repository\AcademicClassRepository;
use App\Repository\DepartmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateAcademicClass extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, AcademicClassRepository $repository, DepartmentRepository $departmentRepo, int $id)
    {
        $academicClass = $repository->find($id);
        if (!$academicClass) {
            return $this->json(['message' => 'Academic class not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            if (isset($data['departmentId'])) {
                $department = $departmentRepo->find($data['departmentId']);
                if ($department) {
                    $academicClass->setDepartmentId($department);
                } else {
                    return $this->json(['message' => 'Invalid department ID'], 400);
                }
            }

            foreach (['name', 'code', 'level', 'academicYear', 'semester', 'capacity'] as $field) {
                if (array_key_exists($field, $data)) {
                    $setter = 'set' . ucfirst($field);
                    $academicClass->$setter($data[$field]);
                }
            }

            if (array_key_exists('isActive', $data)) {
                $academicClass->setIsActive((bool) $data['isActive']);
            }

            $academicClass->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->json(['message' => 'Academic class updated successfully'], 200);
        }
    }
}
