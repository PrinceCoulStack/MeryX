<?php
namespace App\Controller\TrainingEnrollment;

use App\Repository\StudentProfileRepository;
use App\Repository\TrainingEnrollmentRepository;
use App\Repository\TrainingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateTrainingEnrollment extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, TrainingEnrollmentRepository $trainingEnrollmentRepository, TrainingRepository $trainingRepo, StudentProfileRepository $studentProfileRepo, int $id)
    {
        $trainingEnrollment = $trainingEnrollmentRepository->find($id);
        if (!$trainingEnrollment) {
            return $this->json(['message' => 'Training enrollment not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            if (isset($data['status'])) {
                $trainingEnrollment->setStatus($data['status']);
            }
            if (isset($data['enrolledAt'])) {
                $trainingEnrollment->setEnrolledAt(new \DateTimeImmutable($data['enrolledAt']));
            }
            if (isset($data['completedAt'])) {
                $trainingEnrollment->setCompletedAt(new \DateTimeImmutable($data['completedAt']));
            }

            if (!empty($data['trainingId'])) {
                $training = $trainingRepo->find($data['trainingId']);
                if ($training) {
                    $trainingEnrollment->setTrainingId($training);
                } else {
                    return $this->json(['message' => 'Invalid training ID'], 400);
                }
            }

            if (!empty($data['studentProfileId'])) {
                $studentProfile = $studentProfileRepo->find($data['studentProfileId']);
                if ($studentProfile) {
                    $trainingEnrollment->setStudentProfileId($studentProfile);
                } else {
                    return $this->json(['message' => 'Invalid student profile ID'], 400);
                }
            }

            $em->flush();

            return $this->json(['message' => 'Training enrollment updated successfully'], 200);
        }
    }
}
