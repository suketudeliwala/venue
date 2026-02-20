<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

$id = intval($_GET['id']);
$master = $conn->query("SELECT b.*, c.contact_person, c.mobile, c.email, c.company_name, c.address 
                        FROM vms_booking_master b 
                        JOIN vms_customers c ON b.customer_id = c.id 
                        WHERE b.id = $id")->fetch_assoc();

$slots = $conn->query("SELECT s.*, v.venue_name FROM vms_booking_slots s 
                       JOIN vms_venues v ON s.venue_id = v.id 
                       WHERE s.booking_id = $id");
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <h4>Booking Summary: <?= $master['tracking_no'] ?></h4>
        <div>
            <button onclick="window.print()" class="btn btn-outline-dark"><i class="bi bi-printer me-2"></i>Print Summary</button>
            <a href="booking_list.php" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light fw-bold">Customer & Function Information</div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Customer Name:</div>
                        <div class="col-sm-8 fw-bold"><?= $master['contact_person'] ?> (<?= $master['mobile'] ?>)</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Function Name:</div>
                        <div class="col-sm-8"><?= $master['function_name'] ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Email / Address:</div>
                        <div class="col-sm-8"><?= $master['email'] ?><br><small><?= $master['address'] ?></small></div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-light fw-bold">Venue & Schedule Details</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Venue</th>
                                <th>Date</th>
                                <th>Timings</th>
                                <th class="text-end">Rent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($s = $slots->fetch_assoc()): ?>
                            <tr>
                                <td><?= $s['venue_name'] ?></td>
                                <td><?= date('d-M-Y', strtotime($s['booking_date'])) ?></td>
                                <td><?= date('h:i A', strtotime($s['start_time'])) ?> - <?= date('h:i A', strtotime($s['finish_time'])) ?></td>
                                <td class="text-end">₹<?= number_format($s['slot_rent'], 2) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm border-0 border-top border-primary border-4">
                <div class="card-header bg-white fw-bold">Financial Calculation</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Venue Rent:</span>
                        <span>₹<?= number_format($master['total_rent'], 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-muted">
                        <span>GST (18%):</span>
                        <span>₹<?= number_format($master['total_tax'], 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Refundable Security (RSD):</span>
                        <span>₹<?= number_format($master['total_rsd'], 2) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between h4 fw-bold">
                        <span>Net Payable:</span>
                        <span class="text-primary">₹<?= number_format($master['net_payable'], 2) ?></span>
                    </div>
                    <div class="mt-4 d-print-none">
                        <a href="receipt_new.php?booking_id=<?= $id ?>" class="btn btn-success w-100 py-2 fw-bold">
                            <i class="bi bi-cash-stack me-2"></i>Record Advance Payment
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info mt-3 shadow-sm border-0">
                <i class="bi bi-info-circle-fill me-2"></i>
                <strong>Status:</strong> Booking is currently <strong><?= $master['status'] ?></strong>. No payments recorded yet.
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>