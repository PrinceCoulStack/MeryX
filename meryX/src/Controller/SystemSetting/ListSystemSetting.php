<?php
namespace App\Controller\SystemSetting;

use App\Repository\SystemSettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class ListSystemSetting extends AbstractController
{
    public function __invoke(SystemSettingRepository $systemSettingRepository, SerializerInterface $serializer)
    {
        $systemSettings = $systemSettingRepository->findAll();
        $json = $serializer->serialize($systemSettings, 'json', [
            'groups' => ['systemSetting:read']
        ]);

        return new JsonResponse($json, 200, [
            'Content-Type' => 'application/json'
        ]);
    }
}
