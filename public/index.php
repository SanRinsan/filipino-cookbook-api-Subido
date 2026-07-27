<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

$app->addErrorMiddleware(true, true, true);

// Database connection
function getDB() {
    try {
        $db = new PDO('mysql:host=localhost;dbname=filipino_cookbook_api;charset=utf8mb4', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $db;
    } catch (PDOException $e) {
        return null;
    }
}

function formatFood($food) {
    $ingredients = !empty($food['ingredients']) ? explode(', ', $food['ingredients']) : [];
    return [
        'food_id' => (int)$food['food_id'],
        'food_name' => $food['food_name'],
        'category_name' => $food['category_name'],
        'origin_name' => $food['origin_name'],
        'instructions' => $food['instructions'],
        'ingredients' => $ingredients
    ];
}

function sendJSON($response, $data, $status = 200) {
    $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
    return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
}

// ============================================
// AUTH MIDDLEWARE
// ============================================
$authMiddleware = function ($request, $handler) {
    $authHeader = $request->getHeaderLine('Authorization');
    $validToken = 'dmmmsu-cookbook-token-2026';
    
    if (empty($authHeader) || strpos($authHeader, 'Bearer ') !== 0) {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => 'Unauthorized access. Valid API token is required.'
        ]));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }
    
    $token = substr($authHeader, 7);
    
    if ($token !== $validToken) {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => 'Unauthorized access. Valid API token is required.'
        ]));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }
    
    return $handler->handle($request);
};

// ============================================
// 1. GET /
// Description: Welcome message with API info
// Auth Required: No
// ============================================
$app->get('/', function (Request $request, Response $response) {
    return sendJSON($response, [
        'message' => 'Welcome to the Secured Filipino Cookbook API',
        'note' => 'Use a valid Bearer token to access /api endpoints.',
    ]);
});

$app->group('/api', function (RouteCollectorProxy $group) {
    
    // ============================================
    // 2. GET /api/foods
    // Description: Get all Filipino foods with their ingredients
    // Auth Required: Yes (Bearer Token)
    // ============================================
    $group->get('/foods', function (Request $request, Response $response) {
        try {
            $db = getDB();
            if (!$db) {
                return sendJSON($response, [
                    'status' => 'error',
                    'message' => 'Database connection failed. Make sure MySQL is running.'
                ], 500);
            }
            
            $sql = "SELECT 
                        f.food_id,
                        f.food_name,
                        c.category_name,
                        o.origin_name,
                        f.instructions,
                        GROUP_CONCAT(DISTINCT i.ingredient_name ORDER BY i.ingredient_name SEPARATOR ', ') as ingredients
                    FROM foods f
                    JOIN categories c ON f.category_id = c.category_id
                    JOIN origins o ON f.origin_id = o.origin_id
                    LEFT JOIN food_ingredients fi ON f.food_id = fi.food_id
                    LEFT JOIN ingredients i ON fi.ingredient_id = i.ingredient_id
                    GROUP BY f.food_id
                    ORDER BY f.food_name";
            
            $stmt = $db->query($sql);
            $foods = $stmt->fetchAll();
            $result = array_map('formatFood', $foods);
            return sendJSON($response, [
                'status' => 'success',
                'count' => count($result),
                'data' => $result
            ]);
            
        } catch (PDOException $e) {
            return sendJSON($response, [
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // ============================================
    // 3. GET /api/foods/{id}
    // Description: Get a specific food by ID
    // Auth Required: Yes (Bearer Token)
    // Example: /api/foods/1
    // ============================================
    $group->get('/foods/{id}', function (Request $request, Response $response, $args) {
        try {
            $db = getDB();
            if (!$db) {
                return sendJSON($response, [
                    'status' => 'error',
                    'message' => 'Database connection failed'
                ], 500);
            }
            
            $id = (int)$args['id'];
            $sql = "SELECT 
                        f.food_id,
                        f.food_name,
                        c.category_name,
                        o.origin_name,
                        f.instructions,
                        GROUP_CONCAT(DISTINCT i.ingredient_name ORDER BY i.ingredient_name SEPARATOR ', ') as ingredients
                    FROM foods f
                    JOIN categories c ON f.category_id = c.category_id
                    JOIN origins o ON f.origin_id = o.origin_id
                    LEFT JOIN food_ingredients fi ON f.food_id = fi.food_id
                    LEFT JOIN ingredients i ON fi.ingredient_id = i.ingredient_id
                    WHERE f.food_id = ?
                    GROUP BY f.food_id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            $food = $stmt->fetch();
            
            if (!$food) {
                return sendJSON($response, [
                    'status' => 'error',
                    'message' => 'Food not found'
                ], 404);
            }
            
            return sendJSON($response, [
                'status' => 'success',
                'data' => formatFood($food)
            ]);
            
        } catch (PDOException $e) {
            return sendJSON($response, [
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // ============================================
    // 4. GET /api/foods/search/{name}
    // Description: Search foods by name (partial match)
    // Auth Required: Yes (Bearer Token)
    // Example: /api/foods/search/adobo
    // ============================================
    $group->get('/foods/search/{name}', function (Request $request, Response $response, $args) {
        try {
            $db = getDB();
            if (!$db) {
                return sendJSON($response, [
                    'status' => 'error',
                    'message' => 'Database connection failed'
                ], 500);
            }
            
            $name = '%' . $args['name'] . '%';
            $sql = "SELECT 
                        f.food_id,
                        f.food_name,
                        c.category_name,
                        o.origin_name,
                        f.instructions,
                        GROUP_CONCAT(DISTINCT i.ingredient_name ORDER BY i.ingredient_name SEPARATOR ', ') as ingredients
                    FROM foods f
                    JOIN categories c ON f.category_id = c.category_id
                    JOIN origins o ON f.origin_id = o.origin_id
                    LEFT JOIN food_ingredients fi ON f.food_id = fi.food_id
                    LEFT JOIN ingredients i ON fi.ingredient_id = i.ingredient_id
                    WHERE f.food_name LIKE ?
                    GROUP BY f.food_id
                    ORDER BY f.food_name";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$name]);
            $foods = $stmt->fetchAll();
            $result = array_map('formatFood', $foods);
            return sendJSON($response, [
                'status' => 'success',
                'count' => count($result),
                'data' => $result
            ]);
            
        } catch (PDOException $e) {
            return sendJSON($response, [
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // ============================================
    // 8. GET /api/foods/origin/{origin}
    // Description: Search foods by origin (case-insensitive, partial match)
    // Auth Required: Yes (Bearer Token)
    // Example: /api/foods/origin/ilocos
    // ============================================
    $group->get('/foods/origin/{origin}', function (Request $request, Response $response, $args) {
        try {
            $db = getDB();
            if (!$db) {
                return sendJSON($response, [
                    'status' => 'error',
                    'message' => 'Database connection failed'
                ], 500);
            }
            
            $origin = '%' . $args['origin'] . '%';
            $sql = "SELECT 
                        f.food_id,
                        f.food_name,
                        c.category_name,
                        o.origin_name,
                        f.instructions,
                        GROUP_CONCAT(DISTINCT i.ingredient_name ORDER BY i.ingredient_name SEPARATOR ', ') as ingredients
                    FROM foods f
                    JOIN categories c ON f.category_id = c.category_id
                    JOIN origins o ON f.origin_id = o.origin_id
                    LEFT JOIN food_ingredients fi ON f.food_id = fi.food_id
                    LEFT JOIN ingredients i ON fi.ingredient_id = i.ingredient_id
                    WHERE LOWER(o.origin_name) LIKE LOWER(?)
                    GROUP BY f.food_id
                    ORDER BY f.food_name";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$origin]);
            $foods = $stmt->fetchAll();
            $result = array_map('formatFood', $foods);
            
            if (empty($result)) {
                return sendJSON($response, [
                    'status' => 'success',
                    'message' => 'No foods found for this origin',
                    'count' => 0,
                    'data' => []
                ]);
            }
            
            return sendJSON($response, [
                'status' => 'success',
                'count' => count($result),
                'origin' => $args['origin'],
                'data' => $result
            ]);
            
        } catch (PDOException $e) {
            return sendJSON($response, [
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // ============================================
    // 5. GET /api/categories
    // Description: Get all food categories
    // Auth Required: Yes (Bearer Token)
    // ============================================
    $group->get('/categories', function (Request $request, Response $response) {
        try {
            $db = getDB();
            if (!$db) {
                return sendJSON($response, [
                    'status' => 'error',
                    'message' => 'Database connection failed'
                ], 500);
            }
            
            $stmt = $db->query("SELECT category_id, category_name FROM categories ORDER BY category_name");
            $categories = $stmt->fetchAll();
            $result = array_map(function($cat) {
                return [
                    'category_id' => (int)$cat['category_id'],
                    'category_name' => $cat['category_name']
                ];
            }, $categories);
            return sendJSON($response, [
                'status' => 'success',
                'count' => count($result),
                'data' => $result
            ]);
            
        } catch (PDOException $e) {
            return sendJSON($response, [
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // ============================================
    // 6. GET /api/ingredients
    // Description: Get all ingredients
    // Auth Required: Yes (Bearer Token)
    // ============================================
    $group->get('/ingredients', function (Request $request, Response $response) {
        try {
            $db = getDB();
            if (!$db) {
                return sendJSON($response, [
                    'status' => 'error',
                    'message' => 'Database connection failed'
                ], 500);
            }
            
            $stmt = $db->query("SELECT ingredient_id, ingredient_name FROM ingredients ORDER BY ingredient_name");
            $ingredients = $stmt->fetchAll();
            $result = array_map(function($ing) {
                return [
                    'ingredient_id' => (int)$ing['ingredient_id'],
                    'ingredient_name' => $ing['ingredient_name']
                ];
            }, $ingredients);
            return sendJSON($response, [
                'status' => 'success',
                'count' => count($result),
                'data' => $result
            ]);
            
        } catch (PDOException $e) {
            return sendJSON($response, [
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // ============================================
    // 9. GET /api/origins
    // Description: Get all origins
    // Auth Required: Yes (Bearer Token)
    // ============================================
    $group->get('/origins', function (Request $request, Response $response) {
        try {
            $db = getDB();
            if (!$db) {
                return sendJSON($response, [
                    'status' => 'error',
                    'message' => 'Database connection failed'
                ], 500);
            }
            
            $stmt = $db->query("SELECT origin_id, origin_name FROM origins ORDER BY origin_name");
            $origins = $stmt->fetchAll();
            $result = array_map(function($org) {
                return [
                    'origin_id' => (int)$org['origin_id'],
                    'origin_name' => $org['origin_name']
                ];
            }, $origins);
            return sendJSON($response, [
                'status' => 'success',
                'count' => count($result),
                'data' => $result
            ]);
            
        } catch (PDOException $e) {
            return sendJSON($response, [
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // ============================================
    // 7. POST /api/foods  Create
    // Description: Add a new food
    // Auth Required: Yes (Bearer Token)
    // Body: JSON with food_name, category_id, origin_id, instructions, ingredient_ids
    // ============================================
    $group->post('/foods', function (Request $request, Response $response) {
        try {
            $db = getDB();
            if (!$db) {
                return sendJSON($response, [
                    'status' => 'error',
                    'message' => 'Database connection failed'
                ], 500);
            }
            
            $data = $request->getParsedBody();
            
            $required = ['food_name', 'category_id', 'origin_id', 'instructions', 'ingredient_ids'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return sendJSON($response, [
                        'status' => 'error',
                        'message' => "Missing required field: {$field}"
                    ], 400);
                }
            }
            
            if (!is_array($data['ingredient_ids'])) {
                return sendJSON($response, [
                    'status' => 'error',
                    'message' => 'ingredient_ids must be an array'
                ], 400);
            }
            
            // Check for duplicate food (case-insensitive)
            $checkSql = "SELECT food_id, food_name FROM foods WHERE LOWER(food_name) = LOWER(?)";
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->execute([$data['food_name']]);
            $existingFood = $checkStmt->fetch();
            
            if ($existingFood) {
                return sendJSON($response, [
                    'status' => 'error',
                    'message' => 'Food already exists',
                    'existing_food_id' => (int)$existingFood['food_id'],
                    'existing_food_name' => $existingFood['food_name']
                ], 409);
            }
            
            $db->beginTransaction();
            $stmt = $db->query("SELECT MAX(food_id) + 1 as next_id FROM foods");
            $nextId = $stmt->fetch()['next_id'] ?: 1;
            
            $stmt = $db->prepare("INSERT INTO foods (food_id, food_name, category_id, origin_id, instructions) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $nextId,
                $data['food_name'],
                (int)$data['category_id'],
                (int)$data['origin_id'],
                $data['instructions']
            ]);
            
            $stmt = $db->prepare("INSERT INTO food_ingredients (food_id, ingredient_id) VALUES (?, ?)");
            foreach ($data['ingredient_ids'] as $ingredientId) {
                $stmt->execute([$nextId, (int)$ingredientId]);
            }
            
            $db->commit();
            return sendJSON($response, [
                'status' => 'success',
                'message' => 'Food added successfully.',
            ], 201);
            
        } catch (PDOException $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            return sendJSON($response, [
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // ============================================
    // 10. PUT /api/foods/{id} Update
    // Description: Update a food by ID (cannot update food_id)
    // Auth Required: Yes (Bearer Token)
    // Body: JSON with food_name, category_id, origin_id, instructions, ingredient_ids
    // Example: /api/foods/1
    // ============================================
    $group->put('/foods/{id}', function (Request $request, Response $response, $args) {
        try {
            $db = getDB();
            if (!$db) {
                return sendJSON($response, [
                    'status' => 'error',
                    'message' => 'Database connection failed'
                ], 500);
            }
            
            $id = (int)$args['id'];
            $data = $request->getParsedBody();
            
            // Check if food exists
            $checkSql = "SELECT food_id, food_name FROM foods WHERE food_id = ?";
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->execute([$id]);
            $existingFood = $checkStmt->fetch();
            
            if (!$existingFood) {
                return sendJSON($response, [
                    'status' => 'error',
                    'message' => 'Food not found'
                ], 404);
            }
            
            // Validate required fields
            $required = ['food_name', 'category_id', 'origin_id', 'instructions', 'ingredient_ids'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return sendJSON($response, [
                        'status' => 'error',
                        'message' => "Missing required field: {$field}"
                    ], 400);
                }
            }
            
            if (!is_array($data['ingredient_ids'])) {
                return sendJSON($response, [
                    'status' => 'error',
                    'message' => 'ingredient_ids must be an array'
                ], 400);
            }
            
            // Check for duplicate food name (case-insensitive) - exclude current food
            $checkDuplicateSql = "SELECT food_id, food_name FROM foods WHERE LOWER(food_name) = LOWER(?) AND food_id != ?";
            $checkDuplicateStmt = $db->prepare($checkDuplicateSql);
            $checkDuplicateStmt->execute([$data['food_name'], $id]);
            $duplicateFood = $checkDuplicateStmt->fetch();
            
            if ($duplicateFood) {
                return sendJSON($response, [
                    'status' => 'error',
                    'message' => 'Food name already exists',
                    'existing_food_id' => (int)$duplicateFood['food_id'],
                    'existing_food_name' => $duplicateFood['food_name']
                ], 409);
            }
            
            $db->beginTransaction();
            
            // Update the food
            $updateSql = "UPDATE foods SET 
                            food_name = ?,
                            category_id = ?,
                            origin_id = ?,
                            instructions = ?
                          WHERE food_id = ?";
            
            $updateStmt = $db->prepare($updateSql);
            $updateStmt->execute([
                $data['food_name'],
                (int)$data['category_id'],
                (int)$data['origin_id'],
                $data['instructions'],
                $id
            ]);
            
            // Delete existing ingredient relationships
            $deleteStmt = $db->prepare("DELETE FROM food_ingredients WHERE food_id = ?");
            $deleteStmt->execute([$id]);
            
            // Insert new ingredient relationships
            $insertStmt = $db->prepare("INSERT INTO food_ingredients (food_id, ingredient_id) VALUES (?, ?)");
            foreach ($data['ingredient_ids'] as $ingredientId) {
                $insertStmt->execute([$id, (int)$ingredientId]);
            }
            
            $db->commit();
            
            // Get the updated food data
            $getSql = "SELECT 
                        f.food_id,
                        f.food_name,
                        c.category_name,
                        o.origin_name,
                        f.instructions,
                        GROUP_CONCAT(DISTINCT i.ingredient_name ORDER BY i.ingredient_name SEPARATOR ', ') as ingredients
                    FROM foods f
                    JOIN categories c ON f.category_id = c.category_id
                    JOIN origins o ON f.origin_id = o.origin_id
                    LEFT JOIN food_ingredients fi ON f.food_id = fi.food_id
                    LEFT JOIN ingredients i ON fi.ingredient_id = i.ingredient_id
                    WHERE f.food_id = ?
                    GROUP BY f.food_id";
            
            $getStmt = $db->prepare($getSql);
            $getStmt->execute([$id]);
            $updatedFood = $getStmt->fetch();
            
            return sendJSON($response, [
                'status' => 'success',
                'message' => 'Food updated successfully.',
                'data' => formatFood($updatedFood)
            ]);
            
        } catch (PDOException $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            return sendJSON($response, [
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // ============================================
    // 11. DELETE /api/foods/{id}
    // Description: Delete a food by ID
    // Auth Required: Yes (Bearer Token)
    // Example: /api/foods/1
    // ============================================
    $group->delete('/foods/{id}', function (Request $request, Response $response, $args) {
        try {
            $db = getDB();
            if (!$db) {
                return sendJSON($response, [
                    'status' => 'error',
                    'message' => 'Database connection failed'
                ], 500);
            }
            
            $id = (int)$args['id'];
            
            // First, check if the food exists
            $checkSql = "SELECT food_id, food_name FROM foods WHERE food_id = ?";
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->execute([$id]);
            $food = $checkStmt->fetch();
            
            if (!$food) {
                return sendJSON($response, [
                    'status' => 'error',
                    'message' => 'Food not found'
                ], 404);
            }
            
            // Begin transaction
            $db->beginTransaction();
            
            // Delete from food_ingredients first (foreign key constraint)
            $stmt = $db->prepare("DELETE FROM food_ingredients WHERE food_id = ?");
            $stmt->execute([$id]);
            
            // Delete from foods
            $stmt = $db->prepare("DELETE FROM foods WHERE food_id = ?");
            $stmt->execute([$id]);
            
            $db->commit();
            
            return sendJSON($response, [
                'status' => 'success',
                'message' => 'Food deleted successfully.',
                'deleted_food_id' => $id,
                'deleted_food_name' => $food['food_name']
            ]);
            
        } catch (PDOException $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            return sendJSON($response, [
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Apply auth middleware to all routes in the group
})->add($authMiddleware);

$app->run();