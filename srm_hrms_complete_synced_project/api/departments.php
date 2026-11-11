<?php
require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

try {
    if ($method === 'GET') {
        $stmt = $pdo->query("
            SELECT 
                d.department_id,
                d.department_name,
                d.department_code,
                d.office_location,
                COUNT(e.employee_id) as headcount
            FROM Department d
            LEFT JOIN Employee e ON d.department_id = e.department_id AND e.status != 'Former'
            GROUP BY d.department_id, d.department_name, d.department_code, d.office_location
            ORDER BY d.department_id ASC
        ");
        $departments = $stmt->fetchAll();

        echo json_encode(["status" => "success", "data" => $departments]);
        exit();
    }

    if ($method === 'POST') {
        $name     = trim($input['department_name'] ?? '');
        $code     = trim($input['department_code'] ?? '');
        $location = trim($input['office_location'] ?? '');

        if (empty($name) || empty($code)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Department name and code are required."]);
            exit();
        }

        $stmt = $pdo->prepare("INSERT INTO Department (department_name, department_code, office_location) VALUES (?, ?, ?)");
        $stmt->execute([$name, $code, $location]);

        echo json_encode([
            "status" => "success",
            "message" => "Department created successfully.",
            "department_id" => $pdo->lastInsertId()
        ]);
        exit();
    }

    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
