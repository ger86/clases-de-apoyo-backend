<?php

namespace App\Service;

use App\Entity\TestYear;
use App\Model\View\TestYearTeaserView;

class GetTestYearTeaserView
{

    public function __invoke(TestYear $testYear): TestYearTeaserView
    {
        return new TestYearTeaserView(
            $testYear->getId(),
            $testYear->getYear(),
            $testYear->getExamsIds()
        );
    }
}
