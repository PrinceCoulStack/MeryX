<?php

namespace App\Entity;

use App\Repository\UserTypeRepository;
use App\Controller\UserType\ListUserType;
use App\Controller\UserType\CreateUserType;
use App\Controller\UserType\UpdateUserType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;

#[ORM\Entity(repositoryClass: UserTypeRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['userType:read']],
    denormalizationContext: ['groups' => ['userType:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/userTypes',
            controller: ListUserType::class,
            name: 'listUserType'
        ),
        new Post(
            uriTemplate: '/userTypes',
            controller: CreateUserType::class,
            name: 'createUserType'
        ),
        new Get(
            uriTemplate: '/userTypes/{id}',
            controller: ListUserType::class,
            name: 'getUserType'
        ),
        new Put(
            uriTemplate: '/userTypes/{id}',
            controller: UpdateUserType::class,
            name: 'updateUserType'
        ),
        new Delete(
            uriTemplate: '/userTypes/{id}',
            name: 'deleteUserType'
        )
    ]
)]
class UserType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['userType:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['userType:read', 'userType:write'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['userType:read', 'userType:write'])]
    private array $permission = [];

    #[ORM\Column(length: 255)]
    #[Groups(['userType:read', 'userType:write'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['userType:read', 'userType:write'])]
    private ?bool $isEnabled = null;

    #[ORM\Column]
    private ?bool $isDeleted = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'userTypeId')]
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getRoleName(): string
    {
        $rawName = trim((string) $this->name);

        if ($rawName === '') {
            return 'ROLE_USER';
        }

        $normalized = strtoupper(trim((string) preg_replace('/[^A-Za-z0-9]+/', '_', $rawName), '_'));

        $mapped = [
            'SUPER_ADMIN' => 'SUPER_ADMIN',
            'SUPERADMIN' => 'SUPER_ADMIN',
            'ADMIN' => 'ADMIN',
            'ADMINISTRATOR' => 'ADMIN',
            'UNIVERSITY' => 'UNIVERSITY',
            'COMPANY' => 'COMPANY',
            'STUDENT' => 'STUDENT',
        ];

        return $mapped[$normalized] ?? $normalized;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPermission(): array
    {
        return $this->permission;
    }

    public function setPermission(array $permission): static
    {
        $this->permission = $permission;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): static
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): static
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    #[Groups(['userType:read'])]
    public function getAssignedUserCount(): int
    {
        return count($this->users ?? []);
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users ??= new ArrayCollection();
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setUserTypeId($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getUserTypeId() === $this) {
                $user->setUserTypeId(null);
            }
        }

        return $this;
    }

}
