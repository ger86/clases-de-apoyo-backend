<?php

namespace App\Service;

use App\Entity\File;

/**
 * The full name of the chapter or exam a file belongs to, such as
 * "4º E.S.O Matemáticas Números Reales". File names repeat ("Apuntes", "Enunciados"), so
 * the app and the saved files list show this under the name. One place builds it, so a
 * guest's bookmark and an account's bookmark read the same.
 */
final class FileSourceName
{
    public function __invoke(File $file): ?string
    {
        $exam = $file->getExam();
        if ($exam !== null) {
            // The exam name already starts with the year ("2018 Julio"), and so does the
            // string of its test year, so the test year itself is skipped here.
            $subject = $exam->getTestYear()->getCommunityTestCourseSubject();

            return $subject === null ? $exam->getName() : \sprintf('%s %s', $subject, $exam->getName());
        }

        return $file->getChapter()?->asString();
    }
}
