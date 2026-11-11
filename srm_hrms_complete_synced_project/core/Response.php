<?php
// SRM HRMS - Standardized API Response Handler
class Response {
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    public static function success($message, $data = [], $statusCode = 200) {
        self::json([
            "status"  => "success",
            "message" => $message,
            "data"    => $data
        ], $statusCode);
    }

    public static function error($message, $statusCode = 400, $details = null) {
        self::json([
            "status"  => "error",
            "message" => $message,
            "details" => $details
        ], $statusCode);
    }
}
?>