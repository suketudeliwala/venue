<?php
include("../includes/config.php");
include("../includes/header_admin.php");

$cb_no = mysqli_real_escape_string($conn, $_GET['cb_no']);
$sql = "SELECT cb.*, b.tracking_no, b.function_name, c.contact_person, c.company_name, c.address, c.mobile 
        FROM vms_cancellation_bills cb
        JOIN vms_booking_master b ON cb.booking_id = b.id
        JOIN vms_customers c ON b.customer_id = c.id
        WHERE cb.cb_no = '$cb_no'";

$res = $conn->query($sql);
$data = $res->fetch_assoc();
if(!$data) die("Bill not found.");
?>

<div class="container my-5 p-5 bg-white border">
    <div class="row border-bottom pb-3 mb-4">
        <div class="col-8">
            <h3 class="fw-bold">CANCELLATION BILL</h3>
            <p class="mb-0">Ref: <?= $data['tracking_no'] ?> | <?= $data['function_name'] ?></p>
        </div>
        <div class="col-4 text-end">
            <h5 class="mb-0">#<?= $data['cb_no'] ?></h5>
            <p>Date: <?= date('d-M-Y', strtotime($data['cancellation_date'])) ?></p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-6">
            <strong><?= $data['contact_person'] ?></strong><br>
            <?= $data['company_name'] ?><br><?= $data['address'] ?>
        </div>
    </div>

    <table class="table table-bordered">
        <thead class="table-light">
            <tr><th>Settlement Details</th><th class="text-end">Amount (₹)</th></tr>
        </thead>
        <tbody>
            <tr><td>Original Rent (Excl. GST)</td><td class="text-end"><?= number_format($data['total_rent_original'], 2) ?></td></tr>
            <tr><td>Original GST (Non-Refundable)</td><td class="text-end"><?= number_format($data['gst_original'], 2) ?></td></tr>
            <tr><td>Security Deposit (RSD)</td><td class="text-end"><?= number_format($data['rsd_original'], 2) ?></td></tr>
            <tr class="text-danger fw-bold">
                <td>Penalty (<?= $data['deduction_percent'] ?>%) 
                <?php if($data['adjustment_amount'] != 0): ?><br><small>Adj: ₹<?= $data['adjustment_amount'] ?></small><?php endif; ?>
                </td>
                <td class="text-end">- <?= number_format($data['deduction_amount'], 2) ?></td>
            </tr>
            <tr class="table-success h5 fw-bold">
                <td>NET REFUNDABLE AMOUNT</td>
                <td class="text-end">₹ <?= number_format($data['net_refund_amount'], 2) ?></td>
            </tr>
        </tbody>
    </table>

    <div class="mt-4"><strong>Remarks:</strong> <?= $data['remarks'] ?></div>
    <div class="mt-5 text-center no-print"><button onclick="window.print()" class="btn btn-primary">Print Bill</button></div>
</div>