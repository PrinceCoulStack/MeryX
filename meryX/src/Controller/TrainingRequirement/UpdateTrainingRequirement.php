<?php
namespace App\Controller\TrainingRequirement;

use App\Repository\TrainingRepository;
use App\Repository\TrainingRequirementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateTrainingRequirement extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, TrainingRequirementRepository $trainingRequirementRepository, TrainingRepository $trainingRepo, int $id)
    {
        // logic to update training requirements goes here
        $trainingRequirement = $trainingRequirementRepository->find($id);
        if (!$trainingRequirement) {
            return $this->json(['message' => 'Training requirement not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            if (isset($data['label'])) {
                $trainingRequirement->setLabel($data['label']);
            }
            if (isset($data['isRequired'])) {
                $trainingRequirement->setIsRequired($data['isRequired']);
            }
            if (isset($data['isEnabled'])) {
                $trainingRequirement->setIsEnabled($data['isEnabled']);
            }
            if (isset($data['isDeleted'])) {
                $trainingRequirement->setIsDeleted($data['isDeleted']);
            }

            $training = null;
            if(!empty($data['trainingId'])) {
                $training = $trainingRepo->find($data['trainingId']);
                if(isset($training)) {
                    $trainingRequirement->setTrainingId($training);
                } else {
                    return $this->json(['message' => 'Invalid training ID'], 400);
                }
            }

            if (isset($data['updatedAt'])) {
                $trainingRequirement->setUpdatedAt(new \DateTimeImmutable());
            }

            // Persist the changes to the database
            $em->flush();

            return $this->json(['message' => 'Training requirement updated successfully'], 200);
        }

    }
}
