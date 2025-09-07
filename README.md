# Education Management System

A Symfony 7.3 education management system for students, families, schools, classes, courses, grades, and financial operations.

## Prerequisites

- PHP 8.2+
- Docker & Docker Compose
- Make

## Quick Start

```bash
# Start development environment
make up

# Create database and run migrations
make create-database
make migrate

# Load sample data
make load-fixtures
```

Access the application at `http://localhost:3900`

## Development Commands

### Environment
```bash
make up          # Start containers
make down        # Stop containers
make restart     # Complete restart
make logs        # View all logs
make app         # Access app container shell
```

### Database
```bash
make migrate           # Run migrations
make reset-database    # Reset with fixtures (⚠️ destroys data)
make doctrine-validate # Validate schema
```

### Testing
```bash
make test              # Run tests
make test-all          # Tests with fixtures
make test-report       # Coverage report
```

### Code Quality
```bash
make analyze    # Run all analysis
make stan       # PHPStan analysis
make cs-fix     # Fix code style
make rector     # Code refactoring
```

## Architecture

- **Entities**: Core business models (`Student`, `Family`, `School`, `Package`, etc.)
- **Controllers**: EasyAdmin CRUD controllers + custom controllers
- **Managers**: Business logic layer
- **Repositories**: Data access with custom queries
- **Traits**: Reusable entity properties

## Tech Stack

- **Framework**: Symfony 7.3
- **PHP**: 8.2+
- **Database**: MySQL 8.1
- **Admin Panel**: EasyAdmin 4
- **Testing**: PHPUnit with DAMA DoctrineTestBundle
- **Code Quality**: PHPStan, PHP CS Fixer, Rector

## Configuration

Key environment variables:
```bash
DATABASE_URL=mysql://app:db_password@database/education_app
PROJECT_APP_PORT=3900    # Application port
PROJECT_DB_PORT=8801     # Database port
```