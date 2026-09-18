<?php

namespace App\Model;

use InvalidArgumentException;

/**
 * A chapter or an exam, named the way the app stores it: "chapter:12" or "exam:7".
 * One value type keeps the two id spaces apart everywhere.
 */
final readonly class ProgressTarget
{
    public function __construct(
        public ProgressKind $kind,
        public int $id
    ) {
        if ($id <= 0) {
            throw new InvalidArgumentException('El identificador debe ser positivo.');
        }
    }

    public static function fromKey(string $key): self
    {
        $parts = explode(':', $key, 2);
        if (\count($parts) !== 2) {
            throw new InvalidArgumentException(\sprintf('Clave de progreso no válida: "%s".', $key));
        }

        [$kind, $id] = $parts;
        $progressKind = ProgressKind::tryFrom($kind);
        if ($progressKind === null || !ctype_digit($id)) {
            throw new InvalidArgumentException(\sprintf('Clave de progreso no válida: "%s".', $key));
        }

        return new self($progressKind, (int) $id);
    }

    public function key(): string
    {
        return \sprintf('%s:%d', $this->kind->value, $this->id);
    }
}
