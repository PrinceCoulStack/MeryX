<?php

namespace App\Controller\CandidateShortList;

use App\Repository\CandidateShortListRepository;
use App\Repository\CompanyRepository;
use App\Repository\OpportunitiesRepository;
use App\Repository\StudentProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateCandidateShortList extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, CandidateShortListRepository $repository, CompanyRepository $companyRepo, StudentProfileRepository $studentProfileRepo, OpportunitiesRepository $opportunitiesRepo, int $id)
    {
        $candidateShortList = $repository->find($id);
        if (!$candidateShortList) {
            return $this->json(['message' => 'Candidate short list not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            if (!empty($data['companyId'])) {
                $company = $companyRepo->find($data['companyId']);
                if ($company) {
                    $candidateShortList->setCompanyId($company);
                } else {
                    return $this->json(['message' => 'Invalid company ID'], 400);
                }
            }

            if (!empty($data['studentProfileId'])) {
                $studentProfile = $studentProfileRepo->find($data['studentProfileId']);
                if ($studentProfile) {
                    $candidateShortList->setStudentProfileId($studentProfile);
                } else {
                    return $this->json(['message' => 'Invalid student profile ID'], 400);
                }
            }

            if (!empty($data['opportunityId'])) {
                $opportunity = $opportunitiesRepo->find($data['opportunityId']);
                if ($opportunity) {
                    $candidateShortList->setOpportunityId($opportunity);
                } else {
                    return $this->json(['message' => 'Invalid opportunity ID'], 400);
                }
            }

            if (isset($data['note'])) {
                $candidateShortList->setNote($data['note']);
            }

            $em->flush();

            return $this->json(['message' => 'Candidate short list updated successfully'], 200);
        }
    }
}
