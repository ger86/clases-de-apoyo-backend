<?php

namespace App\Model\View;

/**
 * One exam as the phone search sees it. No download link and no locked flag travel here: both
 * depend on the account, and this index is written once for everybody.
 */
final readonly class SearchIndexExamView
{

    public function __construct(
        public int $examId,
        public string $examName,
        public ?string $description,
        public string $knowledgeTestName,
        public int $communityId,
        public string $communityName,
        public int $communityTestCourseSubjectId,
        public string $subjectName,
        public int $testYearId,
        public string $testYear
    ) {
    }
}
