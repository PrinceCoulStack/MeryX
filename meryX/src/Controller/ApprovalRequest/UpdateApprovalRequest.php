<?php

namespace App\Controller\ApprovalRequest;

use App\Repository\ApprovalRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateApprovalRequest extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, ApprovalRequestRepository $repository, int $id)
    {
        $approvalRequest = $repository->find($id);
        if (!$approvalRequest) {
            return $this->json(['message' => 'Approval request not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            foreach (['type', 'reviewedBy'] as $field) {
                if (array_key_exists($field, $data)) {
                    $setter = 'set' . ucfirst($field);
                    $approvalRequest->$setter($data[$field]);
                }
            }

            if (array_key_exists('status', $data)) {
                $approvalRequest->setStatus($data['status']);
            }

            $approvalRequest->setReviewedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->json(['message' => 'Approval request updated successfully'], 200);
        }
    }
}
