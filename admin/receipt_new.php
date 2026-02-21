<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

$booking_id = intval($_GET['booking_id']);

// Fetch Booking & Customer Details
$booking = $conn->query("SELECT b.*, c.contact_person, c.mobile 
                        FROM vms_booking_master b 
                        JOIN vms_customers c ON b.customer_id = c.id 
                        WHERE b.id = $booking_id")->fetch_assoc();

// Calculate already paid amounts to show balance
$paid = $conn->query("SELECT SUM(amount_rent) as paid_rent, SUM(amount_rsd) as paid_rsd 
                     FROM vms_receipts WHERE booking_id = $booking_id")->fetch_assoc();

$bal_rent = ($booking['total_rent'] + $booking['total_tax']) - ($paid['paid_rent'] ?? 0);
$bal_rsd  = $booking['total_rsd'] - ($paid['paid_rsd'] ?? 0);
?>

<div class="container py-4">
    <div class="card shadow border-0">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">New Receipt: <?= $booking['tracking_no'] ?></h5>
        </div>
        <form action="../api/save_receipt.php" method="POST" class="card-body">
            <input type="hidden" name="booking_id" value="<?= $booking_id ?>">
            
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="text-muted small fw-bold">Customer</label>
                    <p class="form-control-plaintext border-bottom"><?= $booking['contact_person'] ?> (<?= $booking['mobile'] ?>)</p>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small fw-bold">Event</label>
                    <p class="form-control-plaintext border-bottom"><?= $booking['function_name'] ?></p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Receipt Date</label>
                    <input type="date" name="receipt_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Payment Mode</label>
                    <select name="payment_mode" class="form-select" required>
                        <option value="Cheque">Cheque</option>
                        <option value="Draft">Draft</option>
                        <option value="Online Transfer">Online Transfer</option>
                        <option value="Cash">Cash</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Instrument/Ref No</label>
                    <input type="text" name="instrument_no" class="form-control" placeholder="Cheque/UTR No">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" class="form-control">
                </div>
            </div>

            <hr class="my-4">

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded">
                        <label class="fw-bold text-primary">Rent Allocation (Incl. GST)</label>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span>Balance Due:</span> <span class="fw-bold">₹<?= number_format($bal_rent, 2) ?></span>
                        </div>
                        <input type="number" step="0.01" name="amount_rent" class="form-control form-control-lg" placeholder="Enter Amount">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded">
                        <label class="fw-bold text-warning">RSD Allocation</label>
                        <div class="d-flex justify-content-between mb-2 small">
                            <span>Balance Due:</span> <span class="fw-bold">₹<?= number_format($bal_rsd, 2) ?></span>
                        </div>
                        <input type="number" step="0.01" name="amount_rsd" class="form-control form-control-lg" placeholder="Enter Amount">
                    </div>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-success btn-lg px-5">Generate Receipt</button>
            </div>
        </form>
    </div>
</div>

<?php include("../includes/footer.php"); ?>