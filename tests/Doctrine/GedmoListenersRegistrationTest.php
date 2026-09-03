<?php

namespace App\Tests\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\SluggableListener;
use Gedmo\Timestampable\TimestampableListener;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The `doctrine.event_subscriber` tag was dropped in DoctrineBundle 3, which silently
 * unregistered both Gedmo listeners and made every Exam insert fail on a null `slug`.
 * These assertions fail if the registration in config/packages/doctrine_extensions.yaml
 * stops being honoured again.
 */
final class GedmoListenersRegistrationTest extends KernelTestCase
{
    /**
     * @return iterable<string,array{class-string,string}>
     */
    public static function listenerProvider(): iterable
    {
        foreach (['prePersist', 'onFlush', 'loadClassMetadata'] as $event) {
            yield "sluggable {$event}" => [SluggableListener::class, $event];
            yield "timestampable {$event}" => [TimestampableListener::class, $event];
        }
    }

    /**
     * @param class-string $listenerClass
     */
    #[DataProvider('listenerProvider')]
    public function testListenerIsRegisteredForEvent(string $listenerClass, string $event): void
    {
        self::bootKernel();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $registered = array_map(
            static fn (object $listener): string => $listener::class,
            array_values($entityManager->getEventManager()->getListeners($event)),
        );

        self::assertContains($listenerClass, $registered, \sprintf('%s must listen to %s.', $listenerClass, $event));
    }
}
