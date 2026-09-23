<?php
namespace App\Controller\Partnership;

use App\Repository\CompanyRepository;
use App\Repository\PartnershipRepository;
use App\Repository\UniversityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdatePartnership extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, PartnershipRepository $partnershipRepository, CompanyRepository $companyRepo, UniversityRepository $universityRepo, int $id)
    {
        $partnership = $partnershipRepository->find($id);
        if (!$partnership) {
            return $this->json(['message' => 'Partnership not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            if (isset($data['status'])) {
                $partnership->setStatus($data['status']);
            }
            if (isset($data['notes'])) {
                $partnership->setNotes($data['notes']);
            }
            if (isset($data['startAt'])) {
                $partnership->setStartAt(new \DateTimeImmutable($data['startAt']));
            }
            if (isset($data['endedAt'])) {
                $partnership->setEndedAt(new \DateTimeImmutable($data['endedAt']));
            }

            if (!empty($data['companyId'])) {
                $company = $companyRepo->find($data['companyId']);
                if ($company) {
                    $partnership->setCompanyId($company);
                } else {
                    return $this->json(['message' => 'Invalid company ID'], 400);
                }
            }

            if (!empty($data['universityId'])) {
                $university = $universityRepo->find($data['universityId']);
                if ($university) {
                    $partnership->setUniversityId($university);
                } else {
                    return $this->json(['message' => 'Invalid university ID'], 400);
                }
            }

            $partnership->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->json(['message' => 'Partnership updated successfully'], 200);
        }
    }
}
