<?php
namespace App\Controller\Skill;

use App\Entity\Skills;
use App\Repository\SkillsRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateSkills extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, SkillsRepository $skillsRep)
    {
        // logic to list user types goes here
        $skill = new Skills();
        $data = json_decode($request->getContent(), true);

        if($request->isMethod('POST')) {
            $skill->setName($data['name'] ?? null);
            $skill->setLevel($data['level'] ?? null);
            $skill->setIsEnabled($data['isEnabled'] ?? true);
            $skill->setIsDeleted($data['isDeleted'] ?? false);
            //StudentProfile association
            $skill->setStudentProfileId($data['studentProfileId'] ?? null);
            $skill->setCreatedAt(new \DateTimeImmutable());
            $skill->setUpdatedAt(new \DateTimeImmutable());

            $em->persist($skill);
            $em->flush();

            return $this->json(['message' => 'Skill created successfully', 'id' => $skill->getId()], 201);
        }
    }
}
