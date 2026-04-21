# 🛒 Cart & Checkout API

Backend API for a shopping cart and checkout system built with **PHP and Symfony**, applying **DDD, CQRS and clean architecture principles**.

This project demonstrates how to design a scalable and maintainable backend system with a fully decoupled domain model.

Developed as part of a **senior backend engineering code challenge**.

## 🚀 Key Highlights

- Domain fully decoupled from framework (Symfony-agnostic core)
- Implementation of **DDD + CQRS**
- Use of **Domain Events** for key business actions
- Clean separation of layers (Domain / Application / Infrastructure)
- Full test coverage across business logic and application flows

## 📦 Features

- Add, update and remove products in the cart
- Retrieve cart contents
- Checkout process generating persistent orders
- Domain events triggered on all key actions

## 🧠 Architecture

- **Domain Layer** → Core business logic (framework independent)
- **Application Layer** → Command Handlers (CQRS)
- **Infrastructure Layer** → Symfony + Doctrine integration

### Domain Model

- `Cart` (Aggregate Root)
- `CartItem`
- `Order`
- Value Objects: `Money`, `Currency`, `Quantity`, `Product`

### Domain Events

- ProductAddedToCart  
- ProductQuantityUpdatedInCart  
- ProductRemovedFromCart  
- CartEmptied  
- OrderCreated  

## 🔧 API Endpoints

### Cart

- `POST /cart/{cartId}/items`
- `PATCH /cart/{cartId}/items/{productId}`
- `DELETE /cart/{cartId}/items/{productId}`
- `GET /cart/{cartId}`
- `GET /carts`
- `DELETE /cart/{cartId}`

### Checkout

- `POST /checkout/{cartId}`
- `GET /orders`

## 🧪 Testing

- Unit tests → business rules and value objects  
- Integration tests → command handlers and persistence  
- Functional tests → HTTP endpoints  

Run tests:

```bash
composer test
```

✔️ 100% pass rate

## ⚙️ Setup

```bash
git clone https://github.com/JamDesigns/cart-api.git
cd cart-api
composer install
docker-compose up -d
```

App available at:

👉 http://localhost:8000

## 🛠️ Tech Stack

- PHP 8  
- Symfony 6  
- Doctrine ORM  
- PHPUnit  
- Docker  
- DDD / CQRS  

## 📄 API Testing

Postman collection included:

`postman/SirokoCartAPI.postman_collection.json`

## 👤 Author

José Ángel Mosquera Rodríguez  
Senior PHP Backend Developer
