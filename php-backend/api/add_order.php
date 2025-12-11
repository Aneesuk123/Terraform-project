<?php
// api/add_order.php
error_reporting(E_ALL);
ini_set("display_errors", 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // preflight
    http_response_code(204);
    exit;
}

include __DIR__ . '/../db.php';

// Parse JSON body
$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!$body) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON body']);
    exit;
}

// expected fields: items (array or string), total_price (number), customer_name (optional)
$items = isset($body['items']) ? $body['items'] : null;
$total_price = isset($body['total_price']) ? $body['total_price'] : null;
$customer_name = isset($body['customer_name']) ? $body['customer_name'] : null;

if (!$items || $total_price === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields: items, total_price']);
    exit;
}

// Save items as JSON string for the DB
$items_json = is_string($items) ? $items : json_encode($items);

// Prepared insert (adjust columns to match your orders table)
$sql = "INSERT INTO orders (items, total_price, customer_name, created_at) VALUES (?, ?, ?, NOW())";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sds', $items_json, $total_price, $customer_name);
    $stmt->execute();

    $inserted_id = $stmt->insert_id;
    echo json_encode(['success' => true, 'order_id' => $inserted_id]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Insert failed', 'message' => $e->getMessage()]);
}
?>
