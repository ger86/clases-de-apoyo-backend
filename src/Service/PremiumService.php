<?php

namespace App\Service;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Entity\Exam;
use App\Entity\File;

class PremiumService
{

    public function __construct(private AuthorizationCheckerInterface $authChecker, private Security $security)
    {
    }

    public function isPremium(): bool
    {
        if ($this->authChecker->isGranted('ROLE_ADMIN')) {
            return true;
        }
        $user = $this->security->getUser();
        return $user !== null && $user->isPremium();
    }

    public function canSeeExam(Exam $exam): bool
    {
        if ($this->authChecker->isGranted('ROLE_ADMIN')) {
            return true;
        }
        $user = $this->security->getUser();
        return $exam->canSee($user);
    }

    public function canSeeChapterFile(File $file): bool
    {
        if ($this->authChecker->isGranted('ROLE_ADMIN')) {
            return true;
        }
        $user = $this->security->getUser();
        return $file->canSee($user);
    }
}
