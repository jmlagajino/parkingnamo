<?php

// function get_plate_parking($pdo, $plate) {
//     if (!empty($plate)) {

//         $stmt = $pdo->prepare("SELECT * FROM parkings WHERE plate = ?");
//         $stmt->execute([$plate]);
//         $parkingData = $stmt->fetchAll(PDO::FETCH_ASSOC);

//         if ($parkingData) {
//             $response = ['success' => true, 'parking_data' => $parkingData];
//         } else {
//             $response = ['success' => false, 'message' => 'No parking data found for this plate'];
//         }
//     } else {
//         $response = ['success' => false, 'errors' => ['plate' => 'Plate number is required']];
//     }

//     echo json_encode($response);
// }

function dashboard($pdo) {
    // Fetch total cars registered
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_cars FROM users WHERE user_type = 'user'");
    $stmt->execute();
    $total_cars = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch total parkings today
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_parkings FROM parkings WHERE DATE(time_in) = CURDATE()");
    $stmt->execute();
    $total_parkings = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch profit today
    $stmt = $pdo->prepare("SELECT SUM(paid) as profit_today FROM parkings WHERE DATE(time_in) = CURDATE()");
    $stmt->execute();
    $profit_today = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch all parkings
    $stmt = $pdo->prepare("SELECT * FROM parkings ORDER BY id DESC");
    $stmt->execute();
    $all_parkings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare and echo the JSON response
    $response = [
        'success' => true,
        'total_cars_registered' => $total_cars['total_cars'],
        'total_parkings_today' => $total_parkings['total_parkings'],
        'profit_today' => $profit_today['profit_today'],
        'all_parkings' => $all_parkings
    ];

    echo json_encode($response);
}
?>