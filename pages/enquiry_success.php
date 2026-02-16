<?php 
include("../includes/config.php"); 
include("../includes/header.php"); 
$tracking_no = isset($_GET['ref']) ? htmlspecialchars($_GET['ref']) : 'N/A';
?>

<style>
/* CSS to handle A4 Single Page Print */
@media print {
    header, footer, nav, .btn, .d-print-none { display: none !important; }
    body { background: white !important; margin: 0; padding: 0; }
    .print-container { width: 100%; border: none !important; padding: 20px; }
    .letterhead { display: block !important; text-align: center; border-bottom: 2px solid #001d4a; margin-bottom: 30px; }
}
.letterhead { display: none; }
</style>

<div class="container py-5 min-vh-100 print-container">
    <div class="letterhead">
        <h2 class="fw-bold" style="color: #001d4a;"><?= $org_full_name ?></h2>
        <p><?= $org_address ?><br>Regd No: <?= $org_regd ?></p>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="mb-4 d-print-none">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
            </div>
            
            <h2 class="fw-bold text-navy mb-3">Enquiry Submitted Successfully</h2>
            <p class="text-muted">We have received your application. Our team will contact you shortly.</p>

            <div class="card border-0 bg-light rounded-4 my-4 p-4">
                <span class="text-uppercase small fw-bold text-muted">Tracking Reference Number</span>
                <h1 class="text-primary fw-bold" style="letter-spacing: 3px;"><?= $tracking_no ?></h1>
            </div>

            <div class="alert alert-info text-start border-0 shadow-sm">
                <strong>Important:</strong> Please present this tracking number at the Trust office for any follow-up. 
                <br>Contact: <?= $org_comm_phone ?> | Email: <?= $org_comm_email ?>
            </div>

            <div class="mt-5 d-print-none">
                <button onclick="window.print()" class="btn btn-primary px-5 rounded-pill shadow">Print Acknowledgement</button>
                <a href="../index.php" class="btn btn-outline-secondary px-4 rounded-pill ms-2">Back to Home</a>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>