<?php
$servername = "localhost";
$username = "root";
$password = "pradip";
$dbname = "car_center";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = htmlspecialchars($_POST['username']);
    $pass = $_POST['password'];

    $sql = "SELECT password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();

    if ($hashed_password && password_verify($pass, $hashed_password)) {
        // Login successful
        session_start(); // Start session
        $_SESSION['username'] = $user; // Store username in session
        header("Location: home.html"); // Redirect to home page
        exit(); // Ensure no further code is executed after redirection
    } else {
        // Login failed
       // echo "Invalid username or password.   ";

       header("Location: index.html?error=Invalid username and password");
       exit();

       // echo '<p> Don\'t have an account? <a href="registration.html">Create one</a></p>';

    }

    $stmt->close();
    $conn->close();
}
?>
