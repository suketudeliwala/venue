<?php
include("../includes/config.php");
include("../includes/header_admin.php");

if(!isset($_GET['voucher_no'])) { die("Voucher Number required."); }
$v_no = mysqli_real_escape_string($conn, $_GET['voucher_no']);

// Fetch Refund Data with Booking, Customer, and Cancellation Bill details
$sql = "SELECT r.*, b.tracking_no, b.function_name, c.contact_person, c.mobile, c.address, cb.cb_no
        FROM vms_rsd_refunds r
        JOIN vms_booking_master b ON r.booking_id = b.id
        JOIN vms_customers c ON b.customer_id = c.id
        LEFT JOIN vms_cancellation_bills cb ON b.id = cb.booking_id
        WHERE r.voucher_no = '$v_no'";

$res = $conn->query($sql);
if($res->num_rows == 0) die("Voucher record not found.");
$data = $res->fetch_assoc();
?>

<div class="container my-4 no-print text-end">
    <button onclick="window.print()" class="btn btn-primary shadow-sm"><i class="bi bi-printer me-2"></i>Print Official Voucher</button>
    <a href="booking_list.php" class="btn btn-secondary shadow-sm ms-2">Back to List</a>
</div>

<div class="container bg-white p-5 border shadow-sm rounded" id="printableArea">
    <div class="row border-bottom pb-3 mb-4">
        <div class="col-8">
            <h2 class="fw-bold text-navy text-uppercase">Refund Payout Voucher</h2>
            <p class="mb-0">Ref: <strong><?= $data['tracking_no'] ?></strong> | Type: <?= $data['refund_type'] ?></p>
            <?php if(!empty($data['cb_no'])): ?>
                <p class="mb-0 text-danger fw-bold small">Against Cancellation Bill: <?= $data['cb_no'] ?></p>
            <?php endif; ?>
        </div>
        <div class="col-4 text-end">
            <div class="h5 fw-bold mb-0">No: <?= $data['voucher_no'] ?></div>
            <div class="text-muted">Date: <?= date('d-M-Y', strtotime($data['refund_date'])) ?></div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-6">
            <h6 class="text-muted small fw-bold text-uppercase">Billed To:</h6>
            <div class="fw-bold h5 mb-1"><?= htmlspecialchars($data['contact_person']) ?></div>
            <div class="small text-muted"><?= nl2br(htmlspecialchars($data['address'])) ?></div>
        </div>
        <div class="col-6 text-end">
            <h6 class="text-muted small fw-bold text-uppercase">Payment Source:</h6>
            <div class="fw-bold"><?= htmlspecialchars($data['trust_bank_details']) ?></div>
            <div class="small mt-1">Event: <?= htmlspecialchars($data['function_name']) ?></div>
        </div>
    </div>

    <div class="row g-0 border mb-4">
        <div class="col-6 border-end p-0">
            <div class="bg-light text-center py-2 fw-bold border-bottom">1. RENT REFUND PAYMENT</div>
            <div class="p-3">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td>Amount:</td><td class="text-end fw-bold">₹ <?= number_format($data['rent_refund_amount'], 2) ?></td></tr>
                    <tr><td>Mode:</td><td class="text-end fw-bold"><?= $data['rent_payment_mode'] ?></td></tr>
                    <tr><td>Ref No:</td><td class="text-end"><?= $data['rent_ref_no'] ?></td></tr>
                </table>
            </div>
        </div>
        <div class="col-6 p-0">
            <div class="bg-light text-center py-2 fw-bold border-bottom">2. RSD REFUND PAYMENT</div>
            <div class="p-3">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td>Amount:</td><td class="text-end fw-bold">₹ <?= number_format($data['rsd_refund_amount'], 2) ?></td></tr>
                    <tr><td>Mode:</td><td class="text-end fw-bold"><?= $data['rsd_payment_mode'] ?></td></tr>
                    <tr><td>Ref No:</td><td class="text-end"><?= $data['rsd_ref_no'] ?></td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 text-end">
            <h4 class="fw-bold">TOTAL REFUNDED: ₹ <?= number_format($data['total_refund_amount'], 2) ?></h4>
        </div>
    </div>

    <div class="mt-4 p-3 bg-light border rounded">
        <strong>Remarks:</strong> <?= !empty($data['remarks']) ? nl2br(htmlspecialchars($data['remarks'])) : "No specific remarks." ?>
    </div>

    <div class="row mt-5 pt-5 text-center">
        <div class="col-4">
            <hr class="w-75 mx-auto"><small class="fw-bold">Receiver's Signature</small>
        </div>
        <div class="col-4">
            <hr class="w-75 mx-auto"><small class="fw-bold">Verified By</small>
        </div>
        <div class="col-4">
            <hr class="w-75 mx-auto"><small class="fw-bold">For Trust / Organisation</small>
        </div>
    </div>
</div>