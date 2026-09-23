<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\Users\ChangeUserPassword;
use App\Controller\Users\CreateUser;
use App\Controller\Users\ListUsers;
use App\Controller\Users\UpdateUser;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
// use Symfony\Contracts\Cache\ItemInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ApiResource(
    normalizationContext:['groups'=> ['user:read']],
    denormalizationContext:['groups' => ['user:write']],
    operations: [
        new GetCollection(
            uriTemplate: '/users',
            controller: ListUsers::class,
            name: 'listUsers',
            // security: "is_granted('PUBLIC_ACCESS')"
        ),
        new Post(
            uriTemplate: '/users',
            controller: CreateUser::class,
            name:'createUser'
        ),

        //Item
        new Get(
            uriTemplate: '/users/{id}',
            name: 'getUser'
        ),
        new Put(
            uriTemplate: '/users/{id}',
            controller: UpdateUser::class,
            name: 'updateUser'
        ),
        new Delete(
            uriTemplate: '/users/{id}',
            name: 'deleteUser'
        ),
        new Post(
            uriTemplate: '/users/{id}/change-password',
            controller: ChangeUserPassword::class,
            name: 'changeUserPassword'
        )
    ]
)]
// UserInterface === deleted because I remove the roles
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    //Email are unique
    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read', 'user:write', 'applicationStatusHistory:read', 'applicationStatusHistory:write', 'studentProfile:read', 'companyProfile:read'])]
    private ?string $email = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write', 'applicationStatusHistory:read', 'applicationStatusHistory:write', 'studentProfile:read', 'companyProfile:read'])]
    private ?string $status = null;

    #[ORM\Column]
    #[Groups(['user:read', 'user:write', 'applicationStatusHistory:read', 'applicationStatusHistory:write', 'studentProfile:read', 'companyProfile:read'])]
    private ?\DateTimeImmutable $createAt = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[Groups(['user:read', 'user:write','address:read', 'address:write'])]
    private ?Address $AddressId = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write', 'applicationStatusHistory:read', 'applicationStatusHistory:write', 'studentProfile:read', 'companyProfile:read'])]
    private ?string $phone = null;

    // #[ORM\Column]
    // #[Groups(['user:read', 'user:write'])]
    // private array $roles = [];

    #[ORM\Column]
    #[Groups(['user:read', 'user:write', 'applicationStatusHistory:read', 'applicationStatusHistory:write', 'studentProfile:read', 'companyProfile:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    #[Groups(['user:read', 'user:write', 'applicationStatusHistory:read', 'applicationStatusHistory:write', 'studentProfile:read', 'companyProfile:read'])]
    private ?\DateTimeImmutable $lastLoginAt = null;

    #[ORM\Column]
    #[Groups(['user:read', 'user:write', 'applicationStatusHistory:read', 'applicationStatusHistory:write', 'studentProfile:read', 'companyProfile:read'])]
    private ?bool $isActived = null;

    /**
     * @var Collection<int, ApplicationStatusHistory>
     */
    #[ORM\OneToMany(targetEntity: ApplicationStatusHistory::class, mappedBy: 'changedById')]
    #[Groups(['user:read', 'user:write', 'applicationStatusHistory:read', 'applicationStatusHistory:write', 'studentProfile:read', 'companyProfile:read'])]
    private Collection $applicationStatusHistories;

    /**
     * @var Collection<int, CompanyPost>
     */
    #[ORM\OneToMany(targetEntity: CompanyPost::class, mappedBy: 'authorId')]
    #[Groups(['user:read', 'user:write', 'companyPost:read', 'studentProfile:read', 'companyProfile:read'])]
    private Collection $companyPosts;

    /**
     * @var Collection<int, PostComments>
     */
    #[ORM\OneToMany(targetEntity: PostComments::class, mappedBy: 'authorId')]
    #[Groups(['user:read', 'user:write', 'postComments:read', 'studentProfile:read', 'companyProfile:read'])]
    private Collection $postComments;

    /**
     * @var Collection<int, PostReaction>
     */
    #[ORM\OneToMany(targetEntity: PostReaction::class, mappedBy: 'userId')]
    #[Groups(['user:read', 'user:write', 'postReaction:read'])]
    private Collection $postReactions;

    /**
     * @var Collection<int, ConversationParticipant>
     */
    #[ORM\OneToMany(targetEntity: ConversationParticipant::class, mappedBy: 'user')]
    #[Groups(['user:read', 'user:write', 'conversationParticipant:read'])]
    private Collection $conversationParticipants;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'senderId')]
    #[Groups(['user:read', 'user:write', 'message:read'])]
    private Collection $messages;

    /**
     * @var Collection<int, MessageReceipt>
     */
    #[ORM\OneToMany(targetEntity: MessageReceipt::class, mappedBy: 'user')]
    private Collection $messageReceipts;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'userId')]
    #[Groups(['user:read', 'user:write', 'notification:read'])]
    private Collection $notifications;

    /**
     * @var Collection<int, SystemSetting>
     */
    #[ORM\OneToMany(targetEntity: SystemSetting::class, mappedBy: 'userKeyId')]
    #[Groups(['user:read', 'user:write', 'systemSetting:read'])]
    private Collection $systemSettings;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[Groups(['user:read', 'user:write'])]
    private ?UserType $userTypeId = null;

    public function __construct()
    {
        $this->applicationStatusHistories = new ArrayCollection();
        $this->companyPosts = new ArrayCollection();
        $this->postComments = new ArrayCollection();
        $this->postReactions = new ArrayCollection();
        $this->conversationParticipants = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->messageReceipts = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->systemSettings = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
    public function getRoles(): array
    {
        $roleName = $this->userTypeId?->getRoleName();

        if ($roleName === null || $roleName === '') {
            return ['ROLE_USER'];
        }

        return [str_starts_with($roleName, 'ROLE_') ? $roleName : 'ROLE_' . $roleName];
    }

    public function getRoleName(): ?string
    {
        return $this->userTypeId?->getRoleName();
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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

    public function getAddressId(): ?Address
    {
        return $this->AddressId;
    }

    public function setAddressId(?Address $AddressId): static
    {
        $this->AddressId = $AddressId;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    // public function getRoles(): array
    // {
    //     return $this->roles;
    // }

    // public function setRoles(array $roles): static
    // {
    //     $this->roles = $roles;

    //     return $this;
    // }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(\DateTimeImmutable $lastLoginAt): static
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    public function isActived(): ?bool
    {
        return $this->isActived;
    }

    public function setIsActived(bool $isActived): static
    {
        $this->isActived = $isActived;

        return $this;
    }

    /**
     * @return Collection<int, ApplicationStatusHistory>
     */
    public function getApplicationStatusHistories(): Collection
    {
        return $this->applicationStatusHistories;
    }

    public function addApplicationStatusHistory(ApplicationStatusHistory $applicationStatusHistory): static
    {
        if (!$this->applicationStatusHistories->contains($applicationStatusHistory)) {
            $this->applicationStatusHistories->add($applicationStatusHistory);
            $applicationStatusHistory->setChangedById($this);
        }

        return $this;
    }

    public function removeApplicationStatusHistory(ApplicationStatusHistory $applicationStatusHistory): static
    {
        if ($this->applicationStatusHistories->removeElement($applicationStatusHistory)) {
            // set the owning side to null (unless already changed)
            if ($applicationStatusHistory->getChangedById() === $this) {
                $applicationStatusHistory->setChangedById(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CompanyPost>
     */
    public function getCompanyPosts(): Collection
    {
        return $this->companyPosts;
    }

    public function addCompanyPost(CompanyPost $companyPost): static
    {
        if (!$this->companyPosts->contains($companyPost)) {
            $this->companyPosts->add($companyPost);
            $companyPost->setAuthorId($this);
        }

        return $this;
    }

    public function removeCompanyPost(CompanyPost $companyPost): static
    {
        if ($this->companyPosts->removeElement($companyPost)) {
            // set the owning side to null (unless already changed)
            if ($companyPost->getAuthorId() === $this) {
                $companyPost->setAuthorId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PostComments>
     */
    public function getPostComments(): Collection
    {
        return $this->postComments;
    }

    public function addPostComment(PostComments $postComment): static
    {
        if (!$this->postComments->contains($postComment)) {
            $this->postComments->add($postComment);
            $postComment->setAuthorId($this);
        }

        return $this;
    }

    public function removePostComment(PostComments $postComment): static
    {
        if ($this->postComments->removeElement($postComment)) {
            // set the owning side to null (unless already changed)
            if ($postComment->getAuthorId() === $this) {
                $postComment->setAuthorId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PostReaction>
     */
    public function getPostReactions(): Collection
    {
        return $this->postReactions;
    }

    public function addPostReaction(PostReaction $postReaction): static
    {
        if (!$this->postReactions->contains($postReaction)) {
            $this->postReactions->add($postReaction);
            $postReaction->setUserId($this);
        }

        return $this;
    }

    public function removePostReaction(PostReaction $postReaction): static
    {
        if ($this->postReactions->removeElement($postReaction)) {
            // set the owning side to null (unless already changed)
            if ($postReaction->getUserId() === $this) {
                $postReaction->setUserId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ConversationParticipant>
     */
    public function getConversationParticipants(): Collection
    {
        return $this->conversationParticipants;
    }

    public function addConversationParticipant(ConversationParticipant $conversationParticipant): static
    {
        if (!$this->conversationParticipants->contains($conversationParticipant)) {
            $this->conversationParticipants->add($conversationParticipant);
            $conversationParticipant->setUser($this);
        }

        return $this;
    }

    public function removeConversationParticipant(ConversationParticipant $conversationParticipant): static
    {
        if ($this->conversationParticipants->removeElement($conversationParticipant)) {
            // set the owning side to null (unless already changed)
            if ($conversationParticipant->getUser() === $this) {
                $conversationParticipant->setUser(null);
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
            $message->setSenderId($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getSenderId() === $this) {
                $message->setSenderId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MessageReceipt>
     */
    public function getMessageReceipts(): Collection
    {
        return $this->messageReceipts;
    }

    public function addMessageReceipt(MessageReceipt $messageReceipt): static
    {
        if (!$this->messageReceipts->contains($messageReceipt)) {
            $this->messageReceipts->add($messageReceipt);
            $messageReceipt->setUser($this);
        }

        return $this;
    }

    public function removeMessageReceipt(MessageReceipt $messageReceipt): static
    {
        if ($this->messageReceipts->removeElement($messageReceipt)) {
            if ($messageReceipt->getUser() === $this) {
                $messageReceipt->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setUserId($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUserId() === $this) {
                $notification->setUserId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SystemSetting>
     */
    public function getSystemSettings(): Collection
    {
        return $this->systemSettings;
    }

    public function addSystemSetting(SystemSetting $systemSetting): static
    {
        if (!$this->systemSettings->contains($systemSetting)) {
            $this->systemSettings->add($systemSetting);
            $systemSetting->setUserKeyId($this);
        }

        return $this;
    }

    public function removeSystemSetting(SystemSetting $systemSetting): static
    {
        if ($this->systemSettings->removeElement($systemSetting)) {
            // set the owning side to null (unless already changed)
            if ($systemSetting->getUserKeyId() === $this) {
                $systemSetting->setUserKeyId(null);
            }
        }

        return $this;
    }

    public function getUserTypeId(): ?UserType
    {
        return $this->userTypeId;
    }

    public function setUserTypeId(?UserType $userTypeId): static
    {
        $this->userTypeId = $userTypeId;

        return $this;
    }
}
