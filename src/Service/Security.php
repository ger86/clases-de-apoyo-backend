<?php

namespace App\Service;

use App\Entity\User;
use LogicException;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundleSecurity;

class Security
{
    public function __construct(private SecurityBundleSecurity $security)
    {
    }

    public function getUser(): ?User
    {
        $user = $this->security->getUser();
        if ($user === null) {
            return null;
        }
        if (!$user instanceof User) {
            throw new LogicException('Current user must be an instance of User entity');
        }
        return $user;
    }

    public function getSafeUser(): User
    {
        $user = $this->getUser();
        if ($user === null) {
            throw new LogicException('Current user is null');
        }
        return $user;
    }
}
