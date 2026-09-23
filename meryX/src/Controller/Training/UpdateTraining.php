<?php
namespace App\Controller\Training;

use App\Repository\CompanyRepository;
use App\Repository\TrainingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateTraining extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, TrainingRepository $trainingRepository, CompanyRepository $companyRepo, int $id)
    {
        $training = $trainingRepository->find($id);
        if (!$training) {
            return $this->json(['message' => 'Training not found'], 404);
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

        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            if (isset($data['title'])) {
                $training->setTitle($data['title']);
            }
            if (isset($data['type'])) {
                $training->setType($data['type']);
            }
            if (isset($data['mode'])) {
                $training->setMode($data['mode']);
            }
            if (isset($data['location'])) {
                $training->setLocation($data['location']);
            }
            if (isset($data['durationLabel'])) {
                $training->setDurationLabel($data['durationLabel']);
            }
            if (isset($data['seatCount'])) {
                $training->setSeatCount($data['seatCount']);
            }
            if (isset($data['description'])) {
                $training->setDescription($data['description']);
            }
            if (isset($data['status'])) {
                $training->setStatus($data['status']);
            }
            if (isset($data['startAt'])) {
                $training->setStartAt(new \DateTimeImmutable($data['startAt']));
            }
            if (isset($data['endAt'])) {
                $training->setEndAt(new \DateTimeImmutable($data['endAt']));
            }
            if (isset($data['publishedAt'])) {
                $training->setPublishedAt(new \DateTimeImmutable($data['publishedAt']));
            }
            $companyId = $resolveEntityId($data['companyId'] ?? null);
            if ($companyId !== null) {
                $company = $companyRepo->find($companyId);
                if ($company) {
                    $training->setCompanyId($company);
                } else {
                    return $this->json(['message' => 'Invalid company ID'], 400);
                }
            }

            $training->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->json($training, 200, [], ['groups' => ['training:read']]);
        }

        return $this->json(['message' => 'Invalid request method'], 400);
    }
}
