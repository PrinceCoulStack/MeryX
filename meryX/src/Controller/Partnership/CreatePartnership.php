<?php
namespace App\Controller\Partnership;

use App\Entity\Partnership;
use App\Repository\CompanyRepository;
use App\Repository\PartnershipRepository;
use App\Repository\UniversityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreatePartnership extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, CompanyRepository $companyRepo, UniversityRepository $universityRepo)
    {
        $partnership = new Partnership();
        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('POST')) {
            if (!empty($data['companyId'])) {
                $company = $companyRepo->find($data['companyId']);
                if ($company) {
                    $partnership->setCompanyId($company);
                } else {
                    return $this->json(['message' => 'Invalid company ID'], 400);
                }
            } else {
                return $this->json(['message' => 'Company ID is required'], 400);
            }

            if (!empty($data['universityId'])) {
                $university = $universityRepo->find($data['universityId']);
                if ($university) {
                    $partnership->setUniversityId($university);
                } else {
                    return $this->json(['message' => 'Invalid university ID'], 400);
                }
            } else {
                return $this->json(['message' => 'University ID is required'], 400);
            }

            $partnership->setStatus($data['status'] ?? 'pending');
            $partnership->setNotes($data['notes'] ?? null);
            $partnership->setStartAt(new \DateTimeImmutable($data['startAt'] ?? 'now'));
            $partnership->setEndedAt(new \DateTimeImmutable($data['endedAt'] ?? 'now'));
            $partnership->setCreatedAt(new \DateTimeImmutable());
            $partnership->setUpdatedAt(new \DateTimeImmutable());

            $em->persist($partnership);
            $em->flush();

            return $this->json(['message' => 'Partnership created successfully', 'id' => $partnership->getId()], 201);
        }
    }
}
