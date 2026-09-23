<?php

namespace App\Controller\Marks;

use App\Entity\Marks;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateMarks extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request)
    {
        $marks = new Marks();
        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('POST')) {
            $marks->setValue($data['value'] ?? null);
            $marks->setSemester($data['semester'] ?? null);
            $marks->setYear($data['year'] ?? null);
            $marks->setMarkGiveAt(new \DateTimeImmutable($data['markGiveAt'] ?? 'now'));
            $marks->setUpdatedAt(new \DateTimeImmutable());

            $em->persist($marks);
            $em->flush();

            return $this->json([
                'message' => 'Marks created successfully',
                'id' => $marks->getId(),
            ], 201);
        }
    }
}
