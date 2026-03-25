<?php

namespace App\Entity;

use App\Repository\DiscountCodeRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiscountCodeRepository::class)]
class DiscountCode
{

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 256)]
    private string $code = '';

    #[ORM\Column(type: 'string', length: 256)]
    private string $price = '';

    #[ORM\Column(type: 'string', length: 256)]
    private string $stripePlanId = '';

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $validUntil = null;

    public function __toString()
    {
        return $this->code === null  ? 'Nuevo código' : $this->code;
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

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getValidUntil(): ?DateTimeImmutable
    {
        return $this->validUntil;
    }

    public function setValidUntil(?DateTimeInterface $validUntil): self
    {
        $this->validUntil = $validUntil instanceof DateTimeImmutable || $validUntil === null
            ? $validUntil
            : DateTimeImmutable::createFromMutable($validUntil);

        return $this;
    }

    public function getStripePlanId(): string
    {
        return $this->stripePlanId;
    }

    public function setStripePlanId(string $stripePlanId)
    {
        $this->stripePlanId = $stripePlanId;

        return $this;
    }
}
