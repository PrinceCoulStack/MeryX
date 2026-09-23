<?php

namespace App\Controller\CandidateShortList;

use App\Entity\CandidateShortList;
use App\Repository\CompanyRepository;
use App\Repository\OpportunitiesRepository;
use App\Repository\StudentProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateCandidateShortList extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, CompanyRepository $companyRepo, StudentProfileRepository $studentProfileRepo, OpportunitiesRepository $opportunitiesRepo)
    {
        $candidateShortList = new CandidateShortList();
        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('POST')) {
            if (!empty($data['companyId'])) {
                $company = $companyRepo->find($data['companyId']);
                if ($company) {
                    $candidateShortList->setCompanyId($company);
                } else {
                    return $this->json(['message' => 'Invalid company ID'], 400);
                }
            } else {
                return $this->json(['message' => 'Company ID is required'], 400);
            }

            if (!empty($data['studentProfileId'])) {
                $studentProfile = $studentProfileRepo->find($data['studentProfileId']);
                if ($studentProfile) {
                    $candidateShortList->setStudentProfileId($studentProfile);
                } else {
                    return $this->json(['message' => 'Invalid student profile ID'], 400);
                }
            } else {
                return $this->json(['message' => 'Student profile ID is required'], 400);
            }

            if (!empty($data['opportunityId'])) {
                $opportunity = $opportunitiesRepo->find($data['opportunityId']);
                if ($opportunity) {
                    $candidateShortList->setOpportunityId($opportunity);
                } else {
                    return $this->json(['message' => 'Invalid opportunity ID'], 400);
                }
            } else {
                return $this->json(['message' => 'Opportunity ID is required'], 400);
            }

            $candidateShortList->setNote($data['note'] ?? null);
            $candidateShortList->setCreatedAt(new \DateTimeImmutable());

            $em->persist($candidateShortList);
            $em->flush();

            return $this->json([
                'message' => 'Candidate short list created successfully',
                'id' => $candidateShortList->getId(),
            ], 201);
        }
    }
}
