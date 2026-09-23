<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\ConversationParticipant\CreateConversationParticipant;
use App\Controller\ConversationParticipant\ListConversationParticipant;
use App\Controller\ConversationParticipant\UpdateConversationParticipant;
use App\Repository\ConversationParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ConversationParticipantRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['conversationParticipant:read']],
    denormalizationContext: ['groups' => ['conversationParticipant:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/conversationParticipants',
            controller: ListConversationParticipant::class,
            name: 'listConversationParticipant'
        ),
        new Post(
            uriTemplate: '/conversationParticipants',
            controller: CreateConversationParticipant::class,
            name: 'createConversationParticipant'
        ),
        new Get(
            uriTemplate: '/conversationParticipants/{id}',
            name: 'getConversationParticipant'
        ),
        new Put(
            uriTemplate: '/conversationParticipants/{id}',
            controller: UpdateConversationParticipant::class,
            name: 'updateConversationParticipant'
        ),
        new Delete(
            uriTemplate: '/conversationParticipants/{id}',
            name: 'deleteConversationParticipant'
        )
    ]
)]
class ConversationParticipant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['conversationParticipant:read', 'conversationParticipant:write', 'user:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Conversation::class, inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['conversationParticipant:read', 'conversationParticipant:write'])]
    private ?Conversation $conversation = null;

    #[ORM\ManyToOne(inversedBy: 'conversationParticipants')]
    #[Groups(['conversationParticipant:read', 'conversationParticipant:write', 'user:read'])]
    private ?User $user = null;

    #[ORM\Column]
    #[Groups(['conversationParticipant:read', 'conversationParticipant:write'])]
    private ?\DateTimeImmutable $joinedAt = null;

    #[ORM\Column]
    #[Groups(['conversationParticipant:read', 'conversationParticipant:write'])]
    private ?\DateTimeImmutable $lastReadAt = null;

    /**
     * @var Collection<int, Conversation>
     */
    #[ORM\OneToMany(targetEntity: Conversation::class, mappedBy: 'conversationParticipantId')]
    private Collection $conversations;

    public function __construct()
    {
        $this->conversations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): static
    {
        $this->conversation = $conversation;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getJoinedAt(): ?\DateTimeImmutable
    {
        return $this->joinedAt;
    }

    public function setJoinedAt(\DateTimeImmutable $joinedAt): static
    {
        $this->joinedAt = $joinedAt;

        return $this;
    }

    public function getLastReadAt(): ?\DateTimeImmutable
    {
        return $this->lastReadAt;
    }

    public function setLastReadAt(\DateTimeImmutable $lastReadAt): static
    {
        $this->lastReadAt = $lastReadAt;

        return $this;
    }

    /**
     * @return Collection<int, Conversation>
     */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation): static
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations->add($conversation);
            $conversation->setConversationParticipantId($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): static
    {
        if ($this->conversations->removeElement($conversation)) {
            // set the owning side to null (unless already changed)
            if ($conversation->getConversationParticipantId() === $this) {
                $conversation->setConversationParticipantId(null);
            }
        }

        return $this;
    }
}
