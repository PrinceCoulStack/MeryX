<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Conversation\CreateConversation;
use App\Controller\Conversation\ListConversation;
use App\Controller\Conversation\UpdateConversation;
use App\Repository\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['conversation:read']],
    denormalizationContext: ['groups' => ['conversation:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/conversations',
            controller: ListConversation::class,
            name: 'listConversation'
        ),
        new Post(
            uriTemplate: '/conversations',
            controller: CreateConversation::class,
            name: 'createConversation'
        ),
        new Get(
            uriTemplate: '/conversations/{id}',
            name: 'getConversation'
        ),
        new Put(
            uriTemplate: '/conversations/{id}',
            controller: UpdateConversation::class,
            name: 'updateConversation'
        ),
        new Delete(
            uriTemplate: '/conversations/{id}',
            name: 'deleteConversation'
        )
    ]
)]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['conversation:read', 'conversation:write', 'conversationParticipant:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['conversation:read', 'conversation:write'])]
    private ?string $subject = null;

    #[ORM\Column(length: 255)]
    #[Groups(['conversation:read', 'conversation:write'])]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['conversation:read', 'conversation:write'])]
    private ?string $program = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['conversation:read', 'conversation:write'])]
    private ?string $faculty = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['conversation:read', 'conversation:write'])]
    private ?string $internshipCycle = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['conversation:read', 'conversation:write'])]
    private ?string $academicYear = null;

    #[ORM\Column(options: ['default' => false])]
    #[Groups(['conversation:read', 'conversation:write'])]
    private bool $moderatorVisible = false;

    #[ORM\Column(options: ['default' => false])]
    #[Groups(['conversation:read', 'conversation:write'])]
    private bool $interventionRequired = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['conversation:read', 'conversation:write'])]
    private ?string $moderatorNote = null;

    #[ORM\Column]
    #[Groups(['conversation:read', 'conversation:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['conversation:read', 'conversation:write'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'conversations')]
    #[Groups(['conversation:read', 'conversation:write', 'conversationParticipant:read'])]
    private ?ConversationParticipant $conversationParticipantId = null;

    /**
     * @var Collection<int, ConversationParticipant>
     */
    #[ORM\OneToMany(targetEntity: ConversationParticipant::class, mappedBy: 'conversation', cascade: ['persist', 'remove'])]
    private Collection $participants;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'conversationId')]
    private Collection $messages;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getProgram(): ?string
    {
        return $this->program;
    }

    public function setProgram(?string $program): static
    {
        $this->program = $program;

        return $this;
    }

    public function getFaculty(): ?string
    {
        return $this->faculty;
    }

    public function setFaculty(?string $faculty): static
    {
        $this->faculty = $faculty;

        return $this;
    }

    public function getInternshipCycle(): ?string
    {
        return $this->internshipCycle;
    }

    public function setInternshipCycle(?string $internshipCycle): static
    {
        $this->internshipCycle = $internshipCycle;

        return $this;
    }

    public function getAcademicYear(): ?string
    {
        return $this->academicYear;
    }

    public function setAcademicYear(?string $academicYear): static
    {
        $this->academicYear = $academicYear;

        return $this;
    }

    public function isModeratorVisible(): bool
    {
        return $this->moderatorVisible;
    }

    public function setModeratorVisible(bool $moderatorVisible): static
    {
        $this->moderatorVisible = $moderatorVisible;

        return $this;
    }

    public function isInterventionRequired(): bool
    {
        return $this->interventionRequired;
    }

    public function setInterventionRequired(bool $interventionRequired): static
    {
        $this->interventionRequired = $interventionRequired;

        return $this;
    }

    public function getModeratorNote(): ?string
    {
        return $this->moderatorNote;
    }

    public function setModeratorNote(?string $moderatorNote): static
    {
        $this->moderatorNote = $moderatorNote;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getConversationParticipantId(): ?ConversationParticipant
    {
        return $this->conversationParticipantId;
    }

    public function setConversationParticipantId(?ConversationParticipant $conversationParticipantId): static
    {
        $this->conversationParticipantId = $conversationParticipantId;

        return $this;
    }

    /**
     * @return Collection<int, ConversationParticipant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(ConversationParticipant $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->setConversation($this);
        }

        return $this;
    }

    public function removeParticipant(ConversationParticipant $participant): static
    {
        if ($this->participants->removeElement($participant)) {
            if ($participant->getConversation() === $this) {
                $participant->setConversation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setConversationId($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getConversationId() === $this) {
                $message->setConversationId(null);
            }
        }

        return $this;
    }
}
