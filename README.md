# 🛒 Cart & Checkout API

A personal backend project implementing a REST API for shopping cart management and checkout, built with **PHP 8.4, Symfony 7.4, Doctrine ORM, and SQLite**, following **Domain-Driven Design (DDD), Hexagonal Architecture, and CQRS** principles.

The project demonstrates backend architecture, domain modeling, transactional consistency, persistence, and automated testing, with a focus on clean, maintainable, and testable code.

## Key Features

- Shopping cart creation and management through product operations.
- Product quantity updates and removal.
- Cart retrieval and listing.
- Checkout with persistent order creation.
- Sequentially idempotent checkout operations.
- Transactional order persistence and cart deletion.
- Domain events for cart modifications and order creation.
- Explicit handling of invalid requests and checkout conflicts.
- Automated unit, integration, and functional tests.

## Architecture

The application separates business logic from infrastructure concerns through three primary layers.

### Domain

Contains aggregates, entities, value objects, business rules, and domain events.

Main concepts include:

- `Cart` and `CartItem`
- `Order` and `OrderItem`
- `Product`
- `Money`, `Currency`, and `Quantity`

The domain layer does not depend on Symfony or Doctrine.

### Application

Implements use cases through CQRS:

- **Commands:** modify cart state and execute checkout.
- **Queries:** retrieve carts and orders.
- **Handlers:** coordinate domain operations and persistence.
- **Response DTOs:** expose application results without returning persistence entities.

### Infrastructure

Provides technical implementations using Symfony and Doctrine:

- HTTP controllers.
- Doctrine entities and repositories.
- Transaction management.
- Domain event dispatching.
- SQLite persistence.

## Domain Events

The application defines the following events:

- `ProductAddedToCart`
- `ProductQuantityUpdatedInCart`
- `ProductRemovedFromCart`
- `OrderCreated`

Events are dispatched after the corresponding persistence operations succeed.

Event delivery is synchronous and does not implement a transactional outbox or guaranteed delivery mechanism.

## API Endpoints

| Method | Endpoint                       | Description             |
| ------ | ------------------------------ | ----------------------- |
| GET    | `/cart/{id}`                   | Retrieve a cart         |
| GET    | `/carts`                       | List carts              |
| POST   | `/cart/{id}/product`           | Add a product           |
| PATCH  | `/cart/{id}/product/{product}` | Update product quantity |
| DELETE | `/cart/{id}/product/{product}` | Remove a product        |
| DELETE | `/cart/{id}`                   | Clear a cart            |
| POST   | `/checkout/{cartId}`           | Checkout a cart         |
| GET    | `/orders`                      | List orders             |

Product prices are represented as integer minor currency units (for example, cents).

Supported currencies are EUR, USD, and GBP.

## Checkout Consistency

Checkout uses Doctrine transaction management to persist an order and remove its associated cart within the same database transaction.

The implementation provides:

- **Sequential idempotency:** repeated checkout requests for an already processed cart return the existing order.
- **Transactional persistence:** order creation and cart deletion are performed within a transaction.
- **Concurrent request handling:** recognized database uniqueness conflicts produce HTTP `409 Conflict`, while recognized lock timeouts produce HTTP `503 Service Unavailable`.

Concurrent behavior depends on database locking and constraints. The implementation does not claim universal exactly-once processing.

## Running Locally

### Requirements

- Docker
- Docker Compose

### Installation

```bash
git clone https://github.com/JamDesigns/cart-api.git
cd cart-api
docker compose up -d --build
```

Apply database migrations:

```bash
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

The API is available at:

`http://localhost:8000`

No production credentials are required. The committed environment files contain development and testing defaults only.

## Automated Tests

The project includes PHPUnit tests covering domain behavior, application handlers, Doctrine persistence, and HTTP endpoints.

Prepare the isolated test database:

```bash
docker compose exec app composer run db:test:migrate
```

Validate the Doctrine schema:

```bash
docker compose exec app composer run db:test:validate
```

Run the complete test suite:

```bash
docker compose exec app php bin/phpunit
```

Latest verified local result:

**30 tests passed, 119 assertions.**

The test database is separated from the development database.

### Continuous Integration

GitHub Actions is configured to run on Pull Requests targeting `master`.

The workflow installs dependencies, applies test database migrations, validates the Doctrine schema, and executes PHPUnit.

## Postman Collection

The repository includes a Postman collection and a local environment:

- `postman/cart-api.postman_collection.json`
- `postman/environments/local.postman_environment.json`

The collection is intended for manual API exploration and validation.

## Technology Stack

- PHP 8.4
- Symfony 7.4
- Doctrine ORM
- SQLite
- PHPUnit 12
- Docker and Docker Compose
- GitHub Actions
- DDD, Hexagonal Architecture, CQRS

## Project Scope

This repository is a technical demonstration rather than a production e-commerce platform.

It focuses on shopping cart operations, checkout consistency, domain modeling, and testable backend architecture.

Features such as authentication, payment processing, inventory management, and distributed event delivery are outside its current scope.

## Author

José Ángel Mosquera Rodríguez  
Senior Full Stack Developer

[LinkedIn](https://www.linkedin.com/in/joseamosquera/) · [GitHub](https://github.com/JamDesigns/)
