# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Symfony 7.3 education management system built with PHP 8.4. It's a legacy application that manages students, families, schools, classes, courses, grades, and financial operations.

### Technology Stack

**Backend:**

- Symfony 7.3 framework with PHP 8.4
- Doctrine ORM with MySQL 8.1 database
- EasyAdmin 4 for administrative interfaces
- PHPUnit for testing with Alice/Faker fixtures

**Frontend:**

- TailwindCSS (CDN) for styling and responsive design
- Alpine.js for interactive JavaScript functionality
- Twig Components for reusable UI components (avoiding code duplication)
- Symfony Live Components for real-time database interactions without page reloads

**DevOps & Environment:**

- Docker with Frankenphp (PHP 8.4)
- MySQL 8.1 container
- Automated code quality with Husky + lint-staged hooks

## Quick Start

**First-time setup:**

```bash
make up                    # Start containers
make create-database       # Setup database
make migrate              # Run migrations
make load-fixtures        # Load sample data
```

**Daily development:**

```bash
make up                   # Start development
make cc                   # Clear cache when needed
make analyze             # Check code quality before commit
```

## Development Commands

### Docker Environment

```bash
# Start the development environment
make up
# or
docker compose up -d

# Stop containers
make down

# Rebuild and start containers
make build-up

# View logs
make logs
make app-logs  # Application logs only

# Access app container shell
make app
# or
docker compose exec app zsh

# Complete restart
make restart
```

### Database Management

```bash
# Create database and run migrations
make create-database
make migrate

# Reset database with fixtures (CAUTION: deletes all data)
make reset-database

# Create a new migration
make migration

# Load fixtures only
make load-fixtures

# Validate doctrine schema
make doctrine-validate
```

### Testing & Quality Assurance

```bash
# Run tests
make test              # Basic tests
make test-all          # All tests with fixtures
make test-report       # Generate coverage report

# Load test fixtures
make test-load-fixtures

# Run a single test
docker compose exec -e APP_ENV=test app ./vendor/bin/simple-phpunit tests/Path/To/TestFile.php

# Code quality (run before committing)
make analyze           # Run all analysis tools
make stan             # PHPStan static analysis
make cs-fix           # PHP CS Fixer (fix code style)
make cs-dry           # PHP CS Fixer (dry run)
make rector           # Rector (code refactoring)
```

### Git Hooks & Automation

The project uses Husky + lint-staged for automated code quality checks:

```bash
# Install git hooks
make hooks-install

# Test lint-staged configuration
make hooks-test

# Show hooks status and configuration
make hooks-status
```

**Pre-commit hooks automatically:**

- Check PHP syntax (`php -l`)
- Fix code style with PHP CS Fixer
- Run PHPStan static analysis
- Validate composer.json files

**Pre-push hooks:**

- Run the full test suite (`make test-all`)
- Prevent push if tests fail

### Symfony Commands

```bash
# Clear cache
make cc

# Warmup cache
make warmup

# Debug routes
make routes

# Full purge (cache, logs, assets, permissions)
make purge
```

## Architecture

### Core Structure

The application follows Symfony best practices with Domain-Driven Design principles:

**Entities** (`src/Entity/`): Core domain models representing business objects

- `Student`, `Family`, `School`, `ClassSchool`, `ClassPeriod` - Education management
- `Package`, `PaymentPackageStudent`, `Operation` - Financial management
- `User`, `Person`, `Member` - User management
- `Period` - Academic periods/years management

**Repositories** (`src/Repository/`): Data access layer using Doctrine ORM

- Each entity has a corresponding repository for database queries
- Custom query methods extend from Doctrine's ServiceEntityRepository

**Controllers** (`src/Controller/`):

- `Admin/` - EasyAdmin CRUD controllers for administrative interfaces
- `SecurityController` - Authentication handling

**Managers** (`src/Manager/`): Business logic layer

- Interfaces define contracts for complex operations
- Implementations handle domain-specific business rules

**Forms** (`src/Form/`): Symfony forms for data input/validation

- Custom form types for specific fields (DatePicker, TimePicker, etc.)

**Traits** (`src/Trait/`): Reusable entity properties and behaviors

- `IdEntityTrait`, `NameEntityTrait`, `EmailEntityTrait` - Common fields
- `SchoolEntityTrait`, `StudentEntityTrait` - Domain-specific relationships

### Key Patterns

1. **EasyAdmin Integration**: Admin panel uses EasyAdmin 4 for CRUD operations
   - Dashboard controller at `src/Controller/Admin/DashboardController.php`
   - CRUD controllers for each entity

2. **Trait-based Entity Composition**: Entities use traits for common properties to avoid duplication

3. **Manager Pattern**: Complex business logic is encapsulated in manager services

4. **Repository Pattern**: All database queries go through repositories

5. **Frontend Component System**:
   - **Twig Components**: Reusable UI components to avoid template duplication
   - **Live Components**: Real-time interactions with database without page reloads
   - **TailwindCSS + Alpine.js**: Styling and interactive functionality

## Database Configuration

The application uses MySQL 8.1 with the following environment variables:

```bash
DATABASE_NAME=education_app
DATABASE_USER=app
DATABASE_PASSWORD=db_password
DATABASE_URL=mysql://app:db_password@database/education_app?charset=utf8mb4
```

## Docker Services

**app**: Symfony application (Frankenphp with PHP 8.4)

- Port: 3900 (configurable via PROJECT_APP_PORT)
- Volumes: `./:/app` (dev), `./public/uploads:/app/public/uploads` (prod)

**database**: MySQL 8.1

- Port: 8801 (configurable via PROJECT_DB_PORT)
- Volume: `./var/db:/var/lib/mysql`

## Testing Configuration

- PHPUnit 9.5 with Symfony test bundle
- DAMA DoctrineTestBundle for database transaction rollback
- Test environment uses separate database configuration
- Fixtures loaded via Alice/Faker

## Code Standards

- **PHP CS Fixer**: @PhpCsFixer, @PSR2, PHP 8.0+ migration rules
- **PHPStan**: Level 5 analysis with Symfony/Doctrine extensions
- **Rector**: Automated refactoring with Symfony/Doctrine presets

## Development Workflow

### Before Committing
1. Run `make analyze` to check code quality
2. Ensure tests pass with `make test-all`
3. Git hooks will automatically run quality checks

### After Pulling Changes
1. Run `make migrate` to apply database migrations
2. Clear cache with `make cc` if needed
3. Update dependencies if composer.lock changed

### Frontend Development
- Use TailwindCSS classes for styling (CDN-based)
- Add interactivity with Alpine.js directives
- Create reusable Twig Components for UI elements
- Use Live Components for dynamic database interactions

## Important Notes

1. Always run code quality checks before committing: `make analyze`
2. The application requires PHP 8.4 and uses Symfony 7.3
3. Database migrations must be run after pulling changes: `make migrate`
4. Test database is automatically reset for each test run with DAMA bundle
5. The project uses Symfony Flex for dependency management
6. Frontend uses TailwindCSS (CDN) + Alpine.js for styling and interactivity
7. Twig Components prevent template duplication, Live Components handle real-time updates