<?php

namespace App\Controller\Application;

use App\Entity\Application;
use App\Repository\OpportunitiesRepository;
use App\Repository\StudentProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateApplication extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, StudentProfileRepository $studentProfileRepo, OpportunitiesRepository $opportunitiesRepo)
    {
        $application = new Application();
        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('POST')) {
            if (!empty($data['studentProfile'])) {
                $studentProfile = $studentProfileRepo->find($data['studentProfile']);
                if ($studentProfile) {
                    $application->setStudentProfile($studentProfile);
                } else {
                    return $this->json(['message' => 'Invalid student profile ID'], 400);
                }
            }

            if (!empty($data['opportunityId'])) {
                $opportunity = $opportunitiesRepo->find($data['opportunityId']);
                if ($opportunity) {
                    $application->setOpportunityId($opportunity);
                } else {
                    return $this->json(['message' => 'Invalid opportunity ID'], 400);
                }
            }

            $application->setStatus($data['status'] ?? 'pending');
            $application->setCoverLetter($data['coverLetter'] ?? '');
            $application->setReviewNote($data['reviewNote'] ?? '');
            $application->setAppliedAt(new \DateTimeImmutable());
            $application->setReviewedAt(new \DateTimeImmutable());
            $application->setUpdatedAt(new \DateTimeImmutable());
            $application->setWithdrawnAt(new \DateTimeImmutable());

            $em->persist($application);
            $em->flush();

            return $this->json([
                'message' => 'Application created successfully',
                'id' => $application->getId(),
            ], 201);
        }
    }
}
