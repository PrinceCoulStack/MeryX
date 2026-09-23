<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Message\CreateMessage;
use App\Controller\Message\ListMessage;
use App\Controller\Message\UpdateMessage;
use App\Repository\MessageRepository;
use App\Service\Messaging\MessageEncryptionService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['message:read']],
    denormalizationContext: ['groups' => ['message:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/messages',
            controller: ListMessage::class,
            name: 'listMessage'
        ),
        new GetCollection(
            uriTemplate: '/conversations/{id}/messages',
            controller: ListMessage::class,
            name: 'listConversationMessages'
        ),
        new Post(
            uriTemplate: '/messages',
            controller: CreateMessage::class,
            name: 'createMessage'
        ),
        new Get(
            uriTemplate: '/messages/{id}',
            name: 'getMessage'
        ),
        new Put(
            uriTemplate: '/messages/{id}',
            controller: UpdateMessage::class,
            name: 'updateMessage'
        ),
        new Delete(
            uriTemplate: '/messages/{id}',
            name: 'deleteMessage'
        )
    ]
)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['message:read', 'message:write', 'conversation:read', 'user:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[Groups(['message:read', 'message:write', 'conversation:read'])]
    private ?Conversation $conversationId = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[Groups(['message:read', 'message:write', 'user:read'])]
    private ?User $senderId = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['message:read', 'message:write'])]
    private ?string $ciphertext = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['message:read', 'message:write'])]
    private ?string $nonce = null;

    #[ORM\Column(length: 64, nullable: true)]
    #[Groups(['message:read', 'message:write'])]
    private ?string $algorithm = 'XChaCha20-Poly1305';

    #[ORM\Column(length: 32, nullable: true)]
    #[Groups(['message:read', 'message:write'])]
    private ?string $keyVersion = 'v1';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['message:read', 'message:write'])]
    private ?string $contentPreview = null;

    #[ORM\Column]
    #[Groups(['message:read', 'message:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['message:read', 'message:write'])]
    private ?\DateTimeImmutable $editedAt = null;

    #[ORM\Column]
    #[Groups(['message:read', 'message:write'])]
    private ?bool $isDeleted = null;

    #[ORM\Column]
    #[Groups(['message:read', 'message:write'])]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['message:read', 'message:write'])]
    private ?string $idempotencyKey = null;

    #[ORM\Column(length: 32, nullable: true)]
    #[Groups(['message:read', 'message:write'])]
    private ?string $deliveryState = 'sent';

    /**
     * @var Collection<int, MessageReceipt>
     */
    #[ORM\OneToMany(targetEntity: MessageReceipt::class, mappedBy: 'message', cascade: ['persist', 'remove'])]
    private Collection $receipts;

    public function __construct()
    {
        $this->receipts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConversationId(): ?Conversation
    {
        return $this->conversationId;
    }

    public function setConversationId(?Conversation $conversationId): static
    {
        $this->conversationId = $conversationId;

        return $this;
    }

    public function getSenderId(): ?User
    {
        return $this->senderId;
    }

    public function setSenderId(?User $senderId): static
    {
        $this->senderId = $senderId;

        return $this;
    }

    public function getBody(): ?string
    {
        if ($this->ciphertext === null || $this->nonce === null) {
            return null;
        }

        $masterKey = $_ENV['APP_MESSAGE_MASTER_KEY'] ?? $_SERVER['APP_MESSAGE_MASTER_KEY'] ?? getenv('APP_MESSAGE_MASTER_KEY') ?: 'meryx-dev-message-key';

        return (new MessageEncryptionService())->decrypt($this->ciphertext, $this->nonce, $masterKey);
    }

    public function setBody(string $body): static
    {
        $masterKey = $_ENV['APP_MESSAGE_MASTER_KEY'] ?? $_SERVER['APP_MESSAGE_MASTER_KEY'] ?? getenv('APP_MESSAGE_MASTER_KEY') ?: 'meryx-dev-message-key';
        $encrypted = (new MessageEncryptionService())->encrypt($body, $masterKey);

        $this->ciphertext = $encrypted['ciphertext'];
        $this->nonce = $encrypted['nonce'];
        $this->algorithm = $encrypted['algorithm'];
        $this->keyVersion = $encrypted['keyVersion'];
        $this->contentPreview = $encrypted['contentPreview'];

        return $this;
    }

    public function getCiphertext(): ?string
    {
        return $this->ciphertext;
    }

    public function getNonce(): ?string
    {
        return $this->nonce;
    }

    public function getAlgorithm(): ?string
    {
        return $this->algorithm;
    }

    public function getKeyVersion(): ?string
    {
        return $this->keyVersion;
    }

    public function getContentPreview(): ?string
    {
        return $this->contentPreview;
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

    public function getEditedAt(): ?\DateTimeImmutable
    {
        return $this->editedAt;
    }

    public function setEditedAt(\DateTimeImmutable $editedAt): static
    {
        $this->editedAt = $editedAt;

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

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(\DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getIdempotencyKey(): ?string
    {
        return $this->idempotencyKey;
    }

    public function setIdempotencyKey(?string $idempotencyKey): static
    {
        $this->idempotencyKey = $idempotencyKey;

        return $this;
    }

    public function getDeliveryState(): ?string
    {
        return $this->deliveryState;
    }

    public function setDeliveryState(?string $deliveryState): static
    {
        $this->deliveryState = $deliveryState;

        return $this;
    }

    /**
     * @return Collection<int, MessageReceipt>
     */
    public function getReceipts(): Collection
    {
        return $this->receipts;
    }

    public function addReceipt(MessageReceipt $receipt): static
    {
        if (!$this->receipts->contains($receipt)) {
            $this->receipts->add($receipt);
            $receipt->setMessage($this);
        }

        return $this;
    }

    public function removeReceipt(MessageReceipt $receipt): static
    {
        if ($this->receipts->removeElement($receipt)) {
            if ($receipt->getMessage() === $this) {
                $receipt->setMessage(null);
            }
        }

        return $this;
    }
}
