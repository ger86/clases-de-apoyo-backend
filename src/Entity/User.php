<?php

namespace App\Entity;

use App\Enum\PremiumProvider;
use App\Enum\SubscriptionStatus;
use App\Repository\UserRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Stripe\Invoice;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $email = '';

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $password = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $premiumUntil = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $customerId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $subscriptionId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $subscriptionStatus = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $premiumProvider = null;

    #[ORM\Column(type: 'string', length: 64, nullable: true, unique: true)]
    private ?string $appleOriginalTransactionId = null;

    #[ORM\Column(type: 'string', length: 36, nullable: true, unique: true)]
    private ?string $appleAppAccountToken = null;

    #[ORM\Column(type: 'string', length: 40, nullable: true)]
    private ?string $appleSubscriptionStatus = null;

    /** Set once, when someone who bought the paid app claims the free year. */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $legacyAppAccessClaimedAt = null;

    /** Only kept for the record: Apple leaves it empty before iOS 18.4. */
    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $legacyAppTransactionId = null;

    /** @var Collection<int,PremiumPayment> */
    #[ORM\OneToMany(targetEntity: PremiumPayment::class, mappedBy: 'user', cascade: ['all'])]
    private Collection $premiumPayments;

    #[ORM\Column(type: 'boolean')]
    private bool $discountCodeSent = false;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'update')]
    private DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'boolean')]
    private bool $isVerified = false;

    private string $plainPassword;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        $this->premiumPayments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPremiumUntil(): ?DateTimeImmutable
    {
        return $this->premiumUntil;
    }

    public function setPremiumUntil(?DateTimeInterface $premiumUntil): self
    {
        $this->premiumUntil = $premiumUntil instanceof DateTimeImmutable || $premiumUntil === null
            ? $premiumUntil
            : DateTimeImmutable::createFromMutable($premiumUntil);

        return $this;
    }

    public function isPremium(): bool
    {
        $now = new DateTimeImmutable();
        return $now < $this->premiumUntil;
    }

    public function addPremiumPayment(PremiumPayment $premiumPayment): self
    {
        $this->premiumPayments[] = $premiumPayment;
        $premiumPayment->setUser($this);

        return $this;
    }

    public function removePremiumPayment(PremiumPayment $premiumPayment): self
    {
        $this->premiumPayments->removeElement($premiumPayment);
        return $this;
    }

    /**
     * @return Collection<int,PremiumPayment>
     */
    public function getPremiumPayments(): Collection
    {
        return $this->premiumPayments;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt instanceof DateTimeImmutable ? $createdAt : DateTimeImmutable::createFromMutable($createdAt);

        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt instanceof DateTimeImmutable ? $updatedAt : DateTimeImmutable::createFromMutable($updatedAt);

        return $this;
    }

    public function getDiscountCodeSent(): bool
    {
        return $this->discountCodeSent;
    }

    public function setDiscountCodeSent(bool $discountCodeSent): self
    {
        $this->discountCodeSent = $discountCodeSent;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->getUserIdentifier();
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(?array $roles): self
    {
        $this->roles = $roles ?? [];

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void {}

    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    public function setCustomerId(?string $customerId): self
    {
        $this->customerId = $customerId;

        return $this;
    }

    public function getSubscriptionId(): ?string
    {
        return $this->subscriptionId;
    }

    public function setSubscriptionId(?string $subscriptionId): self
    {
        $this->subscriptionId = $subscriptionId;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function processInvoicePayment(Invoice $invoice): PremiumPayment
    {
        $line = $invoice->lines->data[0];
        $lineData = $line->toArray();

        $subscriptionId = $lineData['subscription'] ?? null;
        if (\is_array($subscriptionId)) {
            $subscriptionId = $subscriptionId['id'] ?? null;
        }
        $this->subscriptionId = \is_string($subscriptionId) ? $subscriptionId : null;

        $customerId = $invoice->customer;
        if (\is_object($customerId) && property_exists($customerId, 'id')) {
            $customerId = $customerId->id;
        }
        $this->customerId = \is_string($customerId) ? $customerId : null;

        $premiumUntil = DateTimeImmutable::createFromFormat('U', (string) $line->period->end);
        if ($premiumUntil !== false) {
            $this->grantPremiumUntil($premiumUntil->modify('+6 hours'), PremiumProvider::STRIPE);
        }

        $subscriptionItemId = $lineData['parent']['subscription_item_details']['subscription_item'] ?? null;
        if (!\is_string($subscriptionItemId) || $subscriptionItemId === '') {
            $subscriptionItemId = (string) ($lineData['id'] ?? $line->id);
        }

        $premiumPayment = new PremiumPayment($this, $subscriptionItemId);
        $this->addPremiumPayment($premiumPayment);
        return $premiumPayment;
    }

    public function processSubscription(string $subscriptionId, string $customerId, string $subscriptionStatus): self
    {
        $this->subscriptionId = $subscriptionId;
        $this->customerId = $customerId;
        $this->subscriptionStatus = $subscriptionStatus;
        return $this;
    }

    public function getSubscriptionStatus(): ?string
    {
        return $this->subscriptionStatus;
    }

    public function setSubscriptionStatus(?string $subscriptionStatus): self
    {
        $this->subscriptionStatus = $subscriptionStatus;

        return $this;
    }

    public function isSubscriptionActive(): bool
    {
        return $this->subscriptionStatus === SubscriptionStatus::ACTIVE;
    }

    public function getLegacyAppAccessClaimedAt(): ?DateTimeImmutable
    {
        return $this->legacyAppAccessClaimedAt;
    }

    public function hasClaimedLegacyAppAccess(): bool
    {
        return $this->legacyAppAccessClaimedAt !== null;
    }

    public function claimLegacyAppAccess(DateTimeImmutable $claimedAt, ?string $appTransactionId): self
    {
        $this->legacyAppAccessClaimedAt = $claimedAt;
        $this->legacyAppTransactionId = $appTransactionId;

        return $this;
    }

    public function getLegacyAppTransactionId(): ?string
    {
        return $this->legacyAppTransactionId;
    }

    public function getPremiumProvider(): ?string
    {
        return $this->premiumProvider;
    }

    public function setPremiumProvider(?string $premiumProvider): self
    {
        $this->premiumProvider = $premiumProvider;

        return $this;
    }

    public function getAppleOriginalTransactionId(): ?string
    {
        return $this->appleOriginalTransactionId;
    }

    public function setAppleOriginalTransactionId(?string $appleOriginalTransactionId): self
    {
        $this->appleOriginalTransactionId = $appleOriginalTransactionId;

        return $this;
    }

    public function getAppleAppAccountToken(): ?string
    {
        return $this->appleAppAccountToken;
    }

    public function setAppleAppAccountToken(?string $appleAppAccountToken): self
    {
        $this->appleAppAccountToken = $appleAppAccountToken;

        return $this;
    }

    public function getAppleSubscriptionStatus(): ?string
    {
        return $this->appleSubscriptionStatus;
    }

    public function setAppleSubscriptionStatus(?string $appleSubscriptionStatus): self
    {
        $this->appleSubscriptionStatus = $appleSubscriptionStatus;

        return $this;
    }

    /**
     * Extends premium access and records which payment provider owns it.
     *
     * A provider never shortens access that another provider granted, so a user who
     * subscribes on the web and on the App Store keeps the longest of the two.
     * Returns false when the call was ignored for that reason.
     */
    public function grantPremiumUntil(DateTimeImmutable $premiumUntil, string $provider): bool
    {
        $currentProvider = $this->premiumProvider;
        $isOwnedByAnotherProvider = $currentProvider !== null && $currentProvider !== $provider;

        if ($isOwnedByAnotherProvider && $this->isPremium() && $premiumUntil < $this->premiumUntil) {
            return false;
        }

        $this->premiumUntil = $premiumUntil;
        $this->premiumProvider = $provider;

        return true;
    }

    /**
     * Ends premium access, but only when the given provider is the one that granted it.
     * Used for refunds and revocations, where access must stop immediately.
     */
    public function revokePremium(string $provider): bool
    {
        if ($this->premiumProvider !== null && $this->premiumProvider !== $provider) {
            return false;
        }

        $this->premiumUntil = new DateTimeImmutable();
        $this->premiumProvider = $provider;

        return true;
    }

    public function __toString()
    {
        return $this->email ?? '';
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }
}
