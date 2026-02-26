<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

$ur_id = intval($_GET['id']);
$sql = "SELECT ur.*, b.tracking_no, b.function_name, v.venue_name, c.contact_person,
        d.contact_person as decorator, cat.contact_person as caterer
        FROM vms_utilization_reports ur
        JOIN vms_booking_master b ON ur.booking_id = b.id
        JOIN vms_booking_slots s ON ur.slot_id = s.id
        JOIN vms_venues v ON s.venue_id = v.id
        JOIN vms_customers c ON b.customer_id = c.id
        LEFT JOIN vms_customers d ON ur.decorator_id = d.id
        LEFT JOIN vms_customers cat ON ur.caterer_id = cat.id
        WHERE ur.id = $ur_id";
$res = $conn->query($sql);
$data = $res->fetch_assoc();
?>

<div class="container py-4 text-center d-print-none">
    <button onclick="window.print()" class="btn btn-dark">Print Utilization Certificate</button>
</div>

<div class="p-5 bg-white border mx-auto printable-area" style="max-width: 850px; border: 2px solid #000 !important;">
    <div class="row align-items-center border-bottom border-dark pb-3 mb-4">
        <div class="col-2"><img src="../assets/images/org_logo.png" style="width: 80px;"></div>
        <div class="col-10 text-center">
            <h3 class="fw-bold"><?= $org_full_name ?></h3>
            <h5 class="text-decoration-underline">VENUE UTILIZATION & POSSESSION REPORT (V.U.R)</h5>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-6"><strong>VUR No:</strong> <?= $ur_id ?>-W</div>
        <div class="col-6 text-end"><strong>Date:</strong> <?= date('d-M-Y', strtotime($data['report_date'])) ?></div>
    </div>

    <table class="table table-bordered border-dark">
        <tr><th width="30%">Venue Name</th><td><?= $data['venue_name'] ?></td></tr>
        <tr><th>Function Name</th><td><?= $data['function_name'] ?> (<?= $data['tracking_no'] ?>)</td></tr>
        <tr><th>Customer Name</th><td><?= $data['contact_person'] ?></td></tr>
        <tr><th>Actual Timings</th><td>Entry: <?= $data['actual_start_time'] ?> | Exit: <?= $data['actual_end_time'] ?></td></tr>
        <tr><th>Electricity Units</th><td>Start: <?= $data['eb_reading_start'] ?> | End: <?= $data['eb_reading_end'] ?></td></tr>
        <tr><th>Service Providers</th><td>Decorator: <?= $data['decorator'] ?: 'N/A' ?> | Caterer: <?= $data['caterer'] ?: 'N/A' ?></td></tr>
    </table>

    <h6 class="fw-bold mt-4">DAMAGES & EXTRA SERVICES</h6>
    <div class="border border-dark p-3 mb-4" style="min-height: 100px;">
        <strong>Damages:</strong> <?= $data['damage_details'] ?: 'None' ?> (₹<?= number_format($data['damage_charges'], 2) ?>)<br>
        <strong>Extras:</strong> <?= $data['extra_services_details'] ?: 'None' ?> (₹<?= number_format($data['extra_services_charges'], 2) ?>)
    </div>

    <div class="row mt-5 pt-5">
        <div class="col-6 text-center"><p class="border-top border-dark pt-2">Venue Manager Signature</p></div>
        <div class="col-6 text-center"><p class="border-top border-dark pt-2">Customer/Party Signature</p></div>
    </div>
</div>