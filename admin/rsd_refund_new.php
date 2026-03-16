<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

if(!isset($_GET['booking_id'])) { header("Location: booking_list.php"); exit; }
$booking_id = intval($_GET['booking_id']);

// 1. Fetch Booking and Check for Cancellation Bill details
$sql = "SELECT b.id, b.tracking_no, b.status, c.contact_person, 
        cb.cb_no, cb.total_rent_original, cb.deduction_amount, cb.rsd_original,
        (SELECT SUM(amount_rsd) FROM vms_receipts WHERE booking_id = b.id) as paid_rsd
        FROM vms_booking_master b 
        JOIN vms_customers c ON b.customer_id = c.id 
        LEFT JOIN vms_cancellation_bills cb ON b.id = cb.booking_id
        WHERE b.id = $booking_id";
$res = $conn->query($sql);
$data = $res->fetch_assoc();

// 2. Check if Refund has already been processed
$check_sql = "SELECT voucher_no FROM vms_rsd_refunds WHERE booking_id = $booking_id";
$check_res = $conn->query($check_sql);
$already_refunded = ($check_res->num_rows > 0);
$existing_voucher = $already_refunded ? $check_res->fetch_assoc()['voucher_no'] : null;

// Determine Refund Type and split amounts
$refund_type = ($data['status'] == 'Cancelled') ? 'Cancellation Refund' : 'Event Completion Refund';
$rent_part = ($data['status'] == 'Cancelled') ? max(0, ($data['total_rent_original'] - $data['deduction_amount'])) : 0;
$rsd_part  = ($data['status'] == 'Cancelled') ? $data['rsd_original'] : $data['paid_rsd'];

$voucher_no = $already_refunded ? $existing_voucher : "REF-" . date('Ymd') . "-" . str_pad($booking_id, 4, '0', STR_PAD_LEFT);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-navy fw-bold"><i class="bi bi-currency-exchange me-2"></i>RSD Refund Payout</h4>
        <div>
            <?php if($already_refunded): ?>
                <a href="print_rsd_refund.php?voucher_no=<?= urlencode($voucher_no) ?>" class="btn btn-primary shadow-sm" target="_blank"><i class="bi bi-printer me-1"></i> Print Voucher</a>
            <?php endif; ?>
            <a href="booking_list.php" class="btn btn-secondary shadow-sm ms-2"><i class="bi bi-arrow-left"></i> Back to List</a>
        </div>
    </div>

    <?php if($already_refunded): ?>
        <div class="alert alert-info border-info d-flex align-items-center shadow-sm">
            <i class="bi bi-info-circle-fill me-3 h4 mb-0"></i>
            <div>
                <strong>PROCESS COMPLETED:</strong> This refund was already processed under Voucher <strong><?= $voucher_no ?></strong>. 
                Data entry is locked to prevent double payment. Use the <strong>Print Voucher</strong> button above for documentation.
            </div>
        </div>
    <?php endif; ?>

    <form action="../api/save_rsd_refund.php" method="POST" <?= $already_refunded ? 'style="pointer-events: none; opacity: 0.7;"' : '' ?>>
        <input type="hidden" name="booking_id" value="<?= $booking_id ?>">
        <input type="hidden" name="voucher_no" value="<?= $voucher_no ?>">
        <input type="hidden" name="refund_type" value="<?= $refund_type ?>">

        <div class="card shadow border-0 mb-4">
            <div class="card-body bg-light border-start border-4 border-navy">
                <div class="row g-3">
                    <div class="col-md-3"><strong>Customer:</strong><br><?= $data['contact_person'] ?></div>
                    <div class="col-md-3"><strong>Booking Ref:</strong><br><?= $data['tracking_no'] ?></div>
                    <div class="col-md-3"><strong>Refund Mode:</strong><br><span class="badge bg-primary"><?= $refund_type ?></span></div>
                    <div class="col-md-3 text-end">
                        <strong>Cancel Bill No:</strong><br>
                        <span class="text-danger fw-bold"><?= $data['cb_no'] ?: 'N/A' ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100 border-primary shadow-sm">
                    <div class="card-header bg-primary text-white py-2 fw-bold">1. Rent Refund Component</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="small fw-bold">Amount to be Paid</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" name="rent_refund_amount" class="form-control fw-bold" value="<?= $rent_part ?>" <?= $already_refunded ? 'readonly' : 'oninput="updateTotal()"' ?>>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Payment Mode</label>
                            <select name="rent_payment_mode" class="form-select" <?= $already_refunded ? 'disabled' : '' ?>>
                                <option value="Cheque">Cheque</option>
                                <option value="NEFT">NEFT / Bank Transfer</option>
                                <option value="UPI">UPI</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="small fw-bold">Ref No (Chq/UTR)</label>
                            <input type="text" name="rent_ref_no" class="form-control" placeholder="Enter Reference" required <?= $already_refunded ? 'readonly' : '' ?>>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 border-success shadow-sm">
                    <div class="card-header bg-success text-white py-2 fw-bold">2. Security Deposit (RSD) Component</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="small fw-bold">Amount to be Paid</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" name="rsd_refund_amount" class="form-control fw-bold" value="<?= $rsd_part ?>" <?= $already_refunded ? 'readonly' : 'oninput="updateTotal()"' ?>>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Payment Mode</label>
                            <select name="rsd_payment_mode" class="form-select" <?= $already_refunded ? 'disabled' : '' ?>>
                                <option value="Cheque">Cheque</option>
                                <option value="NEFT">NEFT / Bank Transfer</option>
                                <option value="UPI">UPI</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="small fw-bold">Ref No (Chq/UTR)</label>
                            <input type="text" name="rsd_ref_no" class="form-control" placeholder="Enter Reference" required <?= $already_refunded ? 'readonly' : '' ?>>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4 border-dark shadow-sm">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <label class="small fw-bold">Trust Payout Bank & Account No</label>
                        <input type="text" name="trust_bank_details" class="form-control" placeholder="e.g. Bank of India - A/c 1234..." required <?= $already_refunded ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">Refund Date</label>
                        <input type="date" name="refund_date" class="form-control" value="<?= date('Y-m-d') ?>" required <?= $already_refunded ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-4 text-end">
                        <label class="small fw-bold text-muted">Combined Total Refund</label>
                        <div class="h3 fw-bold text-navy mb-0">₹ <span id="total_disp"><?= number_format($rent_part + $rsd_part, 2) ?></span></div>
                        <input type="hidden" name="total_refund_amount" id="total_val" value="<?= $rent_part + $rsd_part ?>">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="small fw-bold">Internal Audit Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2" <?= $already_refunded ? 'readonly' : '' ?>></textarea>
                </div>
            </div>
            <?php if(!$already_refunded): ?>
            <div class="card-footer bg-white text-end py-3">
                <button type="submit" class="btn btn-navy btn-lg px-5 text-white" style="background-color: #001d4a;">
                    <i class="bi bi-check-circle me-2"></i>ISSUE DUAL PAYOUT VOUCHER
                </button>
            </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
function updateTotal() {
    let r = parseFloat(document.getElementsByName('rent_refund_amount')[0].value) || 0;
    let s = parseFloat(document.getElementsByName('rsd_refund_amount')[0].value) || 0;
    let total = r + s;
    document.getElementById('total_disp').innerText = total.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('total_val').value = total.toFixed(2);
}
</script>