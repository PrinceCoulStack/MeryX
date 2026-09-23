<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Address\CreateAddress;
use App\Controller\Address\ListAddress;
use App\Controller\Address\UpdateAddress;
use App\Repository\AddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['address:read']],
    denormalizationContext: ['groups' => ['address:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/addresses',
            controller: ListAddress::class,
            name: 'listAddress'
        ),
        new Post(
            uriTemplate: '/addresses',
            controller: CreateAddress::class,
            name: 'createAddress'
        ),
        new Get(
            uriTemplate: '/addresses/{id}',
            name: 'getAddress'
        ),
        new Put(
            uriTemplate: '/addresses/{id}',
            controller: UpdateAddress::class,
            name: 'updateAddress'
        ),
        new Delete(
            uriTemplate: '/addresses/{id}',
            name: 'deleteAddress'
        )
    ]
)]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['address:read', 'address:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['address:read', 'address:write'])]
    private ?string $city = null;

    #[ORM\Column(length: 255)]
    #[Groups(['address:read', 'address:write'])]
    private ?string $state = null;

    #[ORM\Column(length: 255)]
    #[Groups(['address:read', 'address:write'])]
    private ?string $country = null;

    #[ORM\Column]
    #[Groups(['address:read', 'address:write'])]
    private ?\DateTimeImmutable $createAt = null;

    #[ORM\Column]
    #[Groups(['address:read', 'address:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'AddressId')]
    private Collection $users;

    #[ORM\Column(length: 255)]
    #[Groups(['address:read', 'address:write'])]
    private ?string $latitude = null;

    #[ORM\Column(length: 255)]
    #[Groups(['address:read', 'address:write'])]
    private ?string $longitude = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeImmutable $createAt): static
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setAddressId($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            if ($user->getAddressId() === $this) {
                $user->setAddressId(null);
            }
        }

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }
}
