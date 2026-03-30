# Products API

## Overview

This is a Symfony-based microservice for managing products, implementing a CQRS (Command Query Responsibility
Segregation) architecture. It provides RESTful API endpoints for product operations.

## Key Points

- **Framework**: Symfony 8.x and PHP 8.x
- **Architecture**: CQRS with separate command and query handlers
- **Persistence**: Doctrine ORM for database interactions
- **API Documentation**: Integrated with NelmioApiDocBundle for OpenAPI specs
- **Testing**: Includes PHPUnit for unit and web tests
- **Async Processing**: Uses Symfony Messenger for background tasks
- **Validation**: Symfony Validator for input validation
- **Serialization**: Custom serializers for API responses

## Getting Started

Follow the setup instructions in
the [Orchestrator MS API repository](https://github.com/BonBonSlick/2026_test_task_orchestrator_ms_api).
