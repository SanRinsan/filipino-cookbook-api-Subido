# 🇵🇭 Filipino Cookbook REST API

A secure RESTful API built with **PHP**, **Slim Framework 4**, and **MySQL** for managing a Filipino Cookbook database.

The API provides endpoints to retrieve, search, create, update, and delete Filipino food recipes along with their categories, origins, and ingredients.

---

## Features

- Secure Bearer Token Authentication
- RESTful API Architecture
- JSON Responses
- CRUD Operations for Foods
- Search Foods by Name
- Search Foods by Origin
- Retrieve Categories
- Retrieve Ingredients
- Retrieve Origins
- MySQL Database Integration
- PDO Prepared Statements
- Transaction Support
- Proper HTTP Status Codes
- Error Handling

---

# Technology Stack

- PHP 8+
- Slim Framework 4
- MySQL
- Composer
- PDO

---

# Project Structure

```
project/
│
├── public/
│   └── index.php
│
├── vendor/
│
├── composer.json
│
└── README.md
```

---

# Installation

## Requirements

- PHP 8.0 or later
- MySQL
- XAMPP (or any PHP/MySQL server)

> **Note:** If the project already includes the `vendor` folder, Composer installation is **not required**. If the `vendor` folder is missing, install Composer and run `composer install`.

## Setup

1. Download or extract the project files.
2. Copy the project folder to your web server directory (e.g., `htdocs` if using XAMPP).
3. Create a MySQL database named:

```
filipino_cookbook_api
```

4. Import the provided SQL database file.
5. Verify the database credentials in `public/index.php`:

```php
$db = new PDO(
    'mysql:host=localhost;dbname=filipino_cookbook_api;charset=utf8mb4',
    'root',
    ''
);
```

6. Start MySQL only, on XAMPP

7. (important) only use the built in php server, not apache

   ```bash
php -S localhost:8000 -t public
```

9. Access the API at:

```
http://localhost:8000//
```

Update the credentials if necessary.

---

## 5. Run the Server

```bash
php -S localhost:8080 -t public
```

API Base URL

```
http://localhost:8080
```

---

# Authentication

All `/api` routes require a Bearer Token.

```
Authorization: Bearer dmmmsu-cookbook-token-2026
```

Example

```
Authorization: Bearer dmmmsu-cookbook-token-2026
```

If authentication fails:

```json
{
    "status":"error",
    "message":"Unauthorized access. Valid API token is required."
}
```

---

# API Endpoints

---

## Welcome

### GET /

Returns API information.

### Response

```json
{
    "message":"Welcome to the Secured Filipino Cookbook API",
    "note":"Use a valid Bearer token to access /api endpoints."
}
```

---

# Foods

---

## Get All Foods

### GET /api/foods

Returns every food with category, origin, ingredients and instructions.

### Response

```json
{
  "status":"success",
  "count":2,
  "data":[]
}
```

---

## Get Food by ID

### GET /api/foods/{id}

Example

```
GET /api/foods/1
```

Returns a single food.

---

## Search Food by Name

### GET /api/foods/search/{name}

Example

```
GET /api/foods/search/adobo
```

Performs a partial search.

---

## Search Foods by Origin

### GET /api/foods/origin/{origin}

Example

```
GET /api/foods/origin/Ilocos
```

Case-insensitive partial search.

---

## Create Food

### POST /api/foods

### Request Body

```json
{
    "food_name":"Chicken Adobo",
    "category_id":1,
    "origin_id":2,
    "instructions":"Cook the chicken...",
    "ingredient_ids":[
        1,
        3,
        7
    ]
}
```

### Success Response

```json
{
    "status":"success",
    "message":"Food added successfully."
}
```

---

## Update Food

### PUT /api/foods/{id}

Updates an existing food.

### Request Body

```json
{
    "food_name":"Chicken Adobo",
    "category_id":1,
    "origin_id":2,
    "instructions":"Updated instructions...",
    "ingredient_ids":[
        1,
        2,
        3
    ]
}
```

---

## Delete Food

### DELETE /api/foods/{id}

Example

```
DELETE /api/foods/1
```

Response

```json
{
    "status":"success",
    "message":"Food deleted successfully."
}
```

---

# Categories

---

## Get All Categories

### GET /api/categories

Response

```json
{
    "status":"success",
    "count":5,
    "data":[]
}
```

---

# Ingredients

---

## Get All Ingredients

### GET /api/ingredients

Returns every ingredient.

---

# Origins

---

## Get All Origins

### GET /api/origins

Returns every food origin.

---

# HTTP Status Codes

| Code | Meaning |
|------|---------|
|200|Success|
|201|Created|
|400|Bad Request|
|401|Unauthorized|
|404|Not Found|
|409|Conflict|
|500|Internal Server Error|

---

# Example Request

```http
GET /api/foods HTTP/1.1
Host: localhost:8080
Authorization: Bearer dmmmsu-cookbook-token-2026
Accept: application/json
```

---

# Sample Food Object

```json
{
    "food_id":1,
    "food_name":"Chicken Adobo",
    "category_name":"Main Course",
    "origin_name":"Luzon",
    "instructions":"Cook the chicken...",
    "ingredients":[
        "Chicken",
        "Garlic",
        "Soy Sauce",
        "Vinegar"
    ]
}
```

---

# Database Tables

The API uses the following tables:

- foods
- categories
- origins
- ingredients
- food_ingredients

---

# Error Handling

Possible errors include:

- Missing required fields
- Invalid Bearer Token
- Duplicate food entries
- Database connection failure
- SQL exceptions
- Food not found

---

# Security Features

- Bearer Token Authentication
- PDO Prepared Statements
- SQL Injection Protection
- Transactions for Create, Update, and Delete
- Input Validation
- Consistent JSON Error Responses

---

# Author

Developed as a RESTful API project using Slim Framework 4 and MySQL for managing Filipino cookbook recipes.




