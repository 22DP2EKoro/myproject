<?php
// Allow requests from all origins (you can specify a specific domain instead of '*')
header("Access-Control-Allow-Origin: *");
// Allow certain HTTP methods (if necessary)
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
// Allow certain headers (if needed)
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight OPTIONS requests (this is for browsers to check permissions before sending the actual request)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_auth";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['email']) && isset($data['password'])) {
    $email = $data['email'];
    $password = $data['password']; // User input

    // Check if the user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch user data
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Check if the user is an admin
            if ($user['is_admin'] == 1) {
                echo json_encode(["message" => "Admin login successful!", "role" => "admin"]);
            } else {
                echo json_encode(["message" => "Login successful!", "role" => "user"]);
            }
        } else {
            echo json_encode(["message" => "Incorrect password."]);
        }
    } else {
        echo json_encode(["message" => "User not found."]);
    }

    $stmt->close();
} else {
    echo json_encode(["message" => "Error: Email or password not provided."]);
}

$conn->close();
?>
