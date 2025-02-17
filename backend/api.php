<?php
// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY");
    header("Access-Control-Max-Age: 3600");
    http_response_code(204);
    exit();
}

// Set headers for all other requests
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-API-KEY");

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the incoming request
error_log("Received request: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);

require_once 'Database.php';
require_once 'User.php';
require_once 'Quiz.php';

$method = $_SERVER['REQUEST_METHOD'];

// Parse the request URI
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/template/backend/api.php';

// Remove base path if present
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

$request = explode('/', trim($requestUri, '/'));
$action = array_shift($request) ?? '';

error_log("Parsed action: " . $action);

// Handle empty action for root URL
if (empty($action)) {
    // For root URL, show available endpoints
    if ($requestUri === '/' || $requestUri === '') {
        echo json_encode([
            "available_endpoints" => [
                "POST /login",
                "POST /signup",
                "POST /saveResult",
                "GET /results"
            ]
        ]);
        exit();
    }
    http_response_code(400);
    echo json_encode(["message" => "No action specified"]);
    exit();
}


// Route the request based on HTTP method
switch ($method) {
    case 'POST':
        handlePostRequest($action);
        break;
    case 'GET':
        handleGetRequest($action);
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed"]);
        break;
}

function handlePostRequest($action) {
    error_log("Handling POST request for action: " . $action);
    
    // Get and validate request body
    $input = file_get_contents("php://input");
    error_log("Request body: " . $input);

    if (empty($input)) {
        http_response_code(400);
        echo json_encode(["message" => "Request body is empty"]);
        return;
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            "message" => "Invalid JSON data",
            "error" => json_last_error_msg()
        ]);
        return;
    }

    try {
        switch ($action) {
            case 'login':
                if (!isset($data['email']) || !isset($data['password'])) {
                    throw new Exception("Email and password are required");
                }
                $user = new User();
                $result = $user->login($data['email'], $data['password']);
                echo json_encode($result);
                break;
                
            case 'signup':
                if (!isset($data['email']) || !isset($data['password']) || !isset($data['name'])) {
                    throw new Exception("Name, email and password are required");
                }
                $user = new User();
                $result = $user->create($data);
                echo json_encode($result);
                break;

            case 'saveResult':
                if (!isset($data['userId']) || !isset($data['category']) || 
                    !isset($data['correct']) || !isset($data['total']) || 
                    !isset($data['time'])) {
                    throw new Exception("Missing required fields for saving result");
                }
                $quiz = new Quiz();
                $result = $quiz->saveResult(
                    $data['userId'],
                    $data['category'],
                    $data['correct'],
                    $data['total'],
                    $data['time']
                );
                echo json_encode(['success' => $result]);
                break;

            case 'updatePoints':
                if (!isset($data['userId']) || !isset($data['points'])) {
                    throw new Exception("userId and points are required");
                }
                $user = new User();
                $result = $user->updatePoints($data['userId'], $data['points']);
                echo json_encode(['success' => $result]);
                break;

                
            default:
                http_response_code(404);
                echo json_encode(["message" => "Action not found"]);
                break;
        }
    } catch (Exception $e) {
        error_log("Error in handlePostRequest: " . $e->getMessage());
        http_response_code(400);
        echo json_encode([
            "message" => $e->getMessage(),
            "success" => false
        ]);
    }
}

function handleGetRequest($action) {
    error_log("Handling GET request for action: " . $action);
    
    try {
        switch ($action) {
            case 'results':
                if (!isset($_GET['userId'])) {
                    throw new Exception("userId is required");
                }
                $userId = $_GET['userId'];
                $quiz = new Quiz();
                $results = $quiz->getResults($userId);
                echo json_encode([
                    "success" => true,
                    "data" => $results
                ]);
                break;
                
            default:
                http_response_code(404);
                echo json_encode(["message" => "Action not found"]);
                break;
        }
    } catch (Exception $e) {
        error_log("Error in handleGetRequest: " . $e->getMessage());
        http_response_code(400);
        echo json_encode([
            "message" => $e->getMessage(),
            "success" => false
        ]);
    }
}

// Helper function to validate required fields
function validateRequiredFields($data, $required) {
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Missing required field: " . $field);
        }
    }
    return true;
}
?>
