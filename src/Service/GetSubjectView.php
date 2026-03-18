<?php

namespace App\Service;

use App\Entity\Subject;
use App\Model\View\SubjectView;

class GetSubjectView
{
    public function __invoke(Subject $subject): SubjectView
    {
        return new SubjectView(
            $subject->getId(),
            $subject->getName()
        );
    }
}
