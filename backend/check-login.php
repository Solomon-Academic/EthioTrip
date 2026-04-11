<?php
session_start();
header('Content-Type: application/json');

$response = ['logged_in' => false];
if (isset($_SESSION['user_id'])) {
    $response['logged_in'] = true;
    $response['user_id'] = $_SESSION['user_id'];
    $response['user_name'] = $_SESSION['user_name'];
}

echo json_encode($response);
?>