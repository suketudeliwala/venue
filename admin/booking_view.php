<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

if(!isset($_GET['id'])) { header("Location: booking_list.php"); exit; }
$id = intval($_GET['id']);

// 1. Fetch Master Booking & Customer Data
$master = $conn->query("SELECT b.*, c.contact_person, c.mobile, c.email, c.address 
                        FROM vms_booking_master b 
                        JOIN vms_customers c ON b.customer_id = c.id 
                        WHERE b.id = $id")->fetch_assoc();

// 2. Fetch Venue Slots
$slots_sql = "SELECT s.*, v.venue_name, r.duration_label 
              FROM vms_booking_slots s 
              JOIN vms_venues v ON s.venue_id = v.id 
              LEFT JOIN vms_venue_rates r ON s.slot_rate_id = r.id 
              WHERE s.booking_id = $id";
$slots = $conn->query($slots_sql);

// 3. Fetch Payment Totals (Receipts)
$paid_data = $conn->query("SELECT SUM(amount_rent) as total_paid_rent, SUM(amount_rsd) as total_paid_rsd 
                           FROM vms_receipts WHERE booking_id = $id")->fetch_assoc();

$paid_rent = $paid_data['total_paid_rent'] ?? 0;
$paid_rsd  = $paid_data['total_paid_rsd'] ?? 0;

// 4. Calculate Balances
$total_billable_rent = $master['total_rent'] + $master['total_tax'];
$total_billable_rsd  = $master['total_rsd'];

$bal_rent = $total_billable_rent - $paid_rent;
$bal_rsd  = $total_billable_rsd - $paid_rsd;
$grand_balance = $bal_rent + $bal_rsd;
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <h4><i class="bi bi-file-earmark-text me-2"></i>Booking Voucher: <?= $master['tracking_no'] ?></h4>
        <div>
            <button onclick="window.print()" class="btn btn-outline-dark"><i class="bi bi-printer me-2"></i>Print</button>
            <a href="booking_list.php" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    <?php if($master['status'] == 'Cancelled'): ?>
    <div class="alert alert-danger border-start border-4 shadow-sm mb-4">
        <h5 class="alert-heading fw-bold"><i class="bi bi-x-octagon-fill me-2"></i>Booking Cancelled</h5>
        <p class="mb-0"><?= htmlspecialchars($master['remarks']) ?></p>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light fw-bold text-primary">Customer & Event Details</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <label class="text-muted small d-block">Customer Name</label>
                            <p class="fw-bold"><?= $master['contact_person'] ?> (<?= $master['mobile'] ?>)</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted small d-block">Function / Event</label>
                            <p class="fw-bold"><?= $master['function_name'] ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Venue</th>
                                <th>Date / Time</th>
                                <th>Rent Type</th>
                                <th class="text-end">Rent Amount</th>
                                <th class="text-end">RSD Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($s = $slots->fetch_assoc()): ?>
                            <tr>
                                <td><?= $s['venue_name'] ?></td>
                                <td><?= date('d-M-y', strtotime($s['booking_date'])) ?><br><small><?= $s['start_time'] ?>-<?= $s['finish_time'] ?></small></td>
                                <td><span class="badge bg-info text-dark"><?= $s['duration_label'] ?? 'Custom' ?></span></td>
                                <td class="text-end">₹<?= number_format($s['slot_rent'], 2) ?></td>
                                <td class="text-end">₹<?= number_format($s['slot_rsd'], 2) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow border-0">
                <div class="card-header bg-dark text-white fw-bold text-center">Payment Summary</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Rent (+Tax):</span>
                        <span>₹<?= number_format($total_billable_rent, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total RSD:</span>
                        <span>₹<?= number_format($total_billable_rsd, 2) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2 text-success fw-bold">
                        <span>Total Paid:</span>
                        <span>₹<?= number_format($paid_rent + $paid_rsd, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between h5 fw-bold <?= ($grand_balance <= 0) ? 'text-success' : 'text-danger' ?>">
                        <span>Balance Due:</span>
                        <span>₹<?= number_format($grand_balance, 2) ?></span>
                    </div>

                    <div class="mt-4 d-print-none">
                        <?php if($grand_balance > 0 && $master['status'] != 'Cancelled'): ?>
                            <a href="receipt_new.php?booking_id=<?= $id ?>" class="btn btn-success w-100 py-2 fw-bold">
                                <i class="bi bi-cash-stack me-2"></i>Record Payment
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary w-100 py-2" disabled>
                                <i class="bi bi-lock-fill me-2"></i> Payment Complete
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-secondary text-white fw-bold">Receipt History</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Receipt No</th>
                        <th>Date</th>
                        <th>Mode</th>
                        <th class="text-end">Rent Paid</th>
                        <th class="text-end">RSD Paid</th>
                        <th class="text-end">Total</th>
                        <th class="text-center d-print-none">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $rcpts = $conn->query("SELECT * FROM vms_receipts WHERE booking_id = $id ORDER BY receipt_date DESC");
                    if($rcpts->num_rows > 0):
                        while($r = $rcpts->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><span class="fw-bold text-primary"><?= $r['receipt_no'] ?></span></td>
                        <td><?= date('d-M-Y', strtotime($r['receipt_date'])) ?></td>
                        <td><?= $r['payment_mode'] ?></td>
                        <td class="text-end">₹<?= number_format($r['amount_rent'], 2) ?></td>
                        <td class="text-end">₹<?= number_format($r['amount_rsd'], 2) ?></td>
                        <td class="text-end fw-bold">₹<?= number_format($r['total_amount'], 2) ?></td>
                        <td class="text-center d-print-none">
                            <a href="receipt_print_a5.php?id=<?= $r['id'] ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="Print Receipt">
                                    <i class="bi bi-printer"></i>
                            </a>
                            <a href="receipt_edit.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                <a href="../api/delete_receipt.php?id=<?= $r['id'] ?>&booking_id=<?= $id ?>" 
                                class="btn btn-sm btn-outline-danger" 
                                onclick="return confirm('Delete this receipt?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No receipts recorded yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>