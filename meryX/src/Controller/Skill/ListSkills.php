<?php

namespace App\Controller\Skill;

use App\Entity\Skills;
use App\Repository\SkillsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class ListSkills extends AbstractController
{
    public function __invoke(SkillsRepository $skillsRep, SerializerInterface $serializer)
    {
        // logic to list skills goes here
        $skills = $skillsRep->findAll();
        $json = $serializer->serialize($skills, 'json', [
            'groups' => ['skill:read']
        ]);
        return $this->json($json, 200, [
            'Content-Type' => 'application/json'
        ]);
    }
}
