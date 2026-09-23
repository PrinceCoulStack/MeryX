<?php
namespace App\Controller\Skill;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use App\Repository\SkillsRepository;
use App\Repository\StudentProfileRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

#[AsController]
class UpdateSkill extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, SkillsRepository $repo, Request $request, StudentProfileRepository $studentProfileRepo, int $id)
    {
        // logic to update a skill goes here
        $skill = $repo->find($id);

        if (!$skill) {
            return $this->json(['message' => 'Skill not found'], 404);
        }
        $data = json_decode($request->getContent(), true);

        // Update the skill properties based on the provided data
        if($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            if (isset($data['name'])) {
                $skill->setName($data['name']);
            }
            if (isset($data['level'])) {
                $skill->setLevel($data['level']);
            }
            if (isset($data['isEnabled'])) {
                $skill->setIsEnabled($data['isEnabled']);
            }
            if (isset($data['isDeleted'])) {
                $skill->setIsDeleted($data['isDeleted']);
            }

            $studentProfile = null;
            if(!empty($data['studentProfileId'])) {
                $studentProfile = $studentProfileRepo->find($data['studentProfileId']);
                if(isset($data['studentProfileId']) && $studentProfile) {
                    $skill->setStudentProfileId($studentProfile);
                } else {
                    return $this->json(['message' => 'Invalid student profile ID'], 400);
                }
            }

            if (isset($data['updatedAt'])) {
                $skill->setUpdatedAt(new \DateTimeImmutable());
            }

            // Persist the changes to the database
            $em->flush();

            return $this->json(['message' => 'Skill updated successfully'], 200);
        }
        return $this->json(['message' => 'Invalid request method'], 400);
    }
}
