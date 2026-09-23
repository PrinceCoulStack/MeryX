<?php

namespace App\Controller\AcademicProgram;

use App\Repository\AcademicProgramRepository;
use App\Repository\DepartmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateAcademicProgram extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, AcademicProgramRepository $repository, DepartmentRepository $departmentRepo, int $id)
    {
        $program = $repository->find($id);
        if (!$program) {
            return $this->json(['message' => 'Academic program not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            if (isset($data['departmentId'])) {
                $department = $departmentRepo->find($data['departmentId']);
                if ($department) {
                    $program->setDepartmentId($department);
                } else {
                    return $this->json(['message' => 'Invalid department ID'], 400);
                }
            }

            foreach (['name', 'code', 'description', 'duration', 'degree'] as $field) {
                if (array_key_exists($field, $data)) {
                    $setter = 'set' . ucfirst($field);
                    $program->$setter($data[$field]);
                }
            }

            if (array_key_exists('isActive', $data)) {
                $program->setIsActive((bool) $data['isActive']);
            }

            $program->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->json(['message' => 'Academic program updated successfully'], 200);
        }
    }
}
