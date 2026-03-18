<?php

namespace App\Model\View;

readonly class KnowledgeTestView
{

    /**
     * @param CommunityTestView[] $communityTests
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public array $communityTests
    ) {
    }
}
