<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

if(!isset($_GET['booking_id'])) {
    die("<div class='container mt-5 alert alert-danger'>Error: No Booking ID provided.</div>");
}

$booking_id = intval($_GET['booking_id']);

// 1. Fetch Comprehensive Data
$b = $conn->query("SELECT b.*, c.contact_person, c.company_name, c.address 
                   FROM vms_booking_master b 
                   JOIN vms_customers c ON b.customer_id = c.id 
                   WHERE b.id = $booking_id")->fetch_assoc();

if(!$b) { die("Booking not found."); }

$util = $conn->query("SELECT SUM(damage_charges) as dmg, SUM(extra_services_charges) as extra 
                      FROM vms_utilization_reports 
                      WHERE booking_id = $booking_id")->fetch_assoc();

// 2. REFINED ACCOUNTING LOGIC: Isolate Rent Receipts vs RSD Receipts
$receipts = $conn->query("SELECT SUM(amount_rent) as total_rent_paid, SUM(amount_rsd) as total_rsd_held 
                          FROM vms_receipts 
                          WHERE booking_id = $booking_id")->fetch_assoc();

$advance_rent_paid = $receipts['total_rent_paid'] ?? 0; // Only Rent portion
$rsd_held = $receipts['total_rsd_held'] ?? 0;         // Security portion (held aside)

$slots = $conn->query("SELECT s.*, v.venue_name FROM vms_booking_slots s JOIN vms_venues v ON s.venue_id = v.id WHERE s.booking_id = $booking_id");
?>

<div class="container py-4">
    <form action="../api/save_final_bill.php" method="POST">
        <input type="hidden" name="booking_id" value="<?= $booking_id ?>">
        
        <div class="card mb-4 border-0 shadow-sm bg-light">
            <div class="card-body row align-items-center">
                <div class="col-md-5 border-end">
                    <h6 class="text-muted small">CUSTOMER</h6>
                    <h5 class="mb-0"><?= htmlspecialchars($b['contact_person']) ?></h5>
                    <small><?= htmlspecialchars($b['company_name']) ?></small>
                </div>
                <div class="col-md-4 border-end">
                    <h6 class="text-muted small">EVENT</h6>
                    <h5 class="mb-0"><?= htmlspecialchars($b['function_name']) ?></h5>
                    <small class="text-primary"><?= $b['tracking_no'] ?></small>
                </div>
                <div class="col-md-3 text-center">
                    <h6 class="text-muted small">SECURITY DEPOSIT (RSD)</h6>
                    <h5 class="text-info mb-0">₹<?= number_format($rsd_held, 2) ?></h5>
                    <small class="text-muted">(Held separately)</small>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm p-4">
                    <h6 class="fw-bold border-bottom pb-2 mb-3">Reconciliation of Charges</h6>
                    
                    <div class="mb-3 row">
                        <label class="col-md-7 col-form-label">Base Rent (Agreed):</label>
                        <div class="col-md-5"><input type="number" id="f_rent" name="f_rent" class="form-control text-end calc" value="<?= $b['total_rent'] ?>"></div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-7 col-form-label">Extra Services / Overtime:</label>
                        <div class="col-md-5"><input type="number" id="f_extra" name="f_extra" class="form-control text-end calc" value="<?= $util['extra'] ?>"></div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-7 col-form-label">Loss & Damages:</label>
                        <div class="col-md-5"><input type="number" id="f_damage" name="f_damage" class="form-control text-end calc" value="<?= $util['dmg'] ?>"></div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-md-7 col-form-label text-danger">Less: Discount / Allowance (-):</label>
                        <div class="col-md-5"><input type="number" id="f_discount" name="f_discount" class="form-control text-end calc" value="0"></div>
                    </div>

                    <div class="mb-3 row border-top pt-3">
                        <label class="col-md-7 col-form-label h5 fw-bold">Taxable Amount:</label>
                        <div class="col-md-5 text-end"><h5 id="gross_total" class="fw-bold">₹0.00</h5></div>
                    </div>
                    
                    <div class="mt-3">
                        <label class="small fw-bold">Final Settlement Remarks</label>
                        <textarea name="narration" class="form-control" rows="2" placeholder="e.g. 5% discount allowed due to A/C issue..."></textarea>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-4 bg-dark text-white mb-3">
                    <h6 class="fw-bold border-bottom pb-2 mb-3 text-secondary">Tax Calculation</h6>
                    <div class="d-flex justify-content-between mb-2"><span>CGST (9%)</span> <span id="cgst">₹0.00</span></div>
                    <div class="d-flex justify-content-between mb-2"><span>SGST (9%)</span> <span id="sgst">₹0.00</span></div>
                    <hr>
                    <div class="d-flex justify-content-between h4 fw-bold"><span>Grand Total</span> <span id="grand">₹0.00</span></div>
                </div>

                <div class="card border-0 shadow-sm p-4">
                    <h6 class="fw-bold text-success border-bottom pb-2 mb-3">Advance Reconciliation</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Rent Advance:</span>
                        <span class="fw-bold text-success">₹<?= number_format($advance_rent_paid, 2) ?></span>
                    </div>
                    
                    <div class="mt-4 p-3 rounded text-center border" id="balance_box">
                        <div class="small fw-bold" id="bal_label">Net Receivable</div>
                        <h2 class="mb-0" id="net_val">₹0.00</h2>
                    </div>
                    
                    <p class="small text-muted mt-3 text-center">
                        <i class="bi bi-info-circle me-1"></i> RSD of <strong>₹<?= number_format($rsd_held, 2) ?></strong> is currently withheld and excluded from this calculation.
                    </p>
                    <input type="hidden" name="final_balance" id="final_balance_input">

                    <button type="submit" class="btn btn-warning btn-lg w-100 mt-3 fw-bold shadow">
                        SAVE FINAL BILL
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function calculate() {
    let rent = parseFloat(document.getElementById('f_rent').value) || 0;
    let extra = parseFloat(document.getElementById('f_extra').value) || 0;
    let dmg = parseFloat(document.getElementById('f_damage').value) || 0;
    let disc = parseFloat(document.getElementById('f_discount').value) || 0;
    let rentReceived = <?= $advance_rent_paid ?>;

    let taxable = (rent + extra + dmg) - disc;
    let tax = taxable * 0.09;
    let grandTotal = taxable + (tax * 2);
    let net = grandTotal - rentReceived;

    document.getElementById('gross_total').innerText = '₹' + taxable.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('cgst').innerText = '₹' + tax.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('sgst').innerText = '₹' + tax.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('grand').innerText = '₹' + grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('net_val').innerText = '₹' + Math.abs(net).toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('final_balance_input').value = net.toFixed(2);

    let box = document.getElementById('balance_box');
    let lbl = document.getElementById('bal_label');
    
    if(net > 0) {
        box.className = "mt-4 p-3 rounded text-center bg-danger-subtle text-danger border-danger";
        lbl.innerText = "Final Net Receivable";
    } else {
        box.className = "mt-4 p-3 rounded text-center bg-success-subtle text-success border-success";
        lbl.innerText = "Refundable to Customer";
    }
}

document.querySelectorAll('.calc').forEach(el => el.addEventListener('input', calculate));
window.onload = calculate;
</script>

<?php include("../includes/footer.php"); ?>