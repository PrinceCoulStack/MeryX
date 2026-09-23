<?php
namespace App\Controller\SystemSetting;

use App\Repository\SystemSettingRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateSystemSetting extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, SystemSettingRepository $systemSettingRepository, UserRepository $userRepo, int $id)
    {
        $systemSetting = $systemSettingRepository->find($id);
        if (!$systemSetting) {
            return $this->json(['message' => 'System setting not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            if (isset($data['value'])) {
                $systemSetting->setValue($data['value']);
            }
            if (isset($data['systemKey'])) {
                $systemSetting->setSystemKey($data['systemKey']);
            }

            if (!empty($data['userKeyId'])) {
                $user = $userRepo->find($data['userKeyId']);
                if ($user) {
                    $systemSetting->setUserKeyId($user);
                } else {
                    return $this->json(['message' => 'Invalid user ID'], 400);
                }
            }

            $systemSetting->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->json(['message' => 'System setting updated successfully'], 200);
        }
    }
}
