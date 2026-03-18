# AGENTS.md

## Workflow

### Doctrine migrations

When you create, modify or remove an entity, you must create a new migration file using the following command:

```
bin/console doctrine:migrations:diff
```

Then, review the generated migration file in the `src/Migrations` directory to ensure it accurately reflects the intended changes.

Finally, apply the migration to your local database with:

```
bin/console doctrine:migrations:migrate
```

### Quality gates

Run the following command before opening a PR o finishing a task:

```
composer ci
```

This command runs all quality checks, including code style, static analysis, schema validation, container validation, Twig and YAML linting, and PHPUnit tests. It is mandatory to ensure that this command does not produce any errors before submitting a pull request.

## Guidelines

### Run commands

The project contains a docker-compose setup for local development described in the .docker folder. When you need to run a command, you must follow these steps:

1. Make sure the php docker container is running. If not, navigate to the .docker folder and run `docker-compose up -d php`.
2. Run the command inside the php container using docker-compose exec. For example, to run migrations, use:
   ```bash
   docker-compose exec php composer ci
   ```


