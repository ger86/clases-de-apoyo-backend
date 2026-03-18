# Clases de Apoyo

## Development setup

This project includes a complete Docker environment for local development located in the `.docker` folder.

### Docker Environment Structure

The development environment consists of three main services:

- **php**: PHP 8.4-FPM container with all necessary extensions and tools
- **nginx**: Web server (Nginx 1.25.2) configured to serve the Symfony application
- **db**: MySQL 8.0.35 database server

### Quick Start

1. Navigate to the `.docker` folder:
   ```bash
   cd .docker
   ```

2. Start all services:
   ```bash
   docker-compose up -d
   ```

3. Access the application at `http://localhost:8080`

4. To run commands inside the PHP container (recommended for all Symfony commands):
   ```bash
   docker-compose exec php composer install
   docker-compose exec php bin/console cache:clear
   docker-compose exec php composer ci
   ```

### Service Details

#### PHP Container (`php`)
- **Base Image**: PHP 8.4-FPM on Debian Bullseye
- **Pre-installed Tools**: 
  - Composer (latest)
  - Node.js 14.x with Yarn
  - Git, unzip, and other development tools
- **PHP Extensions**: 
  - Database: `pdo`, `pdo_mysql`
  - Graphics: `gd`, `exif`
  - Performance: `opcache`, `apcu`
  - Utilities: `zip`, `xsl`, `intl`, `mbstring`
- **Working Directory**: `/var/www/symfony`
- **Volumes**: Project files mounted with cached performance mode
- **Timezone**: Europe/Madrid (configurable via `.env`)

#### Nginx Container (`nginx`)
- **Base Image**: Nginx 1.25.2 Alpine
- **Port**: 8080 (mapped to host port 8080)
- **Configuration**: Custom upstream to PHP-FPM container
- **Template System**: Uses environment variables for domain configuration

#### Database Container (`db`)
- **Image**: MySQL 8.0.35
- **Port**: 3307 (exposed to host)
- **Authentication**: MySQL native password
- **Default Credentials** (from `.docker/.env`):
  - Root password: `root`
  - Database: `clasesdeapoyo`
  - User: `db_user`
  - Password: `hola1234`
- **Persistent Storage**: Data persisted in `db_app` Docker volume

### Environment Configuration

The Docker environment uses several configuration files:

- **`.docker/.env`**: Main environment variables (database credentials, timezone)
- **`.docker/.env.nginx.local`**: Nginx-specific configuration
- **`.docker/php/php.ini`**: Custom PHP configuration
- **`.docker/nginx/nginx.conf`**: Nginx server configuration

### Persistent Volumes

The setup uses Docker volumes for optimal performance and data persistence:

- `clases_de_apoyo_var`: Symfony var directory (cache, logs)
- `clases_de_apoyo_vendor`: Composer vendor directory
- `db_app`: MySQL data directory

### Development Workflow

1. **Start the environment**:
   ```bash
   cd .docker && docker-compose up -d
   ```

2. **Install dependencies**:
   ```bash
   docker-compose exec php composer install
   docker-compose exec php yarn install
   ```

3. **Run database migrations**:
   ```bash
   docker-compose exec php bin/console doctrine:migrations:migrate
   ```

4. **Build assets**:
   ```bash
   docker-compose exec php yarn build
   ```

5. **Run quality checks**:
   ```bash
   docker-compose exec php composer ci
   ```

6. **Stop the environment**:
   ```bash
   docker-compose down
   ```

### Troubleshooting

- **Permission issues**: The PHP container runs as root. If you encounter permission issues, ensure the project files are accessible.
- **Port conflicts**: If port 8080 or 3307 are in use, modify the port mappings in `docker-compose.yml`.
- **Performance on macOS**: The setup uses cached volume mounts for better performance on macOS.


## How to test Stripe webhooks

1. Install ngrok

2. Run ngrock locally and configure webhooks in Stripe and Paypal to use the url provided

ngrok http -host-header=rewrite dev.clasesdeapoyo.com:8888

Credit card: 4000 0072 4000 0007
Other credit card: 4000056655665556
