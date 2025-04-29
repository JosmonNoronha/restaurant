<?php
header('Content-Type: application/json');

require_once 'config/db.php'; // Include database connection

$response = ['success' => false, 'data' => [], 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $filterName = $connection->real_escape_string($_POST['filter_name'] ?? '');
    $filterBookingId = (int)($_POST['filter_booking_id'] ?? 0);

    if (!empty($filterName) && $filterBookingId > 0) {
        $sql = "SELECT * FROM bookings WHERE name='$filterName' AND id='$filterBookingId'";
        $result = $connection->query($sql);

        if ($result && $result->num_rows > 0) {
            $booking = $result->fetch_assoc();
            $response['success'] = true;
            $response['data'] = $booking;
        } else {
            $response['message'] = "No matching booking found for the provided name and Booking Number.";
        }
    } else {
        $response['message'] = "Please provide both name and a valid Booking Number.";
    }
} else {
    $response['message'] = "Invalid request method.";
}

$connection->close();
echo json_encode($response);
?>