<?php
include_once("db.php");

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {

    $firstName = $conn->real_escape_string($data['firstName']);
    $lastName  = $conn->real_escape_string($data['lastName']);
    $number    = $conn->real_escape_string($data['number']);
    $email     = $conn->real_escape_string($data['email']);
    $address   = $conn->real_escape_string($data['textarea']); 
    $gender    = $conn->real_escape_string($data['gender']);
    $district  = $conn->real_escape_string($data['district']);


    $query = "INSERT INTO users (first_name, last_name, phone_number, email, address, gender, district) 
              VALUES ('$firstName', '$lastName', '$number', '$email', '$address', '$gender', '$district')";
    

    if ($conn->query($query)) {

        if ($conn->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Operation completed successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "No rows inserted."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "SQL Error", "error" => $conn->error]);
    }
}
?>
