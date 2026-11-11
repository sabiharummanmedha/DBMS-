<?php
require_once __DIR__ . '/../config/db.php';

// Check if CSV download requested
$exportCsv = isset($_GET['export']) && $_GET['export'] === 'csv';

try {
    // Exact SQL Query for PDF Question 1 Specification
    $sql = "
        SELECT 
            e.employee_name,
            d.department_name,
            e.email,
            s.basic_salary,
            s.bonus,
            COALESCE(a.attendance_date, CURDATE()) as attendance_date,
            COALESCE(a.status, 'Present') as status
        FROM Employee e
        JOIN Department d ON e.department_id = d.department_id
        JOIN Salary s ON e.employee_id = s.employee_id
        LEFT JOIN Attendance a ON e.employee_id = a.employee_id AND a.attendance_date = CURDATE()
        WHERE e.status != 'Former'
        ORDER BY e.employee_id ASC
    ";

    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll();

    if ($exportCsv) {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="srm_hr_master_report_q1.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['employee_name', 'department_name', 'email', 'basic_salary', 'bonus', 'attendance_date', 'status']);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['employee_name'],
                $row['department_name'],
                $row['email'],
                $row['basic_salary'],
                $row['bonus'],
                $row['attendance_date'],
                $row['status']
            ]);
        }
        fclose($out);
        exit();
    }

    echo json_encode([
        "status" => "success",
        "query_spec" => "Q1: (employee_name, department_name, email, basic_salary, bonus, attendance_date, status)",
        "count" => count($rows),
        "data" => $rows
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
