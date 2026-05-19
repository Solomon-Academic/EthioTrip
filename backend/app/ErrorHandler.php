<?php
namespace App;

use App\Exceptions\AppException;

class ErrorHandler {
    private static $logDir = __DIR__ . '/../../logs/';

    public static function register() {
        // Create logs directory if it doesn't exist
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }

        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleException(\Throwable $exception) {
        $isAppException = $exception instanceof AppException;

        // Log the error
        self::log($exception);

        // Set HTTP response code
        http_response_code($isAppException ? 400 : 500);

        // Determine if we should show user message
        $isAPI = isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false;

        if ($isAPI) {
            header('Content-Type: application/json');
            if ($isAppException) {
                echo json_encode($exception->toArray());
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'An error occurred',
                    'code' => 'SERVER_ERROR'
                ]);
            }
        } else {
            // Set flash message for HTML pages
            session_start();
            if ($isAppException) {
                $_SESSION['error_message'] = $exception->getUserMessage();
            } else {
                $_SESSION['error_message'] = 'An unexpected error occurred. Please try again.';
            }

            // Redirect to error page
            header('Location: /backend/error.php');
        }

        exit;
    }

    public static function handleError($severity, $message, $file, $line) {
        // Convert errors to exceptions
        if (!(error_reporting() & $severity)) {
            return;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public static function handleShutdown() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::log(new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
        }
    }

    public static function log(\Throwable $exception) {
        $logFile = self::$logDir . date('Y-m-d') . '.log';

        $logMessage = sprintf(
            "[%s] %s: %s in %s:%d\nStack trace: %s\n%s\n",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString(),
            str_repeat('-', 80)
        );

        error_log($logMessage, 3, $logFile);
    }

    public static function logInfo($message) {
        $logFile = self::$logDir . date('Y-m-d') . '.log';
        $logMessage = sprintf("[%s] INFO: %s\n", date('Y-m-d H:i:s'), $message);
        error_log($logMessage, 3, $logFile);
    }
}
?>
