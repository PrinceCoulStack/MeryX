<?php
namespace App\Controller\TrainingRequirement;

use App\Entity\TrainingRequirement;
use App\Repository\TrainingRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TrainingRequirementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateTrainingRequirement extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, TrainingRepository $trainingRepo )
    {
        // logic to create a training requirement goes here
        $trainingRequirement = new TrainingRequirement();
        $data = json_decode($request->getContent(), true);

        if($request->isMethod('POST')) {
            $training = null;
            if(!empty($data['trainingId'])) {
                $training = $trainingRepo->find($data['trainingId']);
                if(isset($training)) {
                    $trainingRequirement->setTrainingId($training);
                }
            }else {
                return $this->json(['message' => 'Invalid training ID'], 400);
            }
            $trainingRequirement->setLabel($data['label'] ?? null);
            $trainingRequirement->setIsRequired($data['isRequired'] ?? false);
            $trainingRequirement->setIsEnabled($data['isEnabled'] ?? true);
            $trainingRequirement->setIsDeleted($data['isDeleted'] ?? false);
            $trainingRequirement->setCreatedAt(new \DateTimeImmutable());
            $trainingRequirement->setUpdatedAt(new \DateTimeImmutable());

            $em->persist($trainingRequirement);
            $em->flush();

            return $this->json(['message' => 'Training requirement created successfully', 'id' => $trainingRequirement->getId()], 201);
        }
    }
}
