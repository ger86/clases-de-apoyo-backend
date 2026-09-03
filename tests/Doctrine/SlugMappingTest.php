<?php

namespace App\Tests\Doctrine;

use Gedmo\Mapping\Annotation\Slug;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

/**
 * Public URLs are built from these slugs and nothing redirects an old slug to a new one,
 * so a slug must never move on its own. It only changes when an editor clears the slug
 * field, which reaches Gedmo as null and asks it to regenerate from the source field.
 */
final class SlugMappingTest extends TestCase
{
    /**
     * @return iterable<string,array{ReflectionProperty}>
     */
    public static function sluggedPropertyProvider(): iterable
    {
        $entityDir = \dirname(__DIR__, 2) . '/src/Entity';
        $found = 0;

        foreach (glob($entityDir . '/*.php') ?: [] as $file) {
            $class = 'App\\Entity\\' . basename($file, '.php');
            foreach ((new ReflectionClass($class))->getProperties() as $property) {
                if ([] === $property->getAttributes(Slug::class)) {
                    continue;
                }

                ++$found;
                yield "{$class}::\${$property->getName()}" => [$property];
            }
        }

        self::assertGreaterThan(0, $found, 'No slugged properties found, the provider is looking in the wrong place.');
    }

    #[DataProvider('sluggedPropertyProvider')]
    public function testSlugIsNotUpdatable(ReflectionProperty $property): void
    {
        $slug = $property->getAttributes(Slug::class)[0]->newInstance();

        self::assertFalse($slug->updatable, 'A slug that follows its source field silently changes a live public URL.');
    }

    #[DataProvider('sluggedPropertyProvider')]
    public function testEmptySlugIsStoredAsNullSoGedmoRegeneratesIt(ReflectionProperty $property): void
    {
        $entity = (new ReflectionClass($property->getDeclaringClass()->getName()))->newInstanceWithoutConstructor();
        $setter = 'set' . ucfirst($property->getName());

        $entity->{$setter}('');
        self::assertNull($property->getValue($entity), 'An empty slug must become null, which is what makes Gedmo regenerate it.');

        $entity->{$setter}('kept-as-is');
        self::assertSame('kept-as-is', $property->getValue($entity));
    }
}
