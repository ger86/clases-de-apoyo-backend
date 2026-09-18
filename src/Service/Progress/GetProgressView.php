<?php

namespace App\Service\Progress;

use App\Entity\User;
use App\Model\View\ProgressEntryView;
use App\Model\View\ProgressView;
use App\Repository\UserProgressRepository;

final class GetProgressView
{
    public function __construct(private UserProgressRepository $userProgressRepository)
    {
    }

    public function __invoke(User $user): ProgressView
    {
        $entries = [];
        foreach ($this->userProgressRepository->findByUser($user) as $progress) {
            $target = $progress->getTarget();
            $entries[] = new ProgressEntryView(
                $target->kind->value,
                $target->id,
                $progress->getDoneAt()->format(DATE_ATOM)
            );
        }

        return new ProgressView($entries);
    }
}
