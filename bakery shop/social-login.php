<?php
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['provider']) || !isset($input['social_id']) || !isset($input['email'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$provider = $input['provider']; // 'facebook' or 'google'
$socialId = $input['social_id'];
$email = $input['email'];
$name = $input['name'] ?? '';

// Check if user exists with this email
$checkQuery = "SELECT * FROM users WHERE email = ?";
$checkResult = executePreparedQuery($checkQuery, "s", [$email]);

if ($checkResult && mysqli_num_rows($checkResult) > 0) {
    // User exists, log them in
    $user = mysqli_fetch_assoc($checkResult);
    
    // Check if social columns exist, if yes, update them
    $columns = [];
    $checkColumns = executeQuery("SHOW COLUMNS FROM users LIKE 'social_id'");
    if ($checkColumns && mysqli_num_rows($checkColumns) > 0) {
        $updateQuery = "UPDATE users SET social_id = ?, social_provider = ? WHERE userID = ?";
        executePreparedUpdate($updateQuery, "ssi", [$socialId, $provider, $user['userID']]);
    }
    
    // Set session
    $_SESSION['userID'] = $user['userID'];
    $_SESSION['fullName'] = $user['fullName'];
    $_SESSION['email'] = $user['email'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => 'index.php'
    ]);
} else {
    // New user, create account
    // Generate a random password (not used for social login but required by DB)
    $randomPassword = bin2hex(random_bytes(16));
    $hashedPassword = password_hash($randomPassword, PASSWORD_BCRYPT);
    
    // Check if social columns exist
    $hasSocialColumns = false;
    $checkColumns = executeQuery("SHOW COLUMNS FROM users LIKE 'social_id'");
    if ($checkColumns && mysqli_num_rows($checkColumns) > 0) {
        $hasSocialColumns = true;
    }
    
    // Insert new user
    if ($hasSocialColumns) {
        $insertQuery = "INSERT INTO users (fullName, email, password, social_id, social_provider, mobileNumber) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        $result = executePreparedUpdate($insertQuery, "ssssss", [
            $name,
            $email,
            $hashedPassword,
            $socialId,
            $provider,
            '' // Mobile number can be empty for social login
        ]);
    } else {
        // Fallback: insert without social columns
        $insertQuery = "INSERT INTO users (fullName, email, password, mobileNumber) 
                        VALUES (?, ?, ?, ?)";
        $result = executePreparedUpdate($insertQuery, "ssss", [
            $name,
            $email,
            $hashedPassword,
            '' // Mobile number can be empty for social login
        ]);
    }
    
    if ($result !== false) {
        // Get the new user ID
        $newUserQuery = "SELECT * FROM users WHERE email = ?";
        $newUserResult = executePreparedQuery($newUserQuery, "s", [$email]);
        
        if ($newUserResult && ($newUser = mysqli_fetch_assoc($newUserResult))) {
            // Set session
            $_SESSION['userID'] = $newUser['userID'];
            $_SESSION['fullName'] = $newUser['fullName'];
            $_SESSION['email'] = $newUser['email'];
            
            echo json_encode([
                'success' => true,
                'message' => 'Account created and logged in successfully',
                'redirect' => 'index.php'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Account created but login failed']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create account']);
    }
}
?>

