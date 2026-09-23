<?php

namespace App\Controller\AcademicProgram;

use App\Entity\AcademicProgram;
use App\Repository\DepartmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateAcademicProgram extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, DepartmentRepository $departmentRepo)
    {
        $program = new AcademicProgram();
        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('POST')) {
            if (!empty($data['departmentId'])) {
                $department = $departmentRepo->find($data['departmentId']);
                if ($department) {
                    $program->setDepartmentId($department);
                } else {
                    return $this->json(['message' => 'Invalid department ID'], 400);
                }
            }

            $program->setName($data['name'] ?? '');
            $program->setCode($data['code'] ?? '');
            $program->setDescription($data['description'] ?? '');
            $program->setDuration($data['duration'] ?? '');
            $program->setDegree($data['degree'] ?? '');
            $program->setIsActive((bool) ($data['isActive'] ?? true));
            $program->setCreatedAt(new \DateTimeImmutable());
            $program->setUpdatedAt(new \DateTimeImmutable());

            $em->persist($program);
            $em->flush();

            return $this->json([
                'message' => 'Academic program created successfully',
                'id' => $program->getId(),
            ], 201);
        }
    }
}
