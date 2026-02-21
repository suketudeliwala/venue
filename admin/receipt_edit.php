<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

$id = intval($_GET['id']);
$receipt = $conn->query("SELECT r.*, b.tracking_no, b.net_payable FROM vms_receipts r JOIN vms_booking_master b ON r.booking_id = b.id WHERE r.id = $id")->fetch_assoc();
?>

<div class="container py-4">
    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white">Edit Receipt: <?= $receipt['receipt_no'] ?></div>
        <form action="../api/update_receipt.php" method="POST" class="card-body">
            <input type="hidden" name="receipt_id" value="<?= $id ?>">
            <input type="hidden" name="booking_id" value="<?= $receipt['booking_id'] ?>">
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Rent Allocation</label>
                    <input type="number" step="0.01" name="amount_rent" class="form-control" value="<?= $receipt['amount_rent'] ?>" required>
                </div>
                <div class="col-md-6">
                    <label>RSD Allocation</label>
                    <input type="number" step="0.01" name="amount_rsd" class="form-control" value="<?= $receipt['amount_rsd'] ?>" required>
                </div>
                <div class="col-md-12 text-end mt-3">
                    <button type="submit" class="btn btn-primary">Update Receipt & Recalculate Balance</button>
                </div>
            </div>
        </form>
    </div>
</div>