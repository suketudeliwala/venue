<?php
include("../includes/config.php");
include("../includes/header_admin.php");

if(!isset($_GET['id'])) { header("Location: booking_list.php"); exit; }
$id = intval($_GET['id']);

$sql = "SELECT b.*, c.contact_person, MIN(s.booking_date) as start_date 
        FROM vms_booking_master b 
        JOIN vms_booking_slots s ON b.id = s.booking_id 
        JOIN vms_customers c ON b.customer_id = c.id
        WHERE b.id = $id";
$booking = $conn->query($sql)->fetch_assoc();

if($booking['status'] == 'Cancelled') {
    die("<div class='container mt-5'><div class='alert alert-warning'>This booking is already cancelled.</div></div>");
}

// Fetch Actual Payments Received
$receipts = $conn->query("SELECT SUM(amount_rent) as paid_rent, SUM(amount_rsd) as paid_rsd FROM vms_receipts WHERE booking_id = $id")->fetch_assoc();
$paid_rent = $receipts['paid_rent'] ?? 0;
$paid_rsd = $receipts['paid_rsd'] ?? 0;

$today = new DateTime();
$eventDate = new DateTime($booking['start_date']);
$daysRemaining = $today->diff($eventDate)->days;
if($eventDate < $today) $daysRemaining = 0;

$policy = $conn->query("SELECT deduction_percent FROM vms_cancellation_policy WHERE $daysRemaining BETWEEN days_before_min AND days_before_max LIMIT 1")->fetch_assoc();
$suggested_percent = $policy['deduction_percent'] ?? 100;
$cb_no = "CB-" . date('Ymd') . "-" . str_pad($id, 4, '0', STR_PAD_LEFT);
?>

<div class="container py-4">
    <div class="card shadow border-0">
        <div class="card-header bg-danger text-white py-3">
            <h5 class="mb-0"><i class="bi bi-file-earmark-x me-2"></i>Cancellation Bill Processing</h5>
        </div>
        <form action="../api/save_cancellation_bill.php" method="POST" class="card-body">
            <input type="hidden" name="booking_id" value="<?= $id ?>">
            <input type="hidden" name="cb_no" value="<?= $cb_no ?>">
            
            <input type="hidden" name="orig_rent" value="<?= $booking['total_rent'] ?>">
            <input type="hidden" name="orig_gst" value="<?= $booking['total_tax'] ?>">
            <input type="hidden" name="orig_rsd" value="<?= $booking['total_rsd'] ?>">

            <div class="row g-3 mb-4">
                <div class="col-md-6"><strong>Customer:</strong> <?= $booking['contact_person'] ?></div>
                <div class="col-md-3 text-danger"><strong>Days to Event:</strong> <?= $daysRemaining ?></div>
                <div class="col-md-3 text-end"><strong>Bill No:</strong> <?= $cb_no ?></div>
            </div>

            <h6 class="fw-bold text-primary border-bottom pb-2">Chargeable / Receivable</h6>
            <div class="row g-2 mb-4 bg-light p-2 small">
                <div class="col">Rent: <?= number_format($booking['total_rent'], 2) ?></div>
                <div class="col">GST: <?= number_format($booking['total_tax'], 2) ?></div>
                <div class="col">RSD: <?= number_format($booking['total_rsd'], 2) ?></div>
                <div class="col fw-bold">Total: <?= number_format($booking['net_payable'], 2) ?></div>
            </div>

            <h6 class="fw-bold text-success border-bottom pb-2">Actual Paid Amount</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="small">Rent Paid (Incl. GST)</label>
                    <input type="number" id="paid_rent" name="paid_rent" class="form-control" value="<?= $paid_rent ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label class="small">RSD Paid</label>
                    <input type="number" id="paid_rsd" name="paid_rsd" class="form-control" value="<?= $paid_rsd ?>" readonly>
                </div>
                <div class="col-md-2">
                    <label class="small text-danger">Deduction %</label>
                    <input type="number" step="0.01" id="deduct_pc" name="deduct_pc" class="form-control border-danger" value="<?= $suggested_percent ?>" oninput="calculateRefund()">
                </div>
                <div class="col-md-4">
                    <label class="small">Manual Adjustment (+/-)</label>
                    <input type="number" id="manual_adj" name="manual_adj" class="form-control" value="0" oninput="calculateRefund()">
                </div>
            </div>

            <div class="row justify-content-end">
                <div class="col-md-6">
                    <table class="table table-sm border">
                        <tr><td>Cancellation Charges:</td><td class="text-end text-danger fw-bold">₹ <span id="disp_penalty">0.00</span></td></tr>
                        <tr><td>Refundable Rent:</td><td class="text-end fw-bold">₹ <span id="disp_rent_ref">0.00</span></td></tr>
                        <tr><td>Refundable RSD:</td><td class="text-end fw-bold">₹ <span id="disp_rsd_ref">0.00</span></td></tr>
                        <tr class="table-success h5 fw-bold"><td>Total Net Refund:</td><td class="text-end">₹ <span id="disp_total">0.00</span></td></tr>
                    </table>
                    <input type="hidden" name="final_penalty" id="hidden_penalty">
                    <input type="hidden" name="net_refund" id="hidden_refund">
                </div>
            </div>

            <div class="mt-3">
                <label class="small fw-bold">Remarks</label>
                <textarea name="remarks" class="form-control" rows="2"></textarea>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-danger btn-lg px-5">GENERATE CANCELLATION BILL</button>
            </div>
        </form>
    </div>
</div>

<script>
function calculateRefund() {
    const rentBase = parseFloat(<?= $booking['total_rent'] ?>);
    const gstBase = parseFloat(<?= $booking['total_tax'] ?>);
    const paidRent = parseFloat(<?= $paid_rent ?>);
    const paidRSD = parseFloat(<?= $paid_rsd ?>);
    
    let pc = parseFloat(document.getElementById('deduct_pc').value) || 0;
    let adj = parseFloat(document.getElementById('manual_adj').value) || 0;

    let penalty = ((rentBase * pc) / 100) + adj;
    // Logic: Refund is from (Paid Rent - GST) minus the Penalty
    let availableRent = Math.max(0, paidRent - gstBase);
    let refRent = Math.max(0, availableRent - penalty);
    let totalRef = refRent + paidRSD;

    document.getElementById('disp_penalty').innerText = penalty.toFixed(2);
    document.getElementById('disp_rent_ref').innerText = refRent.toFixed(2);
    document.getElementById('disp_rsd_ref').innerText = paidRSD.toFixed(2);
    document.getElementById('disp_total').innerText = totalRef.toFixed(2);
    
    document.getElementById('hidden_penalty').value = penalty.toFixed(2);
    document.getElementById('hidden_refund').value = totalRef.toFixed(2);
}
window.onload = calculateRefund;
</script>