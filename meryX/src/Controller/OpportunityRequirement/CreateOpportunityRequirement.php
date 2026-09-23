<?php

namespace App\Controller\OpportunityRequirement;

use App\Entity\OpportunityRequirement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateOpportunityRequirement extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request)
    {
        $opportunityRequirement = new OpportunityRequirement();
        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('POST')) {
            $opportunityRequirement->setLabel($data['label'] ?? null);
            $opportunityRequirement->setType($data['type'] ?? null);
            $opportunityRequirement->setIsRequired((bool) ($data['isRequired'] ?? false));
            $opportunityRequirement->setCreatedAt(new \DateTimeImmutable());

            $em->persist($opportunityRequirement);
            $em->flush();

            return $this->json([
                'message' => 'Opportunity requirement created successfully',
                'id' => $opportunityRequirement->getId(),
            ], 201);
        }
    }
}
