<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

if(!isset($_GET['booking_id'])) { header("Location: booking_list.php"); exit; }
$booking_id = intval($_GET['booking_id']);

// Fetch Booking, Customer, and check for Cancellation Bill
$sql = "SELECT b.id, b.tracking_no, b.status, c.contact_person, 
        cb.cb_no, cb.net_refund_amount as cancel_ref,
        (SELECT SUM(amount_rsd) FROM vms_receipts WHERE booking_id = b.id) as rsd_paid
        FROM vms_booking_master b 
        JOIN vms_customers c ON b.customer_id = c.id 
        LEFT JOIN vms_cancellation_bills cb ON b.id = cb.booking_id
        WHERE b.id = $booking_id";
$res = $conn->query($sql);
$data = $res->fetch_assoc();

// Determine Refund Type and Amount
$refund_type = ($data['status'] == 'Cancelled') ? 'Cancellation Refund' : 'Event Completion Refund';
$amount_to_refund = ($data['status'] == 'Cancelled') ? $data['cancel_ref'] : $data['rsd_paid'];

// Check if already refunded
$check = $conn->query("SELECT voucher_no FROM vms_rsd_refunds WHERE booking_id = $booking_id");
$already_done = ($check->num_rows > 0);

$voucher_no = "REF-" . date('Ymd') . "-" . str_pad($booking_id, 4, '0', STR_PAD_LEFT);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-navy fw-bold"><i class="bi bi-currency-exchange me-2"></i>RSD Refund Payout</h4>
        <a href="booking_list.php" class="btn btn-secondary shadow-sm"><i class="bi bi-arrow-left me-1"></i> Back to List</a>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-4">
            <?php if($already_done): ?>
                <div class="alert alert-warning border-warning">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> 
                    Voucher <strong><?= $check->fetch_assoc()['voucher_no'] ?></strong> has already been issued for this booking.
                </div>
            <?php else: ?>
                <form action="../api/save_rsd_refund.php" method="POST">
                    <input type="hidden" name="booking_id" value="<?= $booking_id ?>">
                    <input type="hidden" name="voucher_no" value="<?= $voucher_no ?>">
                    <input type="hidden" name="refund_type" value="<?= $refund_type ?>">
                    <input type="hidden" name="refund_amount" value="<?= $amount_to_refund ?>">

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded border h-100">
                                <h6 class="fw-bold border-bottom pb-2">Booking & Refund Info</h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><td>Customer:</td><td class="fw-bold"><?= $data['contact_person'] ?></td></tr>
                                    <tr><td>Booking No:</td><td><?= $data['tracking_no'] ?></td></tr>
                                    <tr><td>Refund Type:</td><td class="text-primary fw-bold"><?= $refund_type ?></td></tr>
                                    <?php if($data['cb_no']): ?>
                                        <tr><td>Cancel Bill No:</td><td class="text-danger fw-bold"><?= $data['cb_no'] ?></td></tr>
                                    <?php endif; ?>
                                    <tr class="border-top">
                                        <td class="h5 pt-2">Refund Amount:</td>
                                        <td class="h5 pt-2 fw-bold text-success">₹ <?= number_format($amount_to_refund, 2) ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 border rounded h-100">
                                <h6 class="fw-bold border-bottom pb-2">Payout Details</h6>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Trust Bank / Account Name</label>
                                    <input type="text" name="trust_bank_details" class="form-control" placeholder="e.g. HDFC Bank - A/c 50100..." required>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Payment Mode</label>
                                        <select name="payment_mode" class="form-select" required>
                                            <option value="Cheque">Cheque</option>
                                            <option value="NEFT">NEFT / Bank Transfer</option>
                                            <option value="UPI">UPI</option>
                                            <option value="Cash">Cash</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Ref No (Chq/UTR)</label>
                                        <input type="text" name="ref_no" class="form-control" required>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label class="form-label small fw-bold">Voucher Date</label>
                                    <input type="date" name="refund_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Internal Remarks</label>
                            <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. Refunded after deducting electricity damages..."></textarea>
                        </div>

                        <div class="col-md-12 text-end">
                            <button type="submit" class="btn btn-success btn-lg px-5 shadow">ISSUE PAYOUT VOUCHER</button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include("../includes/footer.php"); ?>