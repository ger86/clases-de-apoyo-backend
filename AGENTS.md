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

### Paid PAU bundle products

When creating or updating a paid downloadable PAU bundle, follow the repeatable runbook:

```
docs/runbooks/create-pau-bundle.md
```

The historical record for the first bundle is:

```
docs/agent-records/2026-05-22-pau-madrid-math-pack.md
```

Do not expose production MySQL publicly, do not rely on ignored `var/` files for paid downloads, and verify the product row, Stripe price, and S3 files before advertising or enabling a new bundle.

### Production deployments

Agents must deploy production by connecting to the EC2 instance and running the deployment wrapper:

```
cd /var/www
./prepare_cda_coffe
```

Do not deploy production by manually running `git pull`, `composer install`, cache clears, service restarts, or asset builds inside `/var/www/clasesdeapoyo`. The wrapper is the project convention and prevents agents from tripping over direct-copy production changes or leaving deployment steps incomplete.

## Guidelines

### Run commands

The project contains a docker-compose setup for local development described in the .docker folder. When you need to run a command, you must follow these steps:

1. Make sure the php docker container is running. If not, navigate to the .docker folder and run `docker-compose up -d php`.
2. Run the command inside the php container using docker-compose exec. For example, to run migrations, use:
   ```bash
   docker-compose exec php composer ci
   ```
