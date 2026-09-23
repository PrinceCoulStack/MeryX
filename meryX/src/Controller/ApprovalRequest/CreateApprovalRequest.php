<?php

namespace App\Controller\ApprovalRequest;

use App\Entity\ApprovalRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateApprovalRequest extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request)
    {
        $approvalRequest = new ApprovalRequest();
        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('POST')) {
            $approvalRequest->setType($data['type'] ?? '');
            $approvalRequest->setStatus($data['status'] ?? []);
            $approvalRequest->setReviewedBy($data['reviewedBy'] ?? '');
            $approvalRequest->setReviewedAt(new \DateTimeImmutable());

            $em->persist($approvalRequest);
            $em->flush();

            return $this->json([
                'message' => 'Approval request created successfully',
                'id' => $approvalRequest->getId(),
            ], 201);
        }
    }
}
