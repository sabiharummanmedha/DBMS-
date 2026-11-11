<?php
require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = isset($_GET['action']) ? $_GET['action'] : ($input['action'] ?? '');

try {
    if ($method === 'GET') {
        $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

        $stmt = $pdo->prepare("
            SELECT 
                e.employee_id,
                e.employee_code,
                e.employee_name,
                e.designation,
                d.department_name,
                COALESCE(a.status, 'Present') as status,
                COALESCE(a.attendance_date, ?) as attendance_date
            FROM Employee e
            JOIN Department d ON e.department_id = d.department_id
            LEFT JOIN Attendance a ON e.employee_id = a.employee_id AND a.attendance_date = ?
            WHERE e.status != 'Former'
            ORDER BY e.employee_id ASC
        ");
        $stmt->execute([$date, $date]);
        $rows = $stmt->fetchAll();

        echo json_encode(["status" => "success", "date" => $date, "data" => $rows]);
        exit();
    }

    if ($method === 'POST') {
        $date = !empty($input['attendance_date']) ? $input['attendance_date'] : date('Y-m-d');

        // Toggle single employee attendance status
        if ($action === 'toggle' || isset($input['employee_id'])) {
            $empId = (int) ($input['employee_id'] ?? 0);
            $status = in_array($input['status'] ?? '', ['Present', 'Absent', 'Leave']) ? $input['status'] : 'Present';

            $stmt = $pdo->prepare("
                INSERT INTO Attendance (employee_id, attendance_date, status)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE status = ?
            ");
            $stmt->execute([$empId, $date, $status, $status]);

            echo json_encode(["status" => "success", "message" => "Attendance updated."]);
            exit();
        }

        // Mark all active employees present
        if ($action === 'mark_all') {
            $status = in_array($input['status'] ?? '', ['Present', 'Absent', 'Leave']) ? $input['status'] : 'Present';

            $stmt = $pdo->query("SELECT employee_id FROM Employee WHERE status != 'Former'");
            $employees = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $stmt = $pdo->prepare("
                INSERT INTO Attendance (employee_id, attendance_date, status)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE status = ?
            ");

            $pdo->beginTransaction();
            foreach ($employees as $empId) {
                $stmt->execute([$empId, $date, $status, $status]);
            }
            $pdo->commit();

            echo json_encode(["status" => "success", "message" => "All active staff marked as {$status}."]);
            exit();
        }
    }

    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
