<?php
// app/models/Candidate.php

class Candidate
{
    private PDO $conn; // Type specified as PDO
    private string $table = 'candidates'; // Type specified as string

    public function __construct(PDO $db) // $db Type specified as PDO
    {
        $this->conn = $db;
    }

    public function getAllCandidates(): array // Return type specified as array
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createCandidate(array $data): bool // $data as array, return type bool
    {
        $query = "INSERT INTO " . $this->table . " 
                  (interview_no, full_name, passport_no, dob, age, pp_expire_date, district, trade, reference_name, phone, medical_status, pc_status, photo_status, application_status, apply_date) 
                  VALUES (:interview_no, :full_name, :passport_no, :dob, :age, :pp_expire_date, :district, :trade, :reference_name, :phone, :medical_status, :pc_status, :photo_status, :application_status, :apply_date)";

        $stmt = $this->conn->prepare($query);
        try {
            return $stmt->execute($this->bindData($data));
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateCandidate(array $data): bool // $data as array, return type bool
    {
        $query = "UPDATE " . $this->table . " SET 
                  interview_no=:interview_no, full_name=:full_name, passport_no=:passport_no, dob=:dob, age=:age, pp_expire_date=:pp_expire_date, district=:district, trade=:trade, reference_name=:reference_name, phone=:phone, medical_status=:medical_status, pc_status=:pc_status, photo_status=:photo_status, application_status=:application_status, apply_date=:apply_date 
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $bind = $this->bindData($data);
        $bind[':id'] = $data['id']; // ID for update

        try {
            return $stmt->execute($bind);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteCandidate(int $id): bool // $id type specified as integer
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        try {
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // ডাটা বাইন্ড করার জন্য হেল্পার ফাংশন
    private function bindData(array $data): array // $data and return type specified as array
    {
        return [
            ':interview_no' => $data['interview_no'],
            ':full_name' => $data['full_name'],
            ':passport_no' => $data['passport_no'],
            ':dob' => $data['dob'],
            ':age' => $data['age'],
            ':pp_expire_date' => $data['pp_expire_date'],
            ':district' => $data['district'],
            ':trade' => $data['trade'],
            ':reference_name' => $data['reference_name'],
            ':phone' => $data['phone'],
            ':medical_status' => $data['medical_status'],
            ':pc_status' => $data['pc_status'],
            ':photo_status' => $data['photo_status'],
            ':application_status' => $data['application_status'],
            ':apply_date' => $data['apply_date']
        ];
    }
}
