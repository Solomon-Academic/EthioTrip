<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Bookings | EthioTrip</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php require __DIR__ . '/../partials/admin_head.php'; ?>
    <style>
        .message {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .message-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .message-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .filter-bar {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
            margin-bottom: 20px;
        }
        .filter-bar .form-group {
            flex: 1;
            min-width: 150px;
        }
        .actions-cell {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .btn-approve {
            background: #27ae60;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
        }
        .btn-reject {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
        }
        .btn-view {
            background: #3498db;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            text-decoration: none;
            display: inline-block;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 16px;
            max-width: 500px;
            width: 90%;
        }
        .modal-content textarea {
            width: 100%;
            padding: 10px;
            margin: 15px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            resize: vertical;
        }
        .button-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="page">
        <?php require __DIR__ . '/../partials/admin_navbar.php'; ?>

        <div class="header">
            <div>
                <h1>Payment & Booking Management</h1>
                <p>Review and approve user payments, manage booking approvals.</p>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message message-<?php echo $messageType ?? 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="cards">
            <div class="card">
                <h3>Total Bookings</h3>
                <span><?php echo intval($stats['total'] ?? 0); ?></span>
            </div>
            <div class="card">
                <h3>Pending Approval</h3>
                <span><?php echo intval($stats['pending'] ?? 0); ?></span>
            </div>
            <div class="card">
                <h3>Approved</h3>
                <span><?php echo intval($stats['approved'] ?? 0); ?></span>
            </div>
            <div class="card">
                <h3>Total Revenue</h3>
                <span>$<?php echo number_format(floatval($stats['total_revenue'] ?? 0), 2); ?></span>
            </div>
        </div>

        <div class="card">
            <form method="GET" action="/ethiotrip1/ethiotrip/public/admin/bookings" class="filter-bar">
                <div class="form-group">
                    <label>Filter</label>
                    <select name="filter">
                        <option value="payment_pending" <?php echo ($currentFilter ?? '') === 'payment_pending' ? 'selected' : ''; ?>>Pending Payments</option>
                        <option value="pending" <?php echo ($currentFilter ?? '') === 'pending' ? 'selected' : ''; ?>>Pending Bookings</option>
                        <option value="approved" <?php echo ($currentFilter ?? '') === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo ($currentFilter ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        <option value="all" <?php echo ($currentFilter ?? '') === 'all' ? 'selected' : ''; ?>>All Bookings</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Search</label>
                    <input type="search" name="search" value="<?php echo htmlspecialchars($searchTerm ?? ''); ?>" placeholder="Name, package, or transaction ID">
                </div>
                <div class="form-group">
                    <button class="button" type="submit">Apply Filter</button>
                </div>
            </form>
        </div>

        <div class="card">
            <?php if ($bookings instanceof \mysqli_result && $bookings->num_rows > 0): ?>
                <div class="table-wrap admin-table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Trip Details</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Admin Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($booking = $bookings->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo intval($booking['id']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['user_name'] ?? 'Unknown'); ?></strong><br>
                                        <small><?php echo htmlspecialchars($booking['user_email'] ?? ''); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['package_name'] ?? ''); ?><br>
                                        <small>
                                            <?php echo htmlspecialchars($booking['destination'] ?? ''); ?><br>
                                            <?php echo htmlspecialchars($booking['start_date']); ?> → <?php echo htmlspecialchars($booking['end_date']); ?>
                                        </small>
                                    </td>
                                    <td>$<?php echo number_format(floatval($booking['final_amount']), 2); ?></td>
                                    <td>
                                        <span class="status status-<?php echo htmlspecialchars($booking['payment_status']); ?>">
                                            <?php echo ucfirst(htmlspecialchars($booking['payment_status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status status-<?php echo htmlspecialchars($booking['admin_approval_status'] ?? 'pending'); ?>">
                                            <?php echo ucfirst(htmlspecialchars($booking['admin_approval_status'] ?? 'pending')); ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="/ethiotrip1/ethiotrip/public/admin/bookings/view?id=<?php echo $booking['id']; ?>" class="btn-view">View</a>
                                        
                                        <?php if (($booking['payment_status'] ?? '') === 'pending'): ?>
                                            <button onclick="showApproveModal(<?php echo $booking['id']; ?>, 'approve_payment')" class="btn-approve">Approve Payment</button>
                                            <button onclick="showRejectModal(<?php echo $booking['id']; ?>, 'fail_payment')" class="btn-reject">Reject Payment</button>
                                        <?php endif; ?>
                                        
                                        <?php if (($booking['admin_approval_status'] ?? '') === 'pending' && ($booking['payment_status'] ?? '') !== 'pending'): ?>
                                            <button onclick="showApproveModal(<?php echo $booking['id']; ?>, 'approve')" class="btn-approve">Approve Booking</button>
                                            <button onclick="showRejectModal(<?php echo $booking['id']; ?>, 'reject')" class="btn-reject">Reject Booking</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="text-align: center; padding: 40px; color: #636e72;">No bookings match this filter.</p>
            <?php endif; ?>
        </div>

        <footer class="footer">
            <div class="footer-inner">© <?php echo date('Y'); ?> <a href="/ethiotrip1/ethiotrip/public/">EthioTrip</a> Ethiopia. All rights reserved.</div>
        </footer>
    </div>

    <div id="approveModal" class="modal">
        <div class="modal-content">
            <h3>Approve Booking / Payment</h3>
            <p>Add optional notes for the customer:</p>
            <form method="POST" action="/ethiotrip1/ethiotrip/public/admin/bookings" id="approveForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(Backend\Core\Session::csrfToken()); ?>">
                <input type="hidden" name="booking_id" id="approveBookingId">
                <input type="hidden" name="action" id="approveAction">
                <textarea name="admin_notes" rows="4" placeholder="Add confirmation notes, special instructions, or thank you message..."></textarea>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="closeModal('approveModal')" class="button-secondary">Cancel</button>
                    <button type="submit" class="btn-approve">Confirm Approval</button>
                </div>
            </form>
        </div>
    </div>

    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <h3>Reject Booking / Payment</h3>
            <p>Please provide a reason for rejection:</p>
            <form method="POST" action="/ethiotrip1/ethiotrip/public/admin/bookings" id="rejectForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(Backend\Core\Session::csrfToken()); ?>">
                <input type="hidden" name="booking_id" id="rejectBookingId">
                <input type="hidden" name="action" id="rejectAction">
                <textarea name="admin_notes" rows="4" placeholder="Please explain why this booking/payment is being rejected..." required></textarea>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="closeModal('rejectModal')" class="button-secondary">Cancel</button>
                    <button type="submit" class="btn-reject">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showApproveModal(bookingId, action) {
            document.getElementById('approveBookingId').value = bookingId;
            document.getElementById('approveAction').value = action;
            document.getElementById('approveModal').style.display = 'flex';
        }
        
        function showRejectModal(bookingId, action) {
            document.getElementById('rejectBookingId').value = bookingId;
            document.getElementById('rejectAction').value = action;
            document.getElementById('rejectModal').style.display = 'flex';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
    <?php require __DIR__ . '/../partials/admin_footer_scripts.php'; ?>
</body>
</html>