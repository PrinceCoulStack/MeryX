<?php
namespace App\Controller\SystemSetting;

use App\Entity\SystemSetting;
use App\Repository\SystemSettingRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateSystemSetting extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, UserRepository $userRepo)
    {
        $systemSetting = new SystemSetting();
        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('POST')) {
            if (!empty($data['userKeyId'])) {
                $user = $userRepo->find($data['userKeyId']);
                if ($user) {
                    $systemSetting->setUserKeyId($user);
                } else {
                    return $this->json(['message' => 'Invalid user ID'], 400);
                }
            }

            $systemSetting->setValue($data['value'] ?? []);
            $systemSetting->setSystemKey($data['systemKey'] ?? null);
            $systemSetting->setCreatedAt(new \DateTimeImmutable());
            $systemSetting->setUpdatedAt(new \DateTimeImmutable());

            $em->persist($systemSetting);
            $em->flush();

            return $this->json(['message' => 'System setting created successfully', 'id' => $systemSetting->getId()], 201);
        }
    }
}
