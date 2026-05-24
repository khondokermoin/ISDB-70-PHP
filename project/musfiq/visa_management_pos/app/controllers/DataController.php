<?php
// app/controllers/DataController.php
session_start();
require_once '../config/database.php';
require_once '../models/Candidate.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $candidateModel = new Candidate($conn);
    $action = $_POST['action'] ?? '';

    // Delete Logic
    if ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if ($candidateModel->deleteCandidate($id)) {
            echo json_encode(["status" => "success", "message" => "Deleted successfully!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete!"]);
        }
        exit();
    }

    // Save or Update Logic
    if ($action === 'save') {
        $id = $_POST['id'] ?? '';

        $data = [
            'id'                 => $id,
            'interview_no'       => trim($_POST['interview_no'] ?? ''),
            'full_name'          => trim($_POST['full_name'] ?? ''),
            'passport_no'        => trim($_POST['passport_no'] ?? ''),
            'dob'                => !empty($_POST['dob']) ? $_POST['dob'] : null,
            'age'                => !empty($_POST['age']) ? $_POST['age'] : null,
            'pp_expire_date'     => !empty($_POST['pp_expire_date']) ? $_POST['pp_expire_date'] : null,
            'district'           => trim($_POST['district'] ?? ''),
            'trade'              => trim($_POST['trade'] ?? ''),
            'reference_name'     => trim($_POST['reference_name'] ?? ''),
            'phone'              => trim($_POST['phone'] ?? ''),
            'medical_status'     => trim($_POST['medical_status'] ?? ''),
            'pc_status'          => trim($_POST['pc_status'] ?? ''),
            'photo_status'       => trim($_POST['photo_status'] ?? ''),
            'application_status' => trim($_POST['application_status'] ?? 'NEW'),
            'apply_date'         => !empty($_POST['apply_date']) ? $_POST['apply_date'] : null
        ];

        if (empty($id)) {
            $result = $candidateModel->createCandidate($data); // Create New
        } else {
            $result = $candidateModel->updateCandidate($data); // Update Existing
        }

        if ($result) {
            echo json_encode(["status" => "success", "message" => "Saved successfully!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database Error! Check values."]);
        }
    }
}
