<?php
require_once __DIR__ . '/../config/db.php';

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

try {
    // 1. Total Active Employees
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Employee WHERE status != 'Former'");
    $totalEmployees = (int) $stmt->fetch()['total'];

    // 2. Total Departments
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Department");
    $totalDepartments = (int) $stmt->fetch()['total'];

    // 3. Total Monthly Payroll (active staff only)
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(s.basic_salary + s.bonus), 0) as total_payroll
        FROM Salary s
        JOIN Employee e ON s.employee_id = e.employee_id
        WHERE e.status != 'Former'
    ");
    $totalPayroll = (float) $stmt->fetch()['total_payroll'];

    // 4. Attendance Statistics for given date
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) as present_count,
            SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
            SUM(CASE WHEN a.status = 'Leave' THEN 1 ELSE 0 END) as leave_count
        FROM Attendance a
        JOIN Employee e ON a.employee_id = e.employee_id
        WHERE a.attendance_date = ? AND e.status != 'Former'
    ");
    $stmt->execute([$date]);
    $attStats = $stmt->fetch();

    $presentCount = (int) ($attStats['present_count'] ?? 0);
    $absentCount  = (int) ($attStats['absent_count'] ?? 0);
    $leaveCount   = (int) ($attStats['leave_count'] ?? 0);
    $attRate      = $totalEmployees > 0 ? round(($presentCount / $totalEmployees) * 100) : 0;

    // 5. Department Overview (Headcount & Monthly Payroll - NO budget)
    $stmt = $pdo->query("
        SELECT 
            d.department_id,
            d.department_name,
            d.department_code,
            d.office_location,
            COUNT(e.employee_id) as headcount,
            COALESCE(SUM(s.basic_salary + s.bonus), 0) as monthly_payroll
        FROM Department d
        LEFT JOIN Employee e ON d.department_id = e.department_id AND e.status != 'Former'
        LEFT JOIN Salary s ON e.employee_id = s.employee_id
        GROUP BY d.department_id, d.department_name, d.department_code, d.office_location
        ORDER BY d.department_id ASC
    ");
    $departments = $stmt->fetchAll();

    // 6. Former staff count
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Employee WHERE status = 'Former'");
    $formerCount = (int) $stmt->fetch()['total'];

    echo json_encode([
        "status" => "success",
        "data" => [
            "total_employees"   => $totalEmployees,
            "former_count"      => $formerCount,
            "total_departments" => $totalDepartments,
            "monthly_payroll"   => $totalPayroll,
            "attendance_rate"   => $attRate,
            "present_count"     => $presentCount,
            "absent_count"      => $absentCount,
            "leave_count"       => $leaveCount,
            "department_overview" => $departments
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
