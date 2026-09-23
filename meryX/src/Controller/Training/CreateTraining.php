<?php
namespace App\Controller\Training;

use App\Entity\Training;
use App\Entity\User;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateTraining extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, CompanyRepository $companyRepo)
    {
        $training = new Training();
        $actor = $this->getUser();

        if (!$actor instanceof User) {
            return $this->json(['message' => 'Login required'], 401);
        }

        if (!$this->isGranted('ROLE_COMPANY')) {
            return $this->json(['message' => 'Only company users can create trainings'], 403);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data && $request->request->all()) {
            $data = $request->request->all();
        }

        if (!is_array($data)) {
            return $this->json(['message' => 'Invalid training payload'], 400);
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

                $idValue = (int) $raw;
                return $idValue > 0 ? $idValue : null;
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

            $training->setCompanyId($actorCompany);

            $training->setTitle($data['title'] ?? null);
            $training->setType($data['type'] ?? null);
            $training->setMode($data['mode'] ?? null);
            $training->setLocation($data['location'] ?? null);
            $training->setDurationLabel($data['durationLabel'] ?? null);
            $training->setSeatCount($data['seatCount'] ?? null);
            $training->setDescription($data['description'] ?? null);
            $training->setStatus($data['status'] ?? 'draft');
            $training->setStartAt(new \DateTimeImmutable($data['startAt'] ?? 'now'));
            $training->setEndAt(new \DateTimeImmutable($data['endAt'] ?? 'now'));
            $training->setPublishedAt(new \DateTimeImmutable($data['publishedAt'] ?? 'now'));
            $training->setCreatedAt(new \DateTimeImmutable());
            $training->setUpdatedAt(new \DateTimeImmutable());

            $em->persist($training);
            $em->flush();

            return $this->json($training, 201, [], ['groups' => ['training:read']]);
        }

        return $this->json(['message' => 'Invalid request method'], 400);
    }
}
