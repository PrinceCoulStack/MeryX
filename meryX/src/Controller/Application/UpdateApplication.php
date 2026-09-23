<?php

namespace App\Controller\Application;

use App\Repository\ApplicationRepository;
use App\Repository\OpportunitiesRepository;
use App\Repository\StudentProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateApplication extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, ApplicationRepository $repository, StudentProfileRepository $studentProfileRepo, OpportunitiesRepository $opportunitiesRepo, int $id)
    {
        $application = $repository->find($id);
        if (!$application) {
            return $this->json(['message' => 'Application not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            if (isset($data['studentProfile'])) {
                $studentProfile = $studentProfileRepo->find($data['studentProfile']);
                if ($studentProfile) {
                    $application->setStudentProfile($studentProfile);
                } else {
                    return $this->json(['message' => 'Invalid student profile ID'], 400);
                }
            }

            if (isset($data['opportunityId'])) {
                $opportunity = $opportunitiesRepo->find($data['opportunityId']);
                if ($opportunity) {
                    $application->setOpportunityId($opportunity);
                } else {
                    return $this->json(['message' => 'Invalid opportunity ID'], 400);
                }
            }

            foreach (['status', 'coverLetter', 'reviewNote'] as $field) {
                if (array_key_exists($field, $data)) {
                    $setter = 'set' . ucfirst($field);
                    $application->$setter($data[$field]);
                }
            }

            $application->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->json(['message' => 'Application updated successfully'], 200);
        }
    }
}
