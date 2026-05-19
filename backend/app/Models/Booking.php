<?php
namespace App\Models;

class Booking {
    private $id;
    private $user_id;
    private $package_id;
    private $package_name;
    private $start_date;
    private $end_date;
    private $duration_days;
    private $number_of_travelers;
    private $total_amount;
    private $discount_amount;
    private $final_amount;
    private $special_requests;
    private $status;
    private $payment_status;
    private $created_at;

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public static function create($user_id, $package_id, $package_name, $start_date, $end_date,
                                   $duration_days, $travelers, $subtotal, $discount, $total, $requests) {
        return Database::execute(
            "INSERT INTO bookings (user_id, package_id, package_name, start_date, end_date, duration_days,
                                   number_of_travelers, total_amount, discount_amount, final_amount,
                                   special_requests, status, payment_status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')",
            "iisssiiddds",
            [$user_id, $package_id, $package_name, $start_date, $end_date, $duration_days,
             $travelers, $subtotal, $discount, $total, $requests]
        );
    }

    public static function getUserBookings($user_id) {
        return Database::query(
            "SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC",
            "i",
            [$user_id]
        );
    }

    public static function findById($id, $user_id = null) {
        if ($user_id) {
            $result = Database::query(
                "SELECT * FROM bookings WHERE id = ? AND user_id = ?",
                "ii",
                [$id, $user_id]
            );
        } else {
            $result = Database::query(
                "SELECT * FROM bookings WHERE id = ?",
                "i",
                [$id]
            );
        }

        if ($row = mysqli_fetch_assoc($result)) {
            return new self($row);
        }
        return null;
    }

    public function update($start_date, $end_date, $duration_days, $travelers, $requests) {
        return Database::execute(
            "UPDATE bookings SET start_date = ?, end_date = ?, duration_days = ?,
                                 number_of_travelers = ?, special_requests = ?
             WHERE id = ? AND user_id = ?",
            "ssiisii",
            [$start_date, $end_date, $duration_days, $travelers, $requests, $this->id, $this->user_id]
        );
    }

    public function delete() {
        return Database::execute(
            "DELETE FROM bookings WHERE id = ? AND user_id = ?",
            "ii",
            [$this->id, $this->user_id]
        );
    }

    public function updateStatus($status, $payment_status = null) {
        if ($payment_status) {
            return Database::execute(
                "UPDATE bookings SET status = ?, payment_status = ? WHERE id = ?",
                "ssi",
                [$status, $payment_status, $this->id]
            );
        }
        return Database::execute(
            "UPDATE bookings SET status = ? WHERE id = ?",
            "si",
            [$status, $this->id]
        );
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getPackageId() { return $this->package_id; }
    public function getPackageName() { return $this->package_name; }
    public function getStartDate() { return $this->start_date; }
    public function getEndDate() { return $this->end_date; }
    public function getDurationDays() { return $this->duration_days; }
    public function getNumberOfTravelers() { return $this->number_of_travelers; }
    public function getTotalAmount() { return $this->total_amount; }
    public function getDiscountAmount() { return $this->discount_amount; }
    public function getFinalAmount() { return $this->final_amount; }
    public function getSpecialRequests() { return $this->special_requests; }
    public function getStatus() { return $this->status; }
    public function getPaymentStatus() { return $this->payment_status; }
    public function getCreatedAt() { return $this->created_at; }
}
?>
