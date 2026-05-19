<?php
namespace App\Models;

class User {
    private $id;
    private $name;
    private $email;
    private $phone;
    private $password;
    private $role;
    private $trips_completed;
    private $total_spent;
    private $loyalty_discount;
    private $created_at;

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public static function findById($id) {
        $result = Database::query(
            "SELECT id, name, email, phone, role, trips_completed, total_spent, loyalty_discount, created_at FROM users WHERE id = ?",
            "i",
            [$id]
        );

        if ($row = mysqli_fetch_assoc($result)) {
            return new self($row);
        }
        return null;
    }

    public static function findByEmail($email) {
        $result = Database::query(
            "SELECT id, name, email, password, role, trips_completed, total_spent, loyalty_discount FROM users WHERE email = ?",
            "s",
            [$email]
        );

        if ($row = mysqli_fetch_assoc($result)) {
            return new self($row);
        }
        return null;
    }

    public static function create($name, $email, $phone, $password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        return Database::execute(
            "INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'user')",
            "ssss",
            [$name, $email, $phone, $hashed]
        );
    }

    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }

    public function updateStats($trips, $spent, $discount) {
        return Database::execute(
            "UPDATE users SET trips_completed = ?, total_spent = ?, loyalty_discount = ? WHERE id = ?",
            "iddi",
            [$trips, $spent, $discount, $this->id]
        );
    }

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getEmail() { return $this->email; }
    public function getPhone() { return $this->phone; }
    public function getRole() { return $this->role; }
    public function getTripsCompleted() { return $this->trips_completed; }
    public function getTotalSpent() { return $this->total_spent; }
    public function getLoyaltyDiscount() { return $this->loyalty_discount; }
    public function getCreatedAt() { return $this->created_at; }

    // Setters
    public function setName($name) { $this->name = $name; return $this; }
    public function setEmail($email) { $this->email = $email; return $this; }
    public function setPhone($phone) { $this->phone = $phone; return $this; }
    public function setRole($role) { $this->role = $role; return $this; }
}
?>
