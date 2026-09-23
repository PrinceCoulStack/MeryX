<?php

namespace App\Controller\Opportunities;

use App\Entity\User;
use App\Entity\Opportunities;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateOpportunities extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, CompanyRepository $companyRepo)
    {
        $opportunity = new Opportunities();
        $actor = $this->getUser();

        if (!$actor instanceof User) {
            return $this->json(['message' => 'Login required'], 401);
        }

        if (!$this->isGranted('ROLE_COMPANY')) {
            return $this->json(['message' => 'Only company users can create opportunities'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data && $request->request->all()) {
            $data = $request->request->all();
        }

        if (!is_array($data)) {
            return $this->json(['message' => 'Invalid opportunities payload'], 400);
        }

        $resolveEntityId = static function ($value): ?int {
            if ($value === null || $value === '' || $value === []) {
                return null;
            }

            if (is_array($value) && isset($value['id'])) {
                $value = $value['id'];
            }

            if (is_string($value) && preg_match('/\/(\d+)$/', $value, $matches)) {
                return (int) $matches[1];
            }

            if (is_scalar($value)) {
                $raw = trim((string) $value);
                if ($raw === '' || $raw === 'null' || $raw === 'undefined') {
                    return null;
                }

                $intValue = (int) $raw;
                return $intValue > 0 ? $intValue : null;
            }

            return null;
        };

        if ($request->isMethod('POST')) {
            $actorCompany = $companyRepo->findOneByUser($actor);
            if ($actorCompany === null) {
                return $this->json(['message' => 'Authenticated company user is not linked to a company profile'], 422);
            }

            $payloadCompanyId = $resolveEntityId($data['companyId'] ?? $data['company'] ?? $data['company_profile'] ?? null);
            if ($payloadCompanyId !== null && $payloadCompanyId !== $actorCompany->getId()) {
                return $this->json(['message' => 'Company relation must match the authenticated company profile'], 403);
            }

            $opportunity->setCompanyId($actorCompany);

            $opportunity->setTitle($data['title'] ?? null);
            $opportunity->setType($data['type'] ?? null);
            $opportunity->setDepartment($data['department'] ?? null);
            $opportunity->setLocation($data['location'] ?? null);
            $opportunity->setRemoteType($data['remoteType'] ?? null);
            $opportunity->setSalaryLabel($data['salaryLabel'] ?? null);
            $opportunity->setDescription($data['description'] ?? null);
            $opportunity->setStatus($data['status'] ?? 'open');
            $opportunity->setIsEnabled((bool) ($data['isEnabled'] ?? true));
            $opportunity->setPublishedAt(new \DateTimeImmutable($data['publishedAt'] ?? 'now'));
            $opportunity->setApplicationDeadLine(new \DateTimeImmutable($data['applicationDeadLine'] ?? 'now'));
            $opportunity->setIsDeleted((bool) ($data['isDeleted'] ?? false));
            $opportunity->setCategory($data['category'] ?? null);
            $opportunity->setExperienceLevel($data['experienceLevel'] ?? null);
            $opportunity->setNumberOfPositions($data['numberOfPositions'] ?? null);
            if (array_key_exists('requirements', $data)) {
                $requirements = is_array($data['requirements']) ? $data['requirements'] : [];
                $opportunity->setRequirements(array_values($requirements));
            } else {
                $opportunity->setRequirements([]);
            }
            $opportunity->setCreatedAt(new \DateTimeImmutable());
            $opportunity->setUpdatedAt(new \DateTimeImmutable());

            $em->persist($opportunity);
            $em->flush();

            return $this->json($opportunity, 201, [], ['groups' => ['opportunities:read']]);
        }

        return $this->json(['message' => 'Invalid request method'], 400);
    }
}
