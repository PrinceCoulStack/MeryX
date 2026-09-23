<?php

namespace App\Controller\Opportunities;

use App\Repository\OpportunitiesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateOpportunities extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, OpportunitiesRepository $repository, int $id)
    {
        $opportunity = $repository->find($id);
        if (!$opportunity) {
            return $this->json(['message' => 'Opportunity not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            foreach ([
                'title', 'type', 'department', 'location', 'remoteType', 'salaryLabel',
                'description', 'status', 'category', 'experienceLevel', 'numberOfPositions'
            ] as $field) {
                if (isset($data[$field])) {
                    $setter = 'set' . ucfirst($field);
                    $opportunity->$setter($data[$field]);
                }
            }

            if (isset($data['isEnabled'])) {
                $opportunity->setIsEnabled((bool) $data['isEnabled']);
            }

            if (isset($data['isDeleted'])) {
                $opportunity->setIsDeleted((bool) $data['isDeleted']);
            }

            if (isset($data['publishedAt'])) {
                $opportunity->setPublishedAt(new \DateTimeImmutable($data['publishedAt']));
            }

            if (isset($data['applicationDeadLine'])) {
                $opportunity->setApplicationDeadLine(new \DateTimeImmutable($data['applicationDeadLine']));
            }

            if (array_key_exists('requirements', $data)) {
                $requirements = is_array($data['requirements']) ? $data['requirements'] : [];
                $opportunity->setRequirements(array_values($requirements));
            }

            $opportunity->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->json(['message' => 'Opportunity updated successfully'], 200);
        }
    }
}
