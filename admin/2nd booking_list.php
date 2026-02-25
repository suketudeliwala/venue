<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

// Determine which tab is active
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'confirmed';

// --- SQL LOGIC ---
if($tab == 'pending_utilization') {
    // Finds past slots that have NO utilization report filed yet
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
    // Standard Master List
    $sql = "SELECT b.*, c.contact_person, c.mobile, 
            (SELECT COUNT(*) FROM vms_booking_slots WHERE booking_id = b.id) as slot_count 
            FROM vms_booking_master b 
            JOIN vms_customers c ON b.customer_id = c.id 
            ORDER BY b.id DESC";
}

$res = $conn->query($sql);
?>

<style>
    /* Custom tab styling to ensure visibility */
    .nav-tabs .nav-link {
        border: 1px solid #313132; /* Light grey border for all tabs */
        background-color: #f9fbfc; /* Very light grey background for inactive tabs */
        color: #86b6e7 !important; /* Grey text for inactive tabs */
        margin-right: 2px;
    }
    
    .nav-tabs .nav-link.active {
        background-color: #ffffff !important; /* Pure white for active tab */
        color: #001d4a !important; /* Dark navy text for active tab visibility */
        border-bottom-color: transparent !important; /* Connects tab to the card below */
        border-top: 3px solid #001d4a; /* Adds a top highlight for the active tab */
    }

    .nav-tabs .nav-link.text-danger.active {
        color: #dc3545 !important; /* Keeps "Pending" tab red even when active */
        border-top-color: #dc3545;
    }
</style>

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
                <?php 
                // Quick badge count for pending reports
                $count_pending = $conn->query("SELECT COUNT(s.id) as total FROM vms_booking_slots s LEFT JOIN vms_utilization_reports ur ON s.id = ur.slot_id WHERE ur.id IS NULL AND CONCAT(s.booking_date, ' ', s.finish_time) < NOW() AND (SELECT status FROM vms_booking_master WHERE id = s.booking_id) != 'Cancelled'")->fetch_assoc()['total'];
                if($count_pending > 0) echo '<span class="badge bg-danger ms-1">'.$count_pending.'</span>';
                ?>
            </a>
        </li>
    </ul>

    <div class="card shadow border-0">
        <div class="card-header bg-navy text-white d-flex justify-content-between align-items-center" style="background-color: #001d4a !important;">
            <h5 class="mb-0">
                <?= ($tab == 'pending_utilization') ? 'Events Awaiting Utilization Reports' : 'Venue Bookings Master List' ?>
            </h5>
            <a href="booking_new.php" class="btn btn-sm btn-warning fw-bold">+ New Booking</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <?php if($tab == 'pending_utilization'): ?>
                            <th>Event Date & Venue</th>
                        <?php endif; ?>
                        <th>Ref No</th>
                        <th>Event & Customer</th>
                        <?php if($tab == 'confirmed'): ?>
                            <th>Slots</th>
                            <th>Total Rent</th>
                            <th>Total RSD</th>
                        <?php endif; ?>
                        <th>Balance Due</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($res->num_rows > 0): ?>
                        <?php while($row = $res->fetch_assoc()): ?>
                        <tr>
                            <?php if($tab == 'pending_utilization'): ?>
                                <td>
                                    <span class="fw-bold"><?= date('d-M-y', strtotime($row['booking_date'])) ?></span><br>
                                    <small class="text-muted"><?= htmlspecialchars($row['venue_name']) ?></small>
                                </td>
                            <?php endif; ?>

                            <td><span class="fw-bold text-primary"><?= $row['tracking_no'] ?></span></td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($row['function_name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($row['contact_person']) ?> - <?= htmlspecialchars($row['mobile']) ?></small>
                            </td>

                            <?php if($tab == 'confirmed'): ?>
                                <td><span class="badge bg-info text-dark"><?= $row['slot_count'] ?> Slot(s)</span></td>
                                <td>₹<?= number_format($row['total_rent'], 2) ?></td>
                                <td>₹<?= number_format($row['total_rsd'], 2) ?></td>
                            <?php endif; ?>

                            <?php 
                            // Calculate Balance
                            $total_paid = $conn->query("SELECT SUM(total_amount) as paid FROM vms_receipts WHERE booking_id = ".$row['id'])->fetch_assoc()['paid'] ?? 0;
                            $balance = $row['net_payable'] - $total_paid;
                            ?>
                            <td class="fw-bold <?= ($balance > 0) ? 'text-danger' : 'text-success' ?>">
                                ₹<?= number_format($balance, 2) ?>
                            </td>

                            <td>
                                <span class="badge bg-<?= ($tab == 'pending_utilization') ? 'warning text-dark' : ($row['status'] == 'Confirmed' ? 'success' : 'danger') ?>">
                                    <?= ($tab == 'pending_utilization') ? 'Report Pending' : $row['status'] ?>
                                </span>
                            </td>

                            <td class="text-end">
                                <?php if($tab == 'pending_utilization'): ?>
                                    <a href="utilization_new.php?slot_id=<?= $row['slot_id'] ?>" class="btn btn-sm btn-danger fw-bold">
                                        <i class="bi bi-file-earmark-plus me-1"></i> File Report
                                    </a>
                                <?php else: ?>
                                    <a href="booking_view.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></a>
                                    <a href="booking_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <button class="btn btn-sm btn-outline-danger" onclick="cancelBooking(<?= $row['id'] ?>)" title="Cancel"><i class="bi bi-x-circle"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="10" class="text-center py-4 text-muted">No bookings found in this category.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function cancelBooking(id) {
    if(confirm("Are you sure you want to cancel this booking? Cancellation charges will apply as per policy.")) {
        window.location.href = "booking_cancel_process.php?id=" + id;
    }
}
</script>

<?php include("../includes/footer.php"); ?>