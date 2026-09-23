<?php

namespace App\Controller\Department;

use App\Repository\DepartmentRepository;
use App\Repository\UniversityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateDepartment extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, DepartmentRepository $repository, UniversityRepository $universityRepo, int $id)
    {
        $department = $repository->find($id);
        if (!$department) {
            return $this->json(['message' => 'Department not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            if (!empty($data['universityId'])) {
                $university = $universityRepo->find($data['universityId']);
                if ($university) {
                    $department->setUniversityId($university);
                } else {
                    return $this->json(['message' => 'Invalid university ID'], 400);
                }
            }

            foreach (['name', 'code', 'description'] as $field) {
                if (isset($data[$field])) {
                    $setter = 'set' . ucfirst($field);
                    $department->$setter($data[$field]);
                }
            }

            if (isset($data['isEnabled'])) {
                $department->setIsEnabled((bool) $data['isEnabled']);
            }

            $department->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->json(['message' => 'Department updated successfully'], 200);
        }
    }
}
