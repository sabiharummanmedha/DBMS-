<?php
require_once __DIR__ . '/../config/db.php';

try {
    $stmt = $pdo->query("
        SELECT 
            e.employee_id,
            e.employee_code,
            e.employee_name,
            e.designation,
            d.department_name,
            s.basic_salary,
            s.bonus,
            (s.basic_salary + s.bonus) as gross_salary,
            ROUND((s.basic_salary + s.bonus) * 0.05, 2) as deduction,
            ROUND((s.basic_salary + s.bonus) - ((s.basic_salary + s.bonus) * 0.05), 2) as net_payout
        FROM Employee e
        JOIN Department d ON e.department_id = d.department_id
        JOIN Salary s ON e.employee_id = s.employee_id
        WHERE e.status != 'Former'
        ORDER BY gross_salary DESC
    ");
    $payrollRows = $stmt->fetchAll();

    $totalPayroll = 0;
    $totalBonus = 0;
    $topComp = 0;

    foreach ($payrollRows as $r) {
        $totalPayroll += (float) $r['gross_salary'];
        $totalBonus += (float) $r['bonus'];
        if ((float) $r['net_payout'] > $topComp) {
            $topComp = (float) $r['net_payout'];
        }
    }

    $avgSalary = count($payrollRows) > 0 ? ($totalPayroll / count($payrollRows)) : 0;

    echo json_encode([
        "status" => "success",
        "month" => "November 2026",
        "summary" => [
            "total_monthly_payroll" => $totalPayroll,
            "average_base_salary"   => round($avgSalary, 2),
            "total_bonuses"         => $totalBonus,
            "top_compensation"      => $topComp,
            "total_staff_count"     => count($payrollRows)
        ],
        "data" => $payrollRows
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
