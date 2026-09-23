<?php

namespace App\Controller\Address;

use App\Repository\AddressRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class UpdateAddress extends AbstractController
{
    public function __invoke(EntityManagerInterface $em, Request $request, AddressRepository $repository, int $id)
    {
        $address = $repository->find($id);
        if (!$address) {
            return $this->json(['message' => 'Address not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if ($request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('POST')) {
            foreach (['city', 'state', 'country', 'latitude', 'longitude'] as $field) {
                if (array_key_exists($field, $data)) {
                    $setter = 'set' . ucfirst($field);
                    $address->$setter($data[$field]);
                }
            }

            $address->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            return $this->json(['message' => 'Address updated successfully'], 200);
        }
    }
}
