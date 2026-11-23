<?php

namespace App\Service\Bookmark;

use App\Entity\File;
use App\Entity\User;
use App\Model\View\BookmarkView;
use App\Model\View\BookmarksView;
use App\Repository\UserBookmarkRepository;
use App\Service\PremiumService;

/**
 * The saved files of an account, for the app and the website alike. It carries no download
 * link: a signed link expires after 30 minutes, so the app asks for the file when it opens it.
 */
final class GetBookmarksView
{
    public function __construct(
        private UserBookmarkRepository $userBookmarkRepository,
        private PremiumService $premiumService
    ) {
    }

    public function __invoke(User $user): BookmarksView
    {
        $bookmarks = [];
        foreach ($this->userBookmarkRepository->findByUser($user) as $bookmark) {
            $file = $bookmark->getFile();
            $bookmarks[] = new BookmarkView(
                (int) $file->getId(),
                $file->getName() ?? '',
                $this->sourceOf($file),
                !$this->premiumService->canSeeExamFile($file),
                $bookmark->getBookmarkedAt()->format(DATE_ATOM)
            );
        }

        return new BookmarksView($bookmarks);
    }

    /** File names repeat ("Apuntes", "Enunciados"), so the list also names the chapter or exam. */
    private function sourceOf(File $file): ?string
    {
        $exam = $file->getExam();
        if ($exam !== null) {
            return (string) $exam;
        }

        return $file->getChapter()?->asString();
    }
}
