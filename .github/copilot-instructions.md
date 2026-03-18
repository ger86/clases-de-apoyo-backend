# Clases de Apoyo Copilot Coding Instructions

## Project Overview

This project holds the codebase for Clases de Apoyo, a platform which stores documents of different subjects and grades like Math, Physics, Chemistry, etc. 

The content is served in two ways:
- A RESTful API for mobile applications
- A web made with Twig and Tailwind that is publicly accessible

## Technology Stack

- **Language**: PHP 8.4+ (currently running PHP 8.4.11)
- **Framework**: Symfony 7.3 
- **Database**: MySQL with Doctrine
- **Architecture**:
    - RESTful API with FOSRestBundle in src/Controller/Api
    - Controllers for the web part in src/Controller, exluding the Api folder.
    - Doctrine entities are stored in src/Entity
    - Admin backend with EasyAdmin in src/Admin
    - Business logic in src/Service
    - Stripe is used for payments through the package stripe/stripe-php.
- **Authentication**: 
    - The RESTful API doesn't require user authentication.
    - The web part uses Symfony Security with login forms and sessions.
- **File Storage**: AWS S3 via Flysystem
- **CI/CD**: 
    - Code style is done with ECS (Easy Coding Standard).
    - Static analysis with PHPStan (level 2, very strict)

## Build and Validation Commands

### Full Validation Pipeline

```bash
composer ci
```

The `composer ci` command executes the full validation pipeline:
- Code style fixes with ECS (Easy Coding Standard)
- Static analysis with PHPStan (level 8, very strict)
- Doctrine schema validation
- Container service validation  
- Twig template linting
- YAML configuration linting
- PHPUnit tests

### Individual Quality Checks

```bash
# Code style check and fix
composer cs                    # Check only
vendor/bin/ecs check --fix    # Check and auto-fix

# Static analysis
composer stan                  # Run PHPStan analysis

# Combined quality check
composer check                 # Runs stan-clear + ecs fix + stan

# Run tests (with deprecation warnings suppressed)
bin/phpunit           # Uses custom SYMFONY_DEPRECATIONS_HELPER config
```

### Symfony Console Commands

```bash
# Database operations
bin/console doctrine:schema:validate       # Validate schema
bin/console doctrine:migrations:migrate    # Run migrations

# Cache and validation
bin/console cache:clear --env=test        # Clear test cache
bin/console lint:container               # Validate service container
bin/console lint:twig templates          # Validate Twig templates  
bin/console lint:yaml config --parse-tags        # Validate YAML configs
bin/console lint:yaml translations --parse-tags  # Validate translations
```

## Configuration Files Reference

- **composer.json**: Dependencies, scripts, and PHP 8.4+ requirement
- **phpstan.neon**: Static analysis config (level 8, very strict)
- **ecs.php**: Code style rules (PSR-12 with custom rules)
- **phpunit.xml.dist**: Test configuration
- **symfony.lock**: Symfony Flex recipes lock file

## Code style & conventions
- ECS (PSR-12 + custom rules). Do not rely on editor formatting; run ECS. These are the rules declared in `ecs.php`:
```
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withSkip([
        __DIR__ . '/src/Migrations'
    ])
    ->withRules([
        NullableTypeDeclarationFixer::class,
        NoUnusedImportsFixer::class,
        NullableTypeDeclarationForDefaultNullValueFixer::class
    ])
    ->withConfiguredRule(
        ArraySyntaxFixer::class,
        [
            'syntax' => 'short',
        ]
    )
    ->withConfiguredRule(MethodArgumentSpaceFixer::class, [
        'on_multiline' => 'ensure_fully_multiline',
    ])
    ->withConfiguredRule(
        NativeFunctionInvocationFixer::class,
        [
            'scope' => 'namespaced',
            'include' => ['@compiler_optimized']
        ]
    );
```
- Always specify return types
- Always specify property types
- Use typed properties; no `@var` annotations for types
- Use constructor property promotion
- If possible, omit the caught variable in exceptions: `catch (SomeException) { ... }`
- By default, controllers doesn't contain business logic; use services in `src/Service/`. They should be thin and only handle request/response.
- Use dependency injection via constructor for services
- Use final classes unless you specifically need inheritance
- Use strict comparisons (`===`, `!==`)
- Use early returns to reduce nesting
- Use named arguments for methods with many parameters
- Use PHP 8 features like match expressions, nullsafe operator, attributes, etc.
- Group use statements by vendor and sort alphabetically

## Project Structure and Key Directories

```
/
├── migrations/                  # Database migration files
├── src/                          # Main application code
│   ├── Controller/Api/           # REST API controllers
│   ├── Controller/               # Web controllers
│   ├── Entity/                   # Doctrine entities
│   ├── Enum/                     # Enum types
|   ├── Event/                    # Events dispatched in the app
│   ├── EventSubscriber/          # Event subscribers
│   ├── Form/                     # Form types used in controllers
│   ├── Model/Dto                  # Data Transfer Objects
│   ├── Model/View               # View models for API Responses
│   ├── Service/                  # Business logic services  
│   ├── Repository/               # Data access layer
│   ├── Security/                 # Authentication/authorization
│   ├── TwigExtension/            # Twig extensions
│   ├── Admin/                    # EasyAdmin backend
│   └── Kernel.php               # Symfony kernel
├── tests/                        # PHPUnit tests (mirrors src/ structure)
├── config/                       # Symfony configuration
│   ├── packages/                 # Bundle configurations
│   ├── routes/                   # Routing configuration
│   ├── services.yaml            # Service definitions
│   └── services_{env}.yaml      # Environment-specific services
├── templates/                    # Twig templates
├── var/                         # Cache, logs, temporary files
├── vendor/                      # Composer dependencies
└── public/                      # Web root directory
```

## Architecture Patterns

- **Controllers**: Follow single-action controller pattern (`GetUsersController`, `PostUserController`)
- **Services**: Located in `src/Service/` with domain-driven design
- **REST API**: 
    - Uses FOSRestBundle for consistent API responses
    - The controllers return views located in `src/Model/View`

## Architecture rules

- All the database queries must be in the repository classes (in `src/Repository`).
- No DQL/QueryBuilder in services/controllers.
- Migrations policy: Use Doctrine Migrations only; forbid schema changes outside migration files; naming standard. Create migrations using the proper command.
    - Doctrine migrations are located in `./migrations`.
- If a controller returns an object, create a class in `src/Model/View` to represent the structure.

```
<?php

namespace App\Model\View;

readonly class ChapterView
{

    /**
     * @param FileView[] $files
     */
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public ?int $weight,
        public array $files
    ) {
    }
}

```


### PHPStan Level 2
The project uses static analysis. Common issues:
- Missing type declarations
- Uninitialized properties  
- Generic type specifications required
- Deprecation warnings from dependencies (ignored in config)

## Environment Variables

The application requires environment variables defined in `.env` and configured via Symfony's environment system. Key variables include database credentials, AWS S3 settings, Stripe keys, and email configuration.

## Trust These Instructions

Only search the codebase if:
- These instructions are incomplete for your specific task
- You need to understand specific business logic implementation details

