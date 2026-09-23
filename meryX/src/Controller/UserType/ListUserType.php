<?php

namespace App\Controller\UserType;

use App\Repository\UserTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class ListUserType extends AbstractController
{
    public function __invoke(UserTypeRepository $repo)
    {
        $userTypes = $repo->findAll();

        return $this->json($userTypes, 200, [], ['groups' => ['userType:read']]);
    }
}
