<?php
include_once("db.php");

// Get JSON data from React Axios
$data = json_decode(file_get_contents("php://input"), true);

if ($data) {

    // Extract data
    $firstName = $data['firstName'];
    $lastName  = $data['lastName'];
    $number    = $data['number'];
    $email     = $data['email'];
    $address   = $data['textarea'];
    $gender    = $data['gender'];
    $district  = $data['district'];

    // Prepared INSERT query
    $query = "INSERT INTO users
              (first_name, last_name, phone_number, email, address, gender, district)
              VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Prepare failed.",
            "error" => $conn->error
        ]);
        exit;
    }

    $stmt->bind_param(
        "sssssss",
        $firstName,
        $lastName,
        $number,
        $email,
        $address,
        $gender,
        $district
    );

    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Data inserted successfully!"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "No row inserted.",
            "error" => $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode([
        "success" => false,
        "message" => "No data received."
    ]);
}
