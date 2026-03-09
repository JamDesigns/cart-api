# 🛒 Cart & Checkout API

A backend API implementing a shopping cart and checkout system using Domain-Driven Design (DDD) principles.

The project demonstrates a decoupled domain model, CQRS command handlers and domain events while keeping the business logic independent from the Symfony framework.

It was developed as part of a senior backend code challenge.

## 📦 Features

- Add, update, and remove products in the cart.
- Retrieve cart contents.
- Checkout and generate persistent orders.
- Full domain decoupling from the Symfony framework.
- Domain events for all key actions.

## 📑 OpenAPI Specification

- Although not provided in YAML format, the collection is exported via Postman and included in the repository:

🗂️ postman/SirokoCartAPI.postman_collection.json
🌍 Uses the environment variable: base_url → http://127.0.0.1:8000

### 🔧 Endpoints - Cart

- POST /cart/{cartId}/items – Add item to cart
- PATCH /cart/{cartId}/items/{productId} – Update item quantity
- DELETE /cart/{cartId}/items/{productId} – Remove item
- GET /cart/{cartId} – View cart contents
- GET /carts – View all carts (for debugging)
- DELETE /cart/{cartId} – Empty entire cart

### 🔧 Endpoints - Checkout

- POST /checkout/{cartId} – Finalize cart and generate order
- GET /orders – View all generated orders

## 🧱 Domain Model

- Cart → Root aggregate holding CartItems
- CartItem → Line in cart, containing product + qty + price
- Order → Immutable order after checkout
- Value Objects: Money, Currency, Quantity, Product

## Domain Events

- ProductAddedToCart
- ProductQuantityUpdatedInCart
- ProductRemovedFromCart
- CartEmptied
- OrderCreated
- These events are dispatched internally using a custom EventBus (prints messages to the console).

## 🧪 Testing

- Unit tests: Value objects and business rules
- Integration tests: Command handlers and repository behaviors
- Functional tests: HTTP requests (using Symfony client)
- Persistence tests: Doctrine repositories with SQLite
- Run tests with:

composer test

✅ 100% pass rate (30/30)

## ⚙️ Setup with Docker

- 1. Clone the repository:

    git clone https://github.com/JamDesigns/siroko-cart-api.git
    cd siroko-cart-api
- 2. Install dependencies:

    composer install
- 3. Build and run the app:

    docker-compose build
    docker-compose up -d
- Application will be available at http://localhost:8000

## 🛠️ Tech Stack

- PHP 8.4
- Symfony 6
- Doctrine ORM
- PHPUnit
- Docker
- DDD
- CQRS
- Postman (for API testing)

## 🧠 Architecture

- Domain layer: Framework-agnostic core logic
- Application layer: CQRS Command Handlers
- Infrastructure layer: Doctrine + Symfony Controllers

## 🔀 Git Workflow

- Feature branches: feature/<name>
- Pull Requests: Used to merge into master
- Commit messages: Imperative, clean, English

## 📁 Postman Collection

- Included in the repo: postman/SirokoCartAPI.postman_collection.json
- Uses base_url variable: http://localhost:8000
- You can import the environment and collection into Postman directly.

- ✍️ Developed for Siroko - Senior Code Challenge
- 👤 By: José Ángel Mosquera Rodríguez

