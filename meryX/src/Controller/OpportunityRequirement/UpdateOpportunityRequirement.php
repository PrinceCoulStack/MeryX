<?php

namespace App\Controller\OpportunityRequirement;

use App\Repository\OpportunityRequirementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateOpportunityRequirement extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, OpportunityRequirementRepository $repository, int $id)
    {
        $opportunityRequirement = $repository->find($id);
        if (!$opportunityRequirement) {
            return $this->json(['message' => 'Opportunity requirement not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            if (isset($data['label'])) {
                $opportunityRequirement->setLabel($data['label']);
            }

            if (isset($data['type'])) {
                $opportunityRequirement->setType($data['type']);
            }

            if (isset($data['isRequired'])) {
                $opportunityRequirement->setIsRequired((bool) $data['isRequired']);
            }

            $em->flush();

            return $this->json(['message' => 'Opportunity requirement updated successfully'], 200);
        }
    }
}
