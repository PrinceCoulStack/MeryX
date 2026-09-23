<?php
namespace App\Controller\TrainingEnrollment;

use App\Entity\TrainingEnrollment;
use App\Repository\StudentProfileRepository;
use App\Repository\TrainingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateTrainingEnrollment extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, TrainingRepository $trainingRepo, StudentProfileRepository $studentProfileRepo)
    {
        $trainingEnrollment = new TrainingEnrollment();
        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('POST')) {
            $training = null;
            if (!empty($data['trainingId'])) {
                $training = $trainingRepo->find($data['trainingId']);
                if ($training) {
                    $trainingEnrollment->setTrainingId($training);
                } else {
                    return $this->json(['message' => 'Invalid training ID'], 400);
                }
            } else {
                return $this->json(['message' => 'Training ID is required'], 400);
            }

            $studentProfile = null;
            if (!empty($data['studentProfileId'])) {
                $studentProfile = $studentProfileRepo->find($data['studentProfileId']);
                if ($studentProfile) {
                    $trainingEnrollment->setStudentProfileId($studentProfile);
                } else {
                    return $this->json(['message' => 'Invalid student profile ID'], 400);
                }
            } else {
                return $this->json(['message' => 'Student profile ID is required'], 400);
            }

            $trainingEnrollment->setStatus($data['status'] ?? 'pending');
            $trainingEnrollment->setEnrolledAt(new \DateTimeImmutable($data['enrolledAt'] ?? 'now'));
            $trainingEnrollment->setCompletedAt(new \DateTimeImmutable($data['completedAt'] ?? 'now'));

            $em->persist($trainingEnrollment);
            $em->flush();

            return $this->json(['message' => 'Training enrollment created successfully', 'id' => $trainingEnrollment->getId()], 201);
        }
    }
}
