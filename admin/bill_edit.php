<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

if(!isset($_GET['id'])) { header("Location: billing_list_report.php"); exit; }
$invoice_id = intval($_GET['id']);

// 1. Fetch Invoice and linked Booking/Customer data
$sql = "SELECT i.*, b.tracking_no, b.function_name, c.contact_person, c.company_name
        FROM vms_invoices i
        JOIN vms_booking_master b ON i.booking_id = b.id
        JOIN vms_customers c ON b.customer_id = c.id
        WHERE i.id = $invoice_id";

$res = $conn->query($sql);
if($res->num_rows == 0) { die("Invoice not found."); }
$inv = $res->fetch_assoc();

// Security Check: If already fully paid, prevent editing
if($inv['final_balance'] <= 0) {
    echo "<div class='container mt-5'><div class='alert alert-warning'>This invoice is fully paid. Adjustments are no longer permitted.</div></div>";
    include("../includes/footer.php"); exit;
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Adjust Final Settlement</h4>
        <a href="billing_list_report.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Report</a>
    </div>

    <div class="card shadow border-0">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Invoice: <?= $inv['invoice_no'] ?> | <?= $inv['contact_person'] ?></h5>
        </div>
        <form action="../api/update_final_bill.php" method="POST" class="card-body">
            <input type="hidden" name="invoice_id" value="<?= $invoice_id ?>">
            <input type="hidden" name="advance_paid" id="advance_paid" value="<?= $inv['total_advance_paid'] ?>">
            
            <div class="row g-4">
                <div class="col-md-7 border-end">
                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Revise Charges</h6>
                    
                    <div class="mb-3 row">
                        <label class="col-sm-6 col-form-label">Base Rent:</label>
                        <div class="col-sm-6">
                            <input type="number" id="f_rent" name="f_rent" class="form-control text-end calc" value="<?= $inv['base_rent'] ?>">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-6 col-form-label">Extra Services:</label>
                        <div class="col-sm-6">
                            <input type="number" id="f_extra" name="f_extra" class="form-control text-end calc" value="<?= $inv['extra_charges'] ?>">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-6 col-form-label">Loss & Damages:</label>
                        <div class="col-sm-6">
                            <input type="number" id="f_damage" name="f_damage" class="form-control text-end calc" value="<?= $inv['total_damages'] ?>">
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-6 col-form-label text-danger">Discount (-):</label>
                        <div class="col-sm-6">
                            <input type="number" id="f_discount" name="f_discount" class="form-control text-end calc" value="<?= $inv['discount_amount'] ?>">
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label class="small fw-bold">Adjustment Reason / Narration</label>
                        <textarea name="narration" class="form-control" rows="3"><?= $inv['narration'] ?></textarea>
                    </div>
                </div>

                <div class="col-md-5">
                    <h6 class="fw-bold text-success mb-3 border-bottom pb-2">New Totals</h6>
                    <div class="bg-light p-3 rounded">
                        <div class="d-flex justify-content-between mb-2"><span>New Taxable Amount:</span> <span id="gross_total">₹0.00</span></div>
                        <div class="d-flex justify-content-between mb-2 small"><span>New CGST (9%):</span> <span id="cgst">₹0.00</span></div>
                        <div class="d-flex justify-content-between mb-2 small border-bottom pb-2"><span>New SGST (9%):</span> <span id="sgst">₹0.00</span></div>
                        <div class="d-flex justify-content-between h5 fw-bold mb-3"><span>New Grand Total:</span> <span id="grand">₹0.00</span></div>
                        
                        <div class="d-flex justify-content-between text-muted mb-2">
                            <span>Advance Already Paid:</span>
                            <span>₹<?= number_format($inv['total_advance_paid'], 2) ?></span>
                        </div>
                        <hr>
                        <div class="p-2 rounded text-center h4 fw-bold" id="bal_box">
                            <div class="small fw-normal" id="bal_label">New Balance Receivable</div>
                            <span id="net_val">₹0.00</span>
                        </div>
                        <input type="hidden" name="final_balance" id="final_balance_input">
                    </div>
                </div>
            </div>

            <div class="text-end mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary px-5 btn-lg">Update Invoice & Recalculate Balance</button>
            </div>
        </form>
    </div>
</div>

<script>
function calculate() {
    let rent = parseFloat(document.getElementById('f_rent').value) || 0;
    let extra = parseFloat(document.getElementById('f_extra').value) || 0;
    let dmg = parseFloat(document.getElementById('f_damage').value) || 0;
    let disc = parseFloat(document.getElementById('f_discount').value) || 0;
    let received = parseFloat(document.getElementById('advance_paid').value) || 0;

    let taxable = (rent + extra + dmg) - disc;
    let tax = taxable * 0.09;
    let grand = taxable + (tax * 2);
    let net = grand - received;

    document.getElementById('gross_total').innerText = '₹' + taxable.toFixed(2);
    document.getElementById('cgst').innerText = '₹' + tax.toFixed(2);
    document.getElementById('sgst').innerText = '₹' + tax.toFixed(2);
    document.getElementById('grand').innerText = '₹' + grand.toFixed(2);
    document.getElementById('net_val').innerText = '₹' + Math.abs(net).toFixed(2);
    document.getElementById('final_balance_input').value = net.toFixed(2);

    let box = document.getElementById('bal_box');
    let lbl = document.getElementById('bal_label');
    if(net > 0) {
        box.className = "p-2 rounded text-center h4 fw-bold bg-danger-subtle text-danger";
        lbl.innerText = "New Balance Receivable";
    } else {
        box.className = "p-2 rounded text-center h4 fw-bold bg-success-subtle text-success";
        lbl.innerText = "New Refundable Amount";
    }
}
document.querySelectorAll('.calc').forEach(el => el.addEventListener('input', calculate));
window.onload = calculate;
</script>

<?php include("../includes/footer.php"); ?>