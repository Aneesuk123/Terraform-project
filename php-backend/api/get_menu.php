<?php
// api/get_menu.php
error_reporting(E_ALL);
ini_set("display_errors", 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

include __DIR__ . '/../db.php'; // ensures $conn is available

$sql = "SELECT id, name, price FROM menu ORDER BY id";
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed', 'message' => $e->getMessage()]);
}
?>
