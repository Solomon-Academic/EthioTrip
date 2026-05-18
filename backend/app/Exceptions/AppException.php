<?php
namespace App\Exceptions;

class AppException extends \Exception {
    protected $userMessage;
    protected $errorCode;

    public function __construct($message, $userMessage = null, $code = 0) {
        $this->userMessage = $userMessage ?? $message;
        $this->errorCode = $code ?: 'APP_ERROR';
        parent::__construct($message, 0, null);
    }

    public function getUserMessage() {
        return $this->userMessage;
    }

    public function getErrorCode() {
        return $this->errorCode;
    }

    public function toArray() {
        return [
            'success' => false,
            'error' => $this->userMessage,
            'code' => $this->errorCode
        ];
    }
}

class ValidationException extends AppException {
    protected $errors = [];

    public function __construct($errors = [], $message = 'Validation failed') {
        $this->errors = $errors;
        parent::__construct($message, $message, 'VALIDATION_ERROR');
    }

    public function getErrors() {
        return $this->errors;
    }

    public function toArray() {
        return [
            'success' => false,
            'message' => $this->userMessage,
            'errors' => $this->errors,
            'code' => 'VALIDATION_ERROR'
        ];
    }
}

class DatabaseException extends AppException {
    public function __construct($message, $userMessage = 'Database error occurred') {
        parent::__construct($message, $userMessage, 'DATABASE_ERROR');
    }
}

class AuthException extends AppException {
    public function __construct($message = 'Authentication failed', $code = 'AUTH_ERROR') {
        parent::__construct($message, $message, $code);
    }
}

class NotFoundException extends AppException {
    public function __construct($resource = 'Resource', $code = 'NOT_FOUND') {
        $message = "$resource not found";
        parent::__construct($message, $message, $code);
    }
}
?>
