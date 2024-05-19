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

// Function to get all data from the balance table
function transaction_data($pdo) {
    try {
        // Prepare and execute the SQL statement to select all data from the balance table
        $stmt = $pdo->prepare("SELECT * FROM balance");
        $stmt->execute();
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare the response
        $response = ['success' => true, 'transactions' => $transactions];
    } catch (PDOException $e) {
        // Handle potential exceptions
        $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }

    // Return the JSON response
    echo json_encode($response);
}

// Function to get all users' balances
function get_all_balances($pdo) {
    try {
        // Prepare and execute the SQL statement
        $stmt = $pdo->prepare("SELECT id, balance FROM parkings");
        $stmt->execute();
        $balances = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare the response
        $response = ['success' => true, 'balances' => $balances];
    } catch (PDOException $e) {
        // Handle potential exceptions
        $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }

    // Return the JSON response
    echo json_encode($response);
}

// Function to get the total sales for the current month
function current_month_sales($pdo) {
    try {
        // Prepare and execute the SQL statement
        $stmt = $pdo->prepare("
            SELECT SUM(paid) as total_sales_month 
            FROM parkings 
            WHERE MONTH(time_out) = MONTH(CURDATE()) 
              AND YEAR(time_out) = YEAR(CURDATE())
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if result is null and set it to 0 if necessary
        $total_sales_month = $result['total_sales_month'] ?? 0;

        $response = ['success' => true, 'total_sales_month' => $total_sales_month];
    } catch (PDOException $e) {
        // Handle potential exceptions
        $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }

    echo json_encode($response);
}


// Function to get the history of parking for a given plate number
function get_history($pdo, $plate) {
    if (!empty($plate)) {
        // Prepare and execute the SQL statement
        $stmt = $pdo->prepare("SELECT * FROM parkings WHERE plate = ? ORDER BY time_in DESC");
        $stmt->execute([$plate]);
        $parkingHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($parkingHistory) {
            $response = ['success' => true, 'parking_history' => $parkingHistory];
        } else {
            $response = ['success' => false, 'message' => 'No parking history found for this plate'];
        }
    } else {
        $response = ['success' => false, 'errors' => ['plate' => 'Plate number is required']];
    }

    echo json_encode($response);
}

function dashboard($pdo) {
    // Fetch total cars registered
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_cars FROM users WHERE user_type = 'user'");
    $stmt->execute();
    $total_cars = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch total parkings today
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_parkings FROM parkings WHERE DATE(time_in) = CURDATE()");
    $stmt->execute();
    $total_parkings = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch total sales in current month
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(paid), 0) as total_sales_month FROM parkings WHERE MONTH(time_in) = MONTH(CURDATE()) AND YEAR(time_in) = YEAR(CURDATE())");
    $stmt->execute();
    $total_sales_month = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch profit today
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(paid), 0) as profit_today FROM parkings WHERE DATE(time_in) = CURDATE()");
    $stmt->execute();
    $profit_today = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch total parkings for the year
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_parkings_year FROM parkings WHERE YEAR(time_in) = YEAR(CURDATE())");
    $stmt->execute();
    $total_parkings_year = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch monthly sales data
    $stmt = $pdo->prepare("SELECT MONTH(time_in) as month, COALESCE(SUM(paid), 0) as monthly_sales FROM parkings WHERE YEAR(time_in) = YEAR(CURDATE()) GROUP BY MONTH(time_in)");
    $stmt->execute();
    $monthly_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch monthly parkings count
    $stmt = $pdo->prepare("SELECT MONTH(time_in) as month, COUNT(*) as parkings_count FROM parkings WHERE YEAR(time_in) = YEAR(CURDATE()) GROUP BY MONTH(time_in)");
    $stmt->execute();
    $monthly_parkings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Map month numbers to month names
    $month_names = [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December'
    ];

    // Prepare the monthly parkings data for display with month names
    $monthly_parkings_data = [];
    foreach ($month_names as $month_number => $month_name) {
        $monthly_parkings_data[$month_name] = 0; // Initialize each month's parkings count to 0
    }

    foreach ($monthly_parkings as $parking) {
        $month_number = $parking['month'];
        $month_name = $month_names[$month_number];
        $monthly_parkings_data[$month_name] = $parking['parkings_count'];
    }

    // Prepare the monthly sales data for display with month names
    $monthly_sales_data = [];
    foreach ($month_names as $month_number => $month_name) {
        $monthly_sales_data[$month_name] = 0; // Initialize each month's sales to 0
    }

    foreach ($monthly_sales as $sale) {
        $month_number = $sale['month'];
        $month_name = $month_names[$month_number];
        $monthly_sales_data[$month_name] = $sale['monthly_sales'];
    }

    // Fetch all parkings
    $stmt = $pdo->prepare("SELECT * FROM parkings ORDER BY id DESC");
    $stmt->execute();
    $all_parkings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare and echo the JSON response
    $response = [
        'success' => true,
        'total_cars_registered' => $total_cars['total_cars'],
        'total_parkings_today' => $total_parkings['total_parkings'],
        'total_sales' => $total_sales_month['total_sales_month'],
        'profit_today' => $profit_today['profit_today'],
        'total_parkings_year' => $total_parkings_year['total_parkings_year'],
        'yearly_sales' => $monthly_sales_data,
        'yearly_parkings' => $monthly_parkings_data,
        'all_parkings' => $all_parkings
    ];

    echo json_encode($response);
}




?>

