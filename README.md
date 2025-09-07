# Education Management System

A comprehensive Symfony 7.3 education management system designed for managing students, families, schools, classes, courses, grades, and financial operations in educational institutions.

## 📋 Table of Contents

- [Features](#-features)
- [Prerequisites](#-prerequisites)
- [Installation](#-installation)
- [Development Commands](#-development-commands)
- [Project Structure](#-project-structure)
- [Architecture](#-architecture)
- [Tech Stack](#-tech-stack)
- [Configuration](#-configuration)
- [Testing](#-testing)
- [Deployment](#-deployment)

## ✨ Features

### Education Management
- **Student Management**: Complete student profiles with academic history
- **Family Management**: Family relationships and contact information
- **School Administration**: Multi-school support with class organization
- **Course Management**: Course creation, scheduling, and assignments
- **Grade Tracking**: Comprehensive grade management and reporting
- **Academic Periods**: Support for academic years and periods

### Financial Operations
- **Package Management**: Educational packages and pricing
- **Payment Processing**: Student payment tracking and management
- **Financial Reporting**: Detailed financial reports and analytics
- **Account Management**: Student account balances and transactions

### Administrative Features
- **User Management**: Role-based access control
- **Document Management**: File uploads and document organization
- **QR Code Generation**: QR codes for various entities
- **Multi-language Support**: Internationalization ready
- **Email Notifications**: Automated email system

## 🔧 Prerequisites

- **PHP**: 8.2 or higher
- **Docker**: Latest version
- **Docker Compose**: Latest version
- **Make**: Build automation tool
- **Git**: Version control

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd education-legacy
```

### 2. Environment Setup
```bash
# Copy environment file
cp .env .env.local

# Edit environment variables as needed
nano .env.local
```

### 3. Start Development Environment
```bash
# Start all services
make up

# Create database and apply migrations
make create-database
make migrate

# Load sample data (optional)
make load-fixtures
```

### 4. Access the Application
- **Application**: `http://localhost:3900`
- **Admin Panel**: `http://localhost:3900/admin`
- **Database**: `localhost:8801` (MySQL)

## 🛠 Development Commands

### Environment Management
```bash
make up              # Start all containers
make down            # Stop all containers
make restart         # Complete restart (down + up)
make build-up        # Rebuild and start containers
make logs            # View all logs
make app-logs        # Application logs only
make app             # Access app container shell
```

### Database Operations
```bash
make create-database     # Create database
make migrate            # Run migrations
make migration          # Create new migration
make reset-database     # Reset with fixtures (⚠️ destroys data)
make load-fixtures      # Load fixtures only
make doctrine-validate  # Validate schema
```

### Testing
```bash
make test              # Run basic tests
make test-all          # Run all tests with fixtures
make test-report       # Generate coverage report
make test-load-fixtures # Load test fixtures

# Run specific test
docker compose exec -e APP_ENV=test app ./vendor/bin/simple-phpunit tests/Path/To/TestFile.php
```

### Code Quality & Analysis
```bash
make analyze    # Run all analysis tools
make stan       # PHPStan static analysis (level 5)
make cs-fix     # Fix code style with PHP CS Fixer
make cs-dry     # Dry run code style check
make rector     # Automated code refactoring
```

### Symfony Commands
```bash
make cc         # Clear cache
make warmup     # Warmup cache
make routes     # Debug routes
make purge      # Full cleanup (cache, logs, assets)
```

## 📁 Project Structure

```
education-legacy/
├── assets/                 # Frontend assets (JS, CSS)
├── config/                 # Symfony configuration
│   ├── packages/          # Bundle configurations
│   └── routes/            # Route definitions
├── docker/                # Docker configuration files
├── migrations/            # Database migrations
├── public/                # Web-accessible files
├── src/                   # Application source code
│   ├── Controller/        # HTTP controllers
│   │   └── Admin/         # EasyAdmin CRUD controllers
│   ├── Entity/            # Doctrine entities (28 entities)
│   ├── Form/              # Symfony forms
│   ├── Manager/           # Business logic layer
│   ├── Repository/        # Data access layer
│   ├── Trait/             # Reusable entity traits
│   ├── Checker/           # Validation logic
│   ├── Components/        # Symfony UX components
│   ├── DataFixtures/      # Sample data
│   ├── Event/             # Domain events
│   └── Services/          # Application services
├── templates/             # Twig templates (162 templates)
├── tests/                 # Test suite
├── translations/          # Multi-language support
└── var/                   # Cache and logs
```

## 🏗 Architecture

### Domain-Driven Design
The application follows DDD principles with clear separation of concerns:

#### Core Entities (28 total)
- **Academic**: `Student`, `Family`, `School`, `ClassSchool`, `ClassPeriod`, `Course`, `Grade`
- **Financial**: `Package`, `PaymentPackageStudent`, `Operation`, `Account`, `AccountSlip`
- **Administrative**: `User`, `Person`, `Member`, `Document`, `Period`
- **System**: `OperationGender`, `TypeOperation`, `Structure`, `Validate`

#### Layered Architecture
- **Controllers**: HTTP request handling, EasyAdmin integration
- **Managers**: Complex business logic and domain operations
- **Repositories**: Database queries with custom methods
- **Forms**: Data input validation and transformation
- **Traits**: Reusable entity behaviors (`IdEntityTrait`, `NameEntityTrait`, etc.)

#### Design Patterns
- **Repository Pattern**: All database access through repositories
- **Manager Pattern**: Business logic encapsulation
- **Trait Composition**: Shared entity properties
- **Event-Driven**: Domain events for decoupled operations

## 🔧 Tech Stack

### Backend
- **Framework**: Symfony 7.3 (migrated from 6.4)
- **PHP**: 8.2+ with modern features
- **Database**: MySQL 8.1 with Doctrine ORM
- **Server**: FrankenPHP with PHP 8.4 Alpine

### Frontend
- **Templates**: Twig templating engine
- **Assets**: Symfony AssetMapper
- **Components**: Symfony UX Live Components
- **Styling**: Bootstrap-based admin interface

### Development Tools
- **Admin Panel**: EasyAdmin 4.7+ for CRUD operations
- **Testing**: PHPUnit 9.5 with DAMA DoctrineTestBundle
- **Code Quality**: PHPStan (level 5), PHP CS Fixer, Rector
- **Container**: Docker with optimized multi-stage builds

### Key Libraries
- **QR Codes**: Endroid QR Code Bundle
- **File Processing**: OFX Parser for financial data
- **Monitoring**: Sentry integration
- **Routing**: FOSJsRouting for frontend routing
- **Extensions**: StofDoctrineExtensions, BeberleDoctrineExtensions

## ⚙️ Configuration

### Environment Variables
```bash
# Database
DATABASE_URL=mysql://app:db_password@database/education_app?charset=utf8mb4
DATABASE_NAME=education_app
DATABASE_USER=app
DATABASE_PASSWORD=db_password

# Application
APP_ENV=dev                 # dev, prod, test
APP_SECRET=your-secret-key
PROJECT_APP_PORT=3900       # Application port
PROJECT_DB_PORT=8801        # Database port

# Services
MAILER_DSN=smtp://localhost:1025
SENTRY_DSN=your-sentry-dsn
```

### Docker Services
- **app**: Symfony application (FrankenPHP + PHP 8.4)
  - Port: 3900 (configurable)
  - Volumes: Application code, uploads
- **database**: MySQL 8.1
  - Port: 8801 (configurable)
  - Volume: Persistent database storage

## 🧪 Testing

### Test Configuration
- **Framework**: PHPUnit 9.5 with Symfony Test Bundle
- **Database**: DAMA DoctrineTestBundle for transaction rollback
- **Fixtures**: Alice/Faker for test data generation
- **Coverage**: PHPUnit coverage reporting

### Running Tests
```bash
# All tests with fresh fixtures
make test-all

# Specific test categories
make test                    # Basic test suite
docker compose exec -e APP_ENV=test app ./vendor/bin/simple-phpunit --group unit
docker compose exec -e APP_ENV=test app ./vendor/bin/simple-phpunit --group integration
```

## 🚀 Deployment

### Production Environment
```bash
# Build production image
docker build --target prod -t education-app .

# Environment variables
APP_ENV=prod
DATABASE_URL=mysql://user:pass@host/database
```

### CI/CD Pipeline
The project includes GitLab CI configuration with:
- Automated testing
- Code quality checks
- Docker image building
- Deployment automation

### Monitoring
- **Error Tracking**: Sentry integration
- **Logging**: Monolog with structured logging
- **Performance**: Built-in Symfony profiler (dev only)

---

For detailed development guidelines and advanced configuration, see [CLAUDE.md](CLAUDE.md).