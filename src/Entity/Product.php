<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'paid_product')]
class Product
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 128, unique: true)]
    private string $code = '';

    #[ORM\Column(type: 'string', length: 256)]
    private string $title = '';

    #[ORM\Column(length: 256, unique: true)]
    #[Gedmo\Slug(fields: ['title'], updatable: true)]
    private string $slug = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 256)]
    private string $stripeProductId = '';

    #[ORM\Column(type: 'string', length: 256)]
    private string $stripePriceId = '';

    #[ORM\Column(type: 'integer')]
    private int $priceCents = 0;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency = 'eur';

    /** @var array<int, array{key: string, label: string, path: string, filename: string, description?: string}> */
    #[ORM\Column(type: 'json')]
    private array $files = [];

    #[ORM\Column(type: 'boolean')]
    private bool $enabled = true;

    /** @var Collection<int,ProductPurchase> */
    #[ORM\OneToMany(targetEntity: ProductPurchase::class, mappedBy: 'product')]
    private Collection $purchases;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'update')]
    private DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->purchases = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStripeProductId(): string
    {
        return $this->stripeProductId;
    }

    public function setStripeProductId(string $stripeProductId): self
    {
        $this->stripeProductId = $stripeProductId;

        return $this;
    }

    public function getStripePriceId(): string
    {
        return $this->stripePriceId;
    }

    public function setStripePriceId(string $stripePriceId): self
    {
        $this->stripePriceId = $stripePriceId;

        return $this;
    }

    public function getPriceCents(): int
    {
        return $this->priceCents;
    }

    public function setPriceCents(int $priceCents): self
    {
        $this->priceCents = $priceCents;

        return $this;
    }

    public function getFormattedPrice(): string
    {
        return number_format($this->priceCents / 100, 2, ',', '.') . ' €';
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = strtolower($currency);

        return $this;
    }

    /**
     * @return array<int, array{key: string, label: string, path: string, filename: string, description?: string}>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param array<int, array{key: string, label: string, path: string, filename: string, description?: string}> $files
     */
    public function setFiles(array $files): self
    {
        $this->files = $files;

        return $this;
    }

    /**
     * @return array{key: string, label: string, path: string, filename: string, description?: string}|null
     */
    public function getFileByKey(string $key): ?array
    {
        foreach ($this->files as $file) {
            if (($file['key'] ?? null) === $key) {
                return $file;
            }
        }

        return null;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

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
}
