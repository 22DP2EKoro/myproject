<?php
header("Access-Control-Allow-Origin: http://127.0.0.1:5173");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

echo json_encode(["status" => "success"]);

// DB konfigurācija
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_auth";

// Savienojums ar DB
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Ievades datu nolasīšana
$data = json_decode(file_get_contents("php://input"), true);

// Validācija
if (empty($data['name']) || empty($data['address'])) {
    echo json_encode(["success" => false, "message" => "Name and address are required fields."]);
    $conn->close();
    exit;
}

// Sākam transakciju
$conn->begin_transaction();

try {
    // 1. Ievietojam pamatdatus objects tabulā
    $stmt = $conn->prepare("INSERT INTO objects (name, description, address) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $data['name'], $data['description'] ?? '', $data['address']);
    
    if (!$stmt->execute()) {
        throw new Exception("Error saving object data: " . $stmt->error);
    }
    
    $objects_id = $conn->insert_id;
    $stmt->close();

    // 2. Ievietojam muzeja specifiskos datus museums tabulā
    $stmt = $conn->prepare("INSERT INTO museums (objects_id, work_hours) VALUES (?, ?)");
    $stmt->bind_param("is", $objects_id, $data['work_hours'] ?? '');
    
    if (!$stmt->execute()) {
        throw new Exception("Error saving museum data: " . $stmt->error);
    }
    
    $stmt->close();

    // Ja viss izdevās, commit transakciju
    $conn->commit();
    
    echo json_encode([
        "success" => true,
        "message" => "Museum successfully added!",
        "objects_id" => $objects_id
    ]);
    
} catch (Exception $e) {
    // Ja kļūda, rollback transakciju
    $conn->rollback();
    
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}

$conn->close();
?>