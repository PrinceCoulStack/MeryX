<?php

namespace App\Controller\Address;

use App\Entity\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class CreateAddress extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request)
    {
        $address = new Address();
        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('POST')) {
            $address->setCity($data['city'] ?? '');
            $address->setState($data['state'] ?? '');
            $address->setCountry($data['country'] ?? '');
            $address->setCreateAt(new \DateTimeImmutable());
            $address->setUpdatedAt(new \DateTimeImmutable());
            $address->setLatitude($data['latitude'] ?? '');
            $address->setLongitude($data['longitude'] ?? '');

            $em->persist($address);
            $em->flush();

            return $this->json([
                'message' => 'Address created successfully',
                'id' => $address->getId(),
            ], 201);
        }
    }
}
