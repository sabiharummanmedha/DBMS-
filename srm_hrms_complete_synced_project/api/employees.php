<?php
require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

// Parse incoming JSON body if present
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = isset($_GET['action']) ? $_GET['action'] : ($input['action'] ?? '');

try {
    // =========================================================================
    // GET: List Employees (Active or Former) with Salary & Department Info
    // =========================================================================
    if ($method === 'GET') {
        $type = isset($_GET['type']) ? $_GET['type'] : 'active'; // 'active' or 'former'
        $dept = isset($_GET['department']) ? $_GET['department'] : '';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        $sql = "
            SELECT 
                e.employee_id,
                e.employee_code,
                e.employee_name,
                e.designation,
                e.email,
                e.phone,
                e.joining_date,
                e.status,
                e.separation_date,
                e.separation_reason,
                e.separation_notes,
                d.department_id,
                d.department_name,
                d.department_code,
                COALESCE(s.basic_salary, 0) as basic_salary,
                COALESCE(s.bonus, 0) as bonus,
                (COALESCE(s.basic_salary, 0) + COALESCE(s.bonus, 0)) as gross_salary,
                ROUND((COALESCE(s.basic_salary, 0) + COALESCE(s.bonus, 0)) * 0.05, 2) as deduction,
                ((COALESCE(s.basic_salary, 0) + COALESCE(s.bonus, 0)) - ROUND((COALESCE(s.basic_salary, 0) + COALESCE(s.bonus, 0)) * 0.05, 2)) as net_payout,
                COALESCE(a.status, 'Present') as today_attendance
            FROM Employee e
            JOIN Department d ON e.department_id = d.department_id
            LEFT JOIN Salary s ON e.employee_id = s.employee_id
            LEFT JOIN Attendance a ON e.employee_id = a.employee_id AND a.attendance_date = CURDATE()
            WHERE 1=1
        ";

        $params = [];

        if ($type === 'former') {
            $sql .= " AND e.status = 'Former'";
        } else {
            $sql .= " AND e.status != 'Former'";
        }

        if (!empty($dept) && $dept !== 'ALL') {
            $sql .= " AND d.department_name = ?";
            $params[] = $dept;
        }

        if (!empty($search)) {
            $sql .= " AND (e.employee_name LIKE ? OR e.designation LIKE ? OR e.email LIKE ? OR e.employee_code LIKE ?)";
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY e.employee_id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        echo json_encode(["status" => "success", "count" => count($rows), "data" => $rows]);
        exit();
    }

    // =========================================================================
    // POST / PUT: Add New Employee
    // =========================================================================
    if ($action === 'add' || ($method === 'POST' && empty($action) && isset($input['employee_name']))) {
        $name    = trim($input['employee_name'] ?? '');
        $code    = trim($input['employee_code'] ?? '#1' . rand(19, 99));
        $deptId  = (int) ($input['department_id'] ?? 1);
        $title   = trim($input['designation'] ?? '');
        $email   = trim($input['email'] ?? '');
        $phone   = trim($input['phone'] ?? '+880 1700-000000');
        $joining = !empty($input['joining_date']) ? $input['joining_date'] : date('Y-m-d');
        $status  = in_array($input['status'] ?? '', ['Active', 'Probation', 'On Leave']) ? $input['status'] : 'Active';
        $basic   = (float) ($input['basic_salary'] ?? 50000);
        $bonus   = (float) ($input['bonus'] ?? 0);

        if (empty($name) || empty($email) || empty($title)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Name, Designation, and Email are required."]);
            exit();
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO Employee (employee_code, employee_name, department_id, designation, email, phone, joining_date, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$code, $name, $deptId, $title, $email, $phone, $joining, $status]);
        $empId = (int) $pdo->lastInsertId();

        // Insert Salary Record
        $stmt = $pdo->prepare("INSERT INTO Salary (employee_id, basic_salary, bonus) VALUES (?, ?, ?)");
        $stmt->execute([$empId, $basic, $bonus]);

        // Insert initial attendance for today
        $stmt = $pdo->prepare("INSERT INTO Attendance (employee_id, attendance_date, status) VALUES (?, CURDATE(), 'Present') ON DUPLICATE KEY UPDATE status='Present'");
        $stmt->execute([$empId]);

        $pdo->commit();

        echo json_encode([
            "status" => "success",
            "message" => "Employee registered successfully",
            "employee_id" => $empId
        ]);
        exit();
    }

    // =========================================================================
    // POST: Update Details & Promotion (Salary, Title, Department)
    // =========================================================================
    if ($action === 'update') {
        $empId  = (int) ($input['employee_id'] ?? 0);
        $name   = trim($input['employee_name'] ?? '');
        $title  = trim($input['designation'] ?? '');
        $deptId = (int) ($input['department_id'] ?? 0);
        $basic  = (float) ($input['basic_salary'] ?? 0);
        $bonus  = (float) ($input['bonus'] ?? 0);
        $email  = trim($input['email'] ?? '');
        $phone  = trim($input['phone'] ?? '');

        if ($empId <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid employee ID"]);
            exit();
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            UPDATE Employee 
            SET employee_name = ?, designation = ?, department_id = ?, email = ?, phone = ?
            WHERE employee_id = ?
        ");
        $stmt->execute([$name, $title, $deptId, $email, $phone, $empId]);

        $stmt = $pdo->prepare("
            UPDATE Salary 
            SET basic_salary = ?, bonus = ?
            WHERE employee_id = ?
        ");
        $stmt->execute([$basic, $bonus, $empId]);

        $pdo->commit();

        echo json_encode(["status" => "success", "message" => "Employee details and compensation updated successfully."]);
        exit();
    }

    // =========================================================================
    // POST: Offboard / Resignation (Preserve into Former Staff Archive)
    // =========================================================================
    if ($action === 'offboard') {
        $empId   = (int) ($input['employee_id'] ?? 0);
        $date    = !empty($input['separation_date']) ? $input['separation_date'] : date('Y-m-d');
        $reason  = trim($input['separation_reason'] ?? 'Voluntary Resignation');
        $notes   = trim($input['separation_notes'] ?? '');

        if ($empId <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid employee ID"]);
            exit();
        }

        $stmt = $pdo->prepare("
            UPDATE Employee 
            SET status = 'Former', separation_date = ?, separation_reason = ?, separation_notes = ?
            WHERE employee_id = ?
        ");
        $stmt->execute([$date, $reason, $notes, $empId]);

        echo json_encode(["status" => "success", "message" => "Employee offboarded and preserved under Former Employee Archive."]);
        exit();
    }

    // =========================================================================
    // POST: Rehire / Restore Former Employee
    // =========================================================================
    if ($action === 'rehire') {
        $empId = (int) ($input['employee_id'] ?? 0);

        if ($empId <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid employee ID"]);
            exit();
        }

        $stmt = $pdo->prepare("
            UPDATE Employee 
            SET status = 'Active', separation_date = NULL, separation_reason = NULL, separation_notes = NULL
            WHERE employee_id = ?
        ");
        $stmt->execute([$empId]);

        echo json_encode(["status" => "success", "message" => "Employee restored to Active Workforce."]);
        exit();
    }

    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Unrecognized action or method."]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
