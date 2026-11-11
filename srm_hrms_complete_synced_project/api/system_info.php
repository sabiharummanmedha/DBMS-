<?php
require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

try {
    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT * FROM SystemInfo WHERE id = 1 LIMIT 1");
        $info = $stmt->fetch();

        if (!$info) {
            $info = [
                'company_name' => 'SRM HRMS',
                'admin_name'   => 'Sabiha Rumman Medha',
                'admin_role'   => 'Head of HR & Admin',
                'notice_text'  => 'Head of HR: Sabiha Rumman Medha • November 2026 workforce records and attendance tracking are actively operational.'
            ];
        }

        echo json_encode(["status" => "success", "data" => $info]);
        exit();
    }

    if ($method === 'POST') {
        $companyName = trim($input['company_name'] ?? 'SRM HRMS');
        $adminName   = trim($input['admin_name'] ?? 'Sabiha Rumman Medha');
        $adminRole   = trim($input['admin_role'] ?? 'Head of HR & Admin');
        $notice      = trim($input['notice_text'] ?? '');

        $stmt = $pdo->prepare("
            INSERT INTO SystemInfo (id, company_name, admin_name, admin_role, notice_text)
            VALUES (1, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                company_name = VALUES(company_name),
                admin_name = VALUES(admin_name),
                admin_role = VALUES(admin_role),
                notice_text = VALUES(notice_text)
        ");
        $stmt->execute([$companyName, $adminName, $adminRole, $notice]);

        echo json_encode(["status" => "success", "message" => "System information updated successfully."]);
        exit();
    }

    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
