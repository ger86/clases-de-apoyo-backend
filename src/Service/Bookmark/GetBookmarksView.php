<?php

namespace App\Service\Bookmark;

use App\Entity\User;
use App\Model\View\BookmarkView;
use App\Model\View\BookmarksView;
use App\Repository\UserBookmarkRepository;
use App\Service\FileSourceName;
use App\Service\PremiumService;

/**
 * The saved files of an account, for the app and the website alike. It carries no download
 * link: a signed link expires after 30 minutes, so the app asks for the file when it opens it.
 */
final class GetBookmarksView
{
    public function __construct(
        private UserBookmarkRepository $userBookmarkRepository,
        private PremiumService $premiumService,
        private FileSourceName $fileSourceName
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
                ($this->fileSourceName)($file),
                !$this->premiumService->canSeeExamFile($file),
                $bookmark->getBookmarkedAt()->format(DATE_ATOM)
            );
        }

        return new BookmarksView($bookmarks);
    }
}
