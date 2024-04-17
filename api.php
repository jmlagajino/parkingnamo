<?php
function register($pdo){
    $required_fields = ['first_name', 'last_name', 'plate', 'phone', 'pin'];
    $errors = [];

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }

    // Validate phone number
    if (!empty($_POST['phone'])) {
        if (!ctype_digit($_POST['phone'])) {
            $errors['phone'] = 'Phone must contain only numbers';
        } elseif (strlen($_POST['phone']) !== 11) {
            $errors['phone'] = 'Phone must be exactly 11 digits';
        }
    }

    // Validate PIN
    if (!empty($_POST['pin'])) {
        if (!ctype_digit($_POST['pin'])) {
            $errors['pin'] = 'PIN must contain only numbers';
        } elseif (strlen($_POST['pin']) !== 4) {
            $errors['pin'] = 'PIN must be exactly 4 digits';
        }
    }

    // Check for duplicate plate number
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM users WHERE plate = ?");
    $stmt->execute([$_POST['plate']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] > 0) {
        $errors['plate'] = 'Plate number already exists';
    }

    if (!empty($errors)) {
        $response = ['success' => false, 'errors' => $errors];
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, plate, phone, pin, user_type) VALUES (?, ?, ?, ?, ?, 'user')");
        $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['plate'], $_POST['phone'], $_POST['pin']]);
        $response = ['success' => true, 'message' => 'User inserted into database'];
    }

    echo json_encode($response);
}


function login($pdo){
    $required_fields = ['plate', 'pin'];
    $errors = [];

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[$field] = ucfirst($field) . ' is required';
        }
    }

    // Validate PIN format
    if (!empty($_POST['pin'])) {
        if (!ctype_digit($_POST['pin'])) {
            $errors['pin'] = 'PIN must contain only numbers';
        } elseif (strlen($_POST['pin']) !== 4) {
            $errors['pin'] = 'PIN must be exactly 4 digits';
        }
    }

    if (empty($errors)) {
        $plate = $_POST['plate'];
        $pin = $_POST['pin'];

        // Validate plate and pin format if needed
        // Example: Check if plate number exists and is associated with the correct pin
        $stmt = $pdo->prepare("SELECT * FROM users WHERE plate = ?");
        $stmt->execute([$plate]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $pin == $user['pin']) {
            if ($user['user_type'] === 'admin') {
                $response = [
                    'success' => true,
                    'user_type' => $user['user_type'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'plate' => $user['plate']
                ];
                echo json_encode($response);
                die();
            } else if ($user['user_type'] === 'user') {
                $response = [
                    'success' => true,
                    'user_type' => $user['user_type'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'plate' => $user['plate']
                ];
                echo json_encode($response);
                die();
            } else {
                $response = ['success' => true, 'message' => 'Login successful'];
            }
        } else {
            $response = ['success' => false, 'errors' => ['login' => 'Invalid plate number or PIN']];
        }
    } else {
        $response = ['success' => false, 'errors' => $errors];
    }

    echo json_encode($response);
}
?>
