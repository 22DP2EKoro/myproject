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
$username = "root"; // default username in XAMPP
$password = "";     // default password is empty in XAMPP
$dbname = "user_auth"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Read the raw POST data
$data = json_decode(file_get_contents("php://input"), true);

// Check if email and password are provided
if (isset($data['email']) && isset($data['password'])) {
    $email = $data['email'];
    $password = $data['password']; // Raw password from user input

    // Check if user already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User already exists
        echo json_encode(["message" => "Email is already registered."]);
    } else {
        // Hash the password before storing it
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Register new user with hashed password
        $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $hashedPassword); // Bind the hashed password
        if ($stmt->execute()) {
            echo json_encode(["message" => "Registration successful!"]);
        } else {
            // Handle any unexpected errors
            echo json_encode(["message" => "Registration failed. Please try again later."]);
        }
    }

    $stmt->close();
} else {
    echo json_encode(["message" => "Error: Email or password not provided."]);
}

$conn->close();
?>
