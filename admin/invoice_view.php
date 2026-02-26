<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

// Fix for Warning: Undefined array key "id"
if(!isset($_GET['id']) || empty($_GET['id'])) {
    die("<div class='container mt-5 alert alert-danger'>Error: Invoice ID is required to view this page.</div>");
}

$invoice_id = intval($_GET['id']);
$print_mode = $_GET['mode'] ?? 'without';

// 1. Fetch Invoice, Master Booking, and Customer Details
$sql = "SELECT i.*, b.id as b_master_id, b.tracking_no as b_tracking, c.contact_person, c.company_name, c.address, 
               (SELECT id FROM vms_utilization_reports WHERE booking_id = i.booking_id LIMIT 1) as vur_no
        FROM vms_invoices i
        JOIN vms_booking_master b ON i.booking_id = b.id
        JOIN vms_customers c ON b.customer_id = c.id
        WHERE i.id = $invoice_id";

$res = $conn->query($sql);
if($res->num_rows == 0) { die("Invoice not found."); }
$inv = $res->fetch_assoc();

// 2. Fix for Fatal Error: SAC Code Query
// Using the actual booking_id from the invoice record
$v_id = $inv['booking_id'];
$venue_sql = "SELECT v.venue_name, v.sac_code 
              FROM vms_booking_slots s 
              JOIN vms_venues v ON s.venue_id = v.id 
              WHERE s.booking_id = $v_id LIMIT 1";
$venue_res = $conn->query($venue_sql);
$venue = ($venue_res && $venue_res->num_rows > 0) ? $venue_res->fetch_assoc() : ['venue_name' => 'Venue Services', 'sac_code' => '997212'];

// 3. Fetch Organization Settings
$org_res = $conn->query("SELECT * FROM organization_settings LIMIT 1");
$org = ($org_res && $org_res->num_rows > 0) ? $org_res->fetch_assoc() : ['trust_name' => 'Trust Organization'];
?>

<div class="container py-4 d-print-none">
    <div class="alert alert-info d-flex justify-content-between align-items-center">
        <span>Select Print Mode:</span>
        <div class="btn-group">
            <a href="?id=<?= $invoice_id ?>&mode=with" class="btn btn-sm <?= $print_mode == 'with' ? 'btn-primary' : 'btn-outline-primary' ?>">With Letterhead</a>
            <a href="?id=<?= $invoice_id ?>&mode=without" class="btn btn-sm <?= $print_mode == 'without' ? 'btn-primary' : 'btn-outline-primary' ?>">Without Letterhead</a>
        </div>
        <button onclick="window.print()" class="btn btn-dark btn-sm"><i class="bi bi-printer me-2"></i>Print Invoice</button>
    </div>
</div>

<div class="invoice-box bg-white p-5 shadow-sm mx-auto" style="max-width: 800px; color: #000; font-family: 'Times New Roman', serif;">
    
    <?php if($print_mode == 'without'): ?>
        <div class="text-center border-bottom pb-3 mb-4">
            <h2 class="fw-bold mb-0 text-uppercase"><?= $org['org_full_name'] ?></h2>
            <p class="mb-0 small text-muted"><?= $org['org_regd_no'] ?></p>
            <p class="mb-0 small"><?= $org['org_address'] ?> | Tel: <?= $org['org_comm_phone'] ?></p>
            <p class="fw-bold mb-0 small">GSTIN No: <?= $org['org_gst_no'] ?> | PAN NO: <?= $org['org_pan_no'] ?></p>
        </div>
    <?php else: ?>
        <div style="height: 150px;"></div> <?php endif; ?>

    <h4 class="text-center fw-bold text-decoration-underline mb-4">TAX INVOICE</h4>

    <div class="row mb-4">
        <div class="col-6">
            <p class="mb-1"><strong>Invoice No:</strong> <?= $inv['invoice_no'] ?></p>
            <p class="mb-1"><strong>Date:</strong> <?= date('d.m.Y', strtotime($inv['invoice_date'])) ?></p>
            <p class="mb-1"><strong>Name of Party:</strong> <?= $inv['contact_person'] ?></p>
            <p class="mb-1"><strong>Address:</strong> <?= $inv['address'] ?></p>
        </div>
        <div class="col-6 text-end">
            <p class="mb-1"><strong>Place of Supply:</strong> Maharashtra</p>
            <p class="mb-1"><strong>Form No:</strong> <?= $inv['booking_id'] ?></p>
            <p class="mb-1"><strong>V.U.R. No:</strong> <?= $inv['vur_no'] ?></p>
        </div>
    </div>

    <table class="table table-bordered border-dark">
        <thead class="bg-light text-center">
            <tr>
                <th style="width: 10%;">Sr.No.</th>
                <th>Particulars</th>
                <th style="width: 20%;">SAC Code</th>
                <th style="width: 25%;">Total Invoice Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>
                    <strong><?= $venue['venue_name'] ?></strong><br>
                    <small>Extension Charges: ₹<?= number_format($inv['extra_charges'], 2) ?></small><br>
                    <small>Breakage/Other: ₹<?= number_format($inv['total_damages'], 2) ?></small>
                    <?php if($inv['discount_amount'] > 0): ?>
                        <br><small class="text-danger">Less Discount: -₹<?= number_format($inv['discount_amount'], 2) ?></small>
                    <?php endif; ?>
                </td>
                <td class="text-center"><?= $venue['sac_code'] ?></td>
                <td class="text-end"><?= number_format($inv['base_rent'], 2) ?></td>
            </tr>
            <tr>
                <td colspan="3" class="text-end fw-bold">Total Taxable Value:</td>
                <td class="text-end fw-bold">₹<?= number_format($inv['taxable_amount'], 2) ?></td>
            </tr>
            <tr>
                <td colspan="3" class="text-end">SGST @ 9%</td>
                <td class="text-end">₹<?= number_format($inv['sgst_amount'], 2) ?></td>
            </tr>
            <tr>
                <td colspan="3" class="text-end border-bottom-0">CGST @ 9%</td>
                <td class="text-end border-bottom-0">₹<?= number_format($inv['cgst_amount'], 2) ?></td>
            </tr>
            <tr class="table-dark text-white">
                <td colspan="3" class="text-end h5 fw-bold">GRAND TOTAL:</td>
                <td class="text-end h5 fw-bold">₹<?= number_format($inv['grand_total'], 2) ?></td>
            </tr>
        </tbody>
    </table>

    <div class="row mt-3 p-2 bg-light border rounded mx-0">
        <div class="col-8 small">
            <span class="text-muted">Net Reconciliation:</span><br>
            Grand Total (A) - Total Advance Paid (B) = <strong>Final Balance</strong>
        </div>
        <div class="col-4 text-end">
            <small>₹<?= number_format($inv['grand_total'], 2) ?> - ₹<?= number_format($inv['total_advance_paid'], 2) ?></small><br>
            <span class="fw-bold <?= $inv['final_balance'] > 0 ? 'text-danger' : 'text-success' ?>">
                <?= $inv['final_balance'] > 0 ? 'Payable: ' : 'Refundable: ' ?> ₹<?= number_format(abs($inv['final_balance']), 2) ?>
            </span>
        </div>
    </div>

    <div class="mt-5 pt-4 border-top">
        <div class="row">
            <div class="col-7">
                <p class="fw-bold small mb-1">Terms & Conditions:</p>
                <ol class="small text-muted ps-3">
                    <li>This is a computer-generated tax invoice.</li>
                    <li>Payment for "Payable" balance must be cleared within 3 working days.</li>
                    <li>Security Deposit (RSD) refunds are processed separately within 15 days of final billing.</li>
                    <li>Subject to Mumbai Jurisdiction.</li>
                </ol>
            </div>
            <div class="col-5 text-center">
                <p class="small mb-5">For <?= $org['org_full_name'] ?></p>
                <p class="fw-bold border-top pt-2 small">Authorized Signatory</p>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body { background: #fff; }
    .d-print-none { display: none !important; }
    .invoice-box { box-shadow: none !important; border: none !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
    .table td, .table th { border: 1px solid #000 !important; }
}
</style>

<?php include("../includes/footer.php"); ?>