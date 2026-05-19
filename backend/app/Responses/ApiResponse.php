<?php
namespace App\Responses;

class ApiResponse {

    public static function success($data = [], $message = 'Success', $code = 200) {
        return self::send(true, $message, $data, $code);
    }

    public static function error($message = 'Error', $code = 400, $data = []) {
        return self::send(false, $message, $data, $code);
    }

    public static function validationError($errors, $message = 'Validation failed', $code = 422) {
        return self::send(false, $message, ['errors' => $errors], $code);
    }

    public static function notFound($message = 'Resource not found', $code = 404) {
        return self::send(false, $message, [], $code);
    }

    public static function unauthorized($message = 'Unauthorized', $code = 401) {
        return self::send(false, $message, [], $code);
    }

    public static function forbidden($message = 'Forbidden', $code = 403) {
        return self::send(false, $message, [], $code);
    }

    public static function serverError($message = 'Server error', $code = 500) {
        return self::send(false, $message, [], $code);
    }

    private static function send($success, $message, $data, $code) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $response = [
            'success' => $success,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'code' => $code
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function json($data) {
        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function paginated($items, $currentPage, $totalPages, $total, $message = 'Success') {
        return self::success([
            'items' => $items,
            'pagination' => [
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
                'total_items' => $total,
                'per_page' => count($items)
            ]
        ], $message);
    }
}
?>
