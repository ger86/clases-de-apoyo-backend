# Gedmo Listeners Fix and Slug URL Policy

Date: 2026-09-03
Project: Clases de Apoyo backend
Status: implemented, deployed to production, and committed
Commits: `ea48098` (listener fix), `f4028d1` (slug policy)

## Symptom

Creating an exam in Sonata Admin failed with "Se ha producido un error durante la creacion del elemento". The underlying database error was `Column 'slug' cannot be null`.

## Root cause

DoctrineBundle 3 removed support for the `doctrine.event_subscriber` service tag. The tag is no longer read anywhere in the bundle, so a service that uses it is silently never registered. No error, no deprecation.

`config/packages/doctrine_extensions.yaml` registered both Gedmo listeners with that dead tag. So `SluggableListener` and `TimestampableListener` were never attached to the entity manager. Two consequences:

- Slugs were never generated. Any entity with a NOT NULL slug column could not be created at all.
- `updatedAt` was frozen at creation time for every timestampable entity, which fed stale values into the sitemap `lastmod` and the JSON-LD `dateModified`.

The fix is one `doctrine.event_listener` tag per event, taken from each listener's `getSubscribedEvents()`: `prePersist`, `onFlush`, `loadClassMetadata`.

Regression test: `tests/Doctrine/GedmoListenersRegistrationTest.php` asserts both classes are attached for all three events. It was proven red against the old configuration.

While making kernel tests bootable, a stale alias for the non-existent `App\Service\Menu\MenuBroker` was removed from `config/services_test.yaml`.

Data audit after deploying: 0 rows with a null or empty slug in any of the 11 slugged tables, and 0 exam rows created on the day of the failure.

## Slug URL policy

Bringing the sluggable listener back to life exposed a second problem. The Gedmo default is `updatable: true`, so 9 of the 11 slugged entities would regenerate their slug whenever the source title changed. There is no redirect mechanism anywhere in the application (the only such code is commented out in `src/Controller/BookShopController.php`), so a title edit would have silently moved a live public URL and left the old one dead.

Decisions:

- All 11 `Gedmo\Slug` attributes now declare `updatable: false`. Editing a title never moves a public URL.
- The deliberate way to change a slug is to clear the slug field in the admin. `setSlug()` converts an empty string to null, which is Gedmo's "regenerate from the source field" marker, and the listener refills it on flush. This is what the admin help text "Dejar en blanco para que se genere automaticamente" already promised. That help text and `required: false` were added to `ArticleAdmin`, `BookAdmin` and `ChapterAdmin`, which were missing them.
- The slug columns keep their existing nullability. NOT NULL is left in place on the 9 columns that have it, because it is the guard that made this bug fail loudly instead of silently. A narrow, commented `doctrine.columnType` ignore in `phpstan.neon` covers the resulting property/column mismatch. No migration is needed.

Do not set `updatable: true` on a slug to "fix" a stale URL. That breaks every existing link to the row. Clear the slug field in the admin instead, and accept that the old URL dies.

Regression test: `tests/Doctrine/SlugMappingTest.php` reflects over `src/Entity/*.php` and, for every property carrying `Gedmo\Slug`, asserts `updatable` is false and that the setter maps an empty string to null while preserving a real value. It was proven red by temporarily flipping one entity back.

## Verification

Behaviour was checked against the production database after deploying, inside a transaction that was rolled back, so no data was mutated:

```
sluggable alive: yes
timestampable alive: yes

1) create, slug box blank      -> 'curso-de-prueba-zz-borrar'
2) title edited, slug kept     -> '4o-eso' -> '4o-eso'
3) title edited, slug cleared  -> '4o-eso' -> '4o-e-s-o-zz-yy'
4) slug cleared, title kept    -> 'curso-de-prueba-zz-borrar' -> 'curso-de-prueba-zz-borrar'
5) exam create                 -> '2025-julio-extraordinaria-2'

updatedAt moved on edit: yes
```

Case 4 is the important one to read correctly. The title was not touched, so regeneration produced the same value the row already had. The slug did not stay null, which is what the old code did and what made `getSlug()` throw a TypeError. In `SluggableListener::generateSlug` the skip for a non-updatable slug only applies when the slug field is absent from the Doctrine changeset, so clearing the field always reaches the generator, and a cleared slug marks the entity as needing a new one even when no source field changed.

Case 3 also shows that a hand written slug does not always match what Gedmo would generate. `4o Eso` was stored by hand against the name `4o E.S.O.`, so clearing that field replaced it with `4o-e-s-o-zz-yy`. Clearing a slug is a deliberate URL change, not a refresh.

## Notes for future agents

- If a Gedmo or Doctrine behaviour appears dead after a bundle upgrade, check the service tag first with `bin/console debug:container --tag=doctrine.event_listener`. An empty result for a configured listener means the tag is being ignored.
- The local Symfony dev cache goes stale after swapping listener configuration. Run `bin/console cache:clear` before trusting any behavioural test, and assert the listener is attached in-process before reporting a result.
- `phpunit.xml.dist` declares `APP_ENV` and `DATABASE_URL` twice. PHPUnit does not overwrite an environment variable that is already set unless the entry says `force="true"`, so the first declaration wins: the env is `test` and the database is `mysql://root:root@127.0.0.1:8889/clasesdeapoyo`, a MAMP address that does not exist inside the container. Tests therefore cannot reach a real database, which is why both regression tests are attribute and container based rather than functional.
