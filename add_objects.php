<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");


// Handle OPTIONS preflight requests:
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database credentials — change these to your actual DB details
$servername = "localhost";
$username = "root";
$password = "";  // default for XAMPP is usually empty
$dbname = "user_auth"; // change this to your database name

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Get POST data as JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['name'], $data['description'], $data['address'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid input"]);
    exit();
}

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO objects (name, description, address) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $data['name'], $data['description'], $data['address']);

// Execute insert
if ($stmt->execute()) {
    // Return inserted id
    $insertedId = $stmt->insert_id;
    echo json_encode(["objects_id" => $insertedId]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Insert failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
