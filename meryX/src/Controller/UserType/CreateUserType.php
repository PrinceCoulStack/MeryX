<?php
namespace App\Controller\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateUserType extends AbstractController
{
    public function __invoke(EntityManagerInterface $entityManager, Request $request)
    {
        // Your logic to create a user type goes here
        $type = new UserType();
        $data = json_decode($request->getContent(), true);

        if($request->isMethod('POST')) {
            $type->setName($data['name'] ?? null);
            $type->setPermission($data['permission'] ?? []);
            $type->setDescription($data['description'] ?? null);
            $type->setIsEnabled($data['isEnabled'] ?? true);
            $type->setIsDeleted($data['isDeleted'] ?? false);

            $entityManager->persist($type);
            $entityManager->flush();

            return $this->json(['message' => 'User type created successfully', 'id' => $type->getId()], 201);
        }
        return $this->json(['message' => 'Method not allowed'], 405);
    }
}
