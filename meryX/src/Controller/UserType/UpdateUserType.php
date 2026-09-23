<?php
namespace App\Controller\UserType;

use App\Entity\UserType;
use App\Repository\UserTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
#[AsController]
class UpdateUserType extends AbstractController
{
    public function __invoke(EntityManagerInterface $entityManager, UserTypeRepository $userTypeRepo, Request $request, int $id)
    {
        // logic to update a user type goes here
        $userType = $userTypeRepo->find($id);
        if (!$userType) {
            return $this->json(['message' => 'User type not found'], 404);
        }
        $data = json_decode($request->getContent(), true);

        if($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            if (isset($data['name'])) {
                $userType->setName($data['name'] ?? $userType->getName());
            }
            if (isset($data['permission'])) {
                $userType->setPermission($data['permission'] ?? $userType->getPermission());
            }
            if (isset($data['description'])) {
                $userType->setDescription($data['description'] ?? $userType->getDescription());
            }
            if (isset($data['isEnabled'])) {
                $userType->setIsEnabled($data['isEnabled'] ?? $userType->getIsEnabled());
            }
            if (isset($data['isDeleted'])) {
                $userType->setIsDeleted($data['isDeleted'] ?? $userType->getIsDeleted());
            }

            $entityManager->flush();

            return $this->json(['message' => 'User type updated successfully'], 200);
        }
        return $this->json(['message' => 'Method not allowed'], 405);

    }
}
