<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\SystemSetting\CreateSystemSetting;
use App\Controller\SystemSetting\ListSystemSetting;
use App\Controller\SystemSetting\UpdateSystemSetting;
use App\Repository\SystemSettingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SystemSettingRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['systemSetting:read']],
    denormalizationContext: ['groups' => ['systemSetting:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/systemSettings',
            controller: ListSystemSetting::class,
            name: 'listSystemSetting'
        ),
        new Post(
            uriTemplate: '/systemSettings',
            controller: CreateSystemSetting::class,
            name: 'createSystemSetting'
        ),
        new Get(
            uriTemplate: '/systemSettings/{id}',
            name: 'getSystemSetting'
        ),
        new Put(
            uriTemplate: '/systemSettings/{id}',
            controller: UpdateSystemSetting::class,
            name: 'updateSystemSetting'
        ),
        new Delete(
            uriTemplate: '/systemSettings/{id}',
            name: 'deleteSystemSetting'
        )
    ]
)]
class SystemSetting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['systemSetting:read', 'systemSetting:write', 'user:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'systemSettings')]
    #[Groups(['systemSetting:read', 'systemSetting:write', 'user:read'])]
    private ?User $userKeyId = null;

    #[ORM\Column]
    #[Groups(['systemSetting:read', 'systemSetting:write'])]
    private array $value = [];

    #[ORM\Column(length: 255)]
    #[Groups(['systemSetting:read', 'systemSetting:write'])]
    private ?string $systemKey = null;

    #[ORM\Column]
    #[Groups(['systemSetting:read', 'systemSetting:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(['systemSetting:read', 'systemSetting:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserKeyId(): ?User
    {
        return $this->userKeyId;
    }

    public function setUserKeyId(?User $userKeyId): static
    {
        $this->userKeyId = $userKeyId;

        return $this;
    }

    public function getValue(): array
    {
        return $this->value;
    }

    public function setValue(array $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getSystemKey(): ?string
    {
        return $this->systemKey;
    }

    public function setSystemKey(string $systemKey): static
    {
        $this->systemKey = $systemKey;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
