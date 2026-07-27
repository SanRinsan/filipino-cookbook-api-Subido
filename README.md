# 🇵🇭 Filipino Cookbook REST API

A secure RESTful API built with **PHP**, **Slim Framework 4**, and **MySQL** for managing a Filipino Cookbook database.

The API provides endpoints to retrieve, search, create, update, and delete Filipino food recipes along with their categories, origins, and ingredients.

---
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
---

# Technology Stack

- PHP 8+
- Slim Framework 4
- MySQL
- Composer
- PDO

---
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

6.  On XAMPP, start MySQL only.

7. (important) activate the built in php server on visual studio terminal.

```bash
php -S localhost:8000 -t public
```

9. Access the API at thunderclient:

```
http://localhost:8000//
```


---
---


# Authentication

All `/api` routes require a Bearer Token.

If authentication fails:

```json
{
    "status":"error",
    "message":"Unauthorized access. Valid API token is required."
}
```


---
---



# API Endpoints

## Welcome page

Description: The welcome page 

```
GET http://localhost:8000//
```

<img width="771" height="187" alt="Screenshot 2026-07-27 204450" src="https://github.com/user-attachments/assets/b0e99b9f-acbc-488e-9cad-e1a34334939b" />



-------

-------




## Get All Foods
Description: Fetch all the foods in the database

```
GET http://localhost:8000//api/foods
```

<img width="766" height="609" alt="image" src="https://github.com/user-attachments/assets/1789e10c-8be1-4be6-bfae-60cafd99762a" />


---
---



## Get Food by ID

Description: Only Returns a food based on its id

Example:

```
GET http://localhost:8000//api/foods/2 
```

<img width="767" height="615" alt="image" src="https://github.com/user-attachments/assets/e259bb6c-76d2-4745-99c0-989a893127bf" />


---
---



## Search Food by Name

Description: Search food by its name (CASE SENSITIVE) 

Example:

```
GET http://localhost:8000//api/foods/search/halo-halo
```

<img width="773" height="609" alt="image" src="https://github.com/user-attachments/assets/8e752de5-9b91-41cf-860b-413cb5d1111c" />



---
---



## Get all Origin 

Description: fetches all location (CASE SENSITIVE) 

example: 

```
GET http://localhost:8000//api/origins
```

<img width="770" height="615" alt="image" src="https://github.com/user-attachments/assets/8dc9c89e-06da-459c-bf4a-50e37c7154fa" />


---
---


## Create Food

Description: Adds a food to the database

```
POST http://localhost:8000//api/foods
```

### Request Body

Example:

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

<img width="770" height="232" alt="image" src="https://github.com/user-attachments/assets/b1de3e1e-96f9-49ba-8657-8a215a79f82d" />


---
---

## Update Food

### PUT /api/foods/{id}

Updates an existing food.

### Request Body

```json
{
"food_name": "Dinengdeng_test_UPDATED",
"category_id": 3,
"origin_id": 4,
"instructions": "Boil vegetables with bagoong-based broth and add grilled fish before serving.",
"ingredient_ids": [10, 15, 22]
}
```

<img width="773" height="624" alt="image" src="https://github.com/user-attachments/assets/77350e74-8c59-4650-a252-1d7e2273f78b" />




---
---



## Delete Food

Description: Delete an existing food

Example:

```
DELETE http://localhost:8000//api/foods/16
```

<img width="772" height="221" alt="image" src="https://github.com/user-attachments/assets/433e1583-70f9-4076-8a05-32f7897e2659" />


---
---


## Get All Categories

Description: fetches all categories in the database 
```
GET http://localhost:8000//api/categories
```

<img width="775" height="619" alt="image" src="https://github.com/user-attachments/assets/6737aed9-e751-420d-8b0d-200056756c1d" />



---
---


## Get All Ingredients

Desciption: Returns every ingredient.

```
GET http://localhost:8000//api/ingredients
```

<img width="775" height="611" alt="image" src="https://github.com/user-attachments/assets/ce4d68e9-25ff-471b-93de-f512a1c70edb" />




---
---

## Search foods by origins

Description: Returns a food based on searched origin.

```
GET http://localhost:8000//api/foods/origin/bacolod
```

<img width="772" height="612" alt="image" src="https://github.com/user-attachments/assets/a12b98be-6db9-4915-989a-3ce5ec5a49ab" />



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




