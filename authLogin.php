<?php
session_start();
require_once "db_config.php";

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (isset($_POST['_csrf']) && $_POST['_csrf'] === $_SESSION['_csrf']) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['userid'] = $user['id'];

            echo json_encode(['success' => true, 'message' => 'Login successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect username or password']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    }
} else {
    header('Location: login.php');
    $_SESSION['message'] = 'Invalid request method';
}
?>
