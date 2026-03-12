<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

// Determine which tab is active
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'confirmed';

// --- SQL LOGIC ---
if($tab == 'pending_utilization') {
    $sql = "SELECT b.*, s.id as slot_id, s.booking_date, s.start_time, s.finish_time, v.venue_name, c.contact_person, c.mobile
            FROM vms_booking_slots s
            JOIN vms_booking_master b ON s.booking_id = b.id
            JOIN vms_venues v ON s.venue_id = v.id
            JOIN vms_customers c ON b.customer_id = c.id
            LEFT JOIN vms_utilization_reports ur ON s.id = ur.slot_id
            WHERE ur.id IS NULL 
            AND CONCAT(s.booking_date, ' ', s.finish_time) < NOW()
            AND b.status != 'Cancelled'
            ORDER BY s.booking_date ASC";
} else {
    $sql = "SELECT b.*, c.contact_person, c.mobile, 
            (SELECT COUNT(*) FROM vms_booking_slots WHERE booking_id = b.id) as slot_count 
            FROM vms_booking_master b 
            JOIN vms_customers c ON b.customer_id = c.id 
            ORDER BY b.id DESC";
}

$res = $conn->query($sql);
?>

<div class="container-fluid py-4">
    <ul class="nav nav-tabs mb-0 border-bottom-0">
        <li class="nav-item">
            <a class="nav-link <?= ($tab == 'confirmed') ? 'active fw-bold' : '' ?>" href="booking_list.php?tab=confirmed">
                <i class="bi bi-calendar-check me-1"></i> Active Bookings
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($tab == 'pending_utilization') ? 'active fw-bold text-danger' : '' ?>" href="booking_list.php?tab=pending_utilization">
                <i class="bi bi-exclamation-triangle me-1"></i> Pending Utilization 
            </a>
        </li>
    </ul>

    <div class="card shadow border-0">
        <div class="card-header bg-navy text-white d-flex justify-content-between align-items-center" style="background-color: #001d4a !important;">
            <h5 class="mb-0"><?= ($tab == 'pending_utilization') ? 'Awaiting Utilization' : 'Booking Master List' ?></h5>
            <a href="booking_new.php" class="btn btn-sm btn-warning fw-bold">+ New Booking</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <?php if($tab == 'pending_utilization'): ?><th>Event Date</th><?php endif; ?>
                        <th>Ref No</th>
                        <th>Event & Customer</th>
                        <th>Net Payable</th>
                        <th>Balance Due</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($res->num_rows > 0): ?>
                        <?php while($row = $res->fetch_assoc()): 
                            $booking_id = $row['id'];
                            
                            // 1. Fetch exact total paid from receipts table
                            $paid_query = $conn->query("SELECT SUM(amount_rent + amount_rsd) as paid_total FROM vms_receipts WHERE booking_id = $booking_id");
                            $paid_row = $paid_query->fetch_assoc();
                            $total_paid = $paid_row['paid_total'] ?? 0;
                            
                            // 2. Calculate Balance
                            $net_payable = floatval($row['net_payable']);
                            $balance_due = $net_payable - $total_paid;

                            // 3. Check for any utilization reports filed 
                            $util_count = $conn->query("SELECT id FROM vms_utilization_reports WHERE booking_id = $booking_id")->num_rows;

                            // 4. Button Logic variables
                            $is_disabled = ($balance_due <= 0 || $util_count > 0);
                            $cancel_disabled = ($util_count > 0);
                        ?>
                        <tr>
                            <?php if($tab == 'pending_utilization'): ?>
                                <td><?= date('d-m-Y', strtotime($row['booking_date'])) ?></td>
                            <?php endif; ?>
                            
                            <td class="fw-bold"><?= $row['tracking_no'] ?></td>
                            <td>
                                <div class="fw-bold text-navy"><?= htmlspecialchars($row['function_name']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($row['contact_person']) ?> | <?= $row['mobile'] ?></div>
                            </td>
                            <td class="text-end">₹<?= number_format($net_payable, 2) ?></td>
                            <td class="text-end fw-bold <?= ($balance_due > 0) ? 'text-danger' : 'text-success' ?>">
                                ₹<?= number_format($balance_due, 2) ?>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill <?= $row['status'] == 'Confirmed' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="booking_view.php?id=<?= $booking_id ?>" class="btn btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a href="booking_edit.php?id=<?= $booking_id ?>" 
                                    class="btn btn-outline-secondary <?= $is_disabled ? 'disabled' : '' ?>" 
                                    title="Edit" 
                                    <?= $is_disabled ? 'onclick="return false;"' : '' ?>>
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <button class="btn btn-outline-warning <?= $cancel_disabled ? 'disabled' : '' ?>" 
                                            onclick="<?= $cancel_disabled ? 'return false;' : 'cancelBooking('.$booking_id.')' ?>" 
                                            title="Cancel">
                                        <i class="bi bi-x-circle"></i>
                                    </button>

                                    <?php if($row['status'] == 'Cancelled' || $util_count > 0): ?>
                                        <a href="rsd_refund_new.php?booking_id=<?= $booking_id ?>" class="btn btn-outline-success" title="Process Refund">
                                            <i class="bi bi-currency-exchange"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if($row['status'] == 'Cancelled'): ?>
                                        <?php 
                                        $cb_res = $conn->query("SELECT cb_no FROM vms_cancellation_bills WHERE booking_id = $booking_id");
                                        if($cb_row = $cb_res->fetch_assoc()): ?>
                                            <a href="print_cancellation.php?cb_no=<?= urlencode($cb_row['cb_no']) ?>" class="btn btn-outline-dark" target="_blank" title="Print CB">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <a href="../api/booking_delete.php?id=<?= $booking_id ?>" 
                                    class="btn btn-outline-danger <?= $is_disabled ? 'disabled' : '' ?>" 
                                    title="Delete" 
                                    onclick="<?= $is_disabled ? 'return false;' : "return confirm('Are you sure you want to permanently delete this booking?');" ?>">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="10" class="text-center py-4 text-muted">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>


            </table>
        </div>
    </div>
</div>

<script>
function cancelBooking(id) {
    if(confirm("Are you sure you want to cancel this booking?")) {
        window.location.href = "booking_cancel_process.php?id=" + id;
    }
}
</script>
<?php include("../includes/footer.php"); ?>