<?php

namespace App\Service\Shop;

use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Event\SendMailEvent;
use App\Model\Dto\MailDto;
use App\Repository\BookRepository;
use Exception;

class SendBookCommand
{

    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private Pool $pool,
        private BookRepository $bookRepository
    ) {
    }


    public function __invoke(int $bookId, string $email): void
    {
        $book = $this->bookRepository->findOneById($bookId);
        if ($book === null) {
            throw new Exception(\sprintf('Book with id: %d does not exist', $bookId));
        }
        $file = $book->getFile();
        $provider = $this->pool->getProvider($file->getProviderName());
        $format = $provider->getFormatName($file, 'reference');
        $url = $provider->generatePublicUrl($file, $format);

        $mailerDto = new MailDto(
            'email/download_book.html.twig',
            [
                'book' => $book,
                'url' => $url
            ],
            \sprintf('Descarga tu libro: %s', $book->getTitle()),
            $email
        );
        $event = new SendMailEvent($mailerDto);
        $this->eventDispatcher->dispatch($event);
    }
}
