<?php

namespace App\Controller\Marks;

use App\Repository\MarksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateMarks extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, MarksRepository $repository, int $id)
    {
        $marks = $repository->find($id);
        if (!$marks) {
            return $this->json(['message' => 'Marks not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            foreach (['value', 'semester', 'year'] as $field) {
                if (isset($data[$field])) {
                    $setter = 'set' . ucfirst($field);
                    $marks->$setter($data[$field]);
                }
            }

            if (isset($data['markGiveAt'])) {
                $marks->setMarkGiveAt(new \DateTimeImmutable($data['markGiveAt']));
            }

            $marks->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->json(['message' => 'Marks updated successfully'], 200);
        }
    }
}
