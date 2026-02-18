<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

if (!isset($_GET['id'])) { header("Location: venue_rates.php"); exit; }
$id = intval($_GET['id']);
$rate = $conn->query("SELECT r.*, v.venue_name FROM vms_venue_rates r JOIN vms_venues v ON r.venue_id = v.id WHERE r.id = $id")->fetch_assoc();
?>

<div class="container-fluid py-4 min-vh-100">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0">
                <div class="card-header bg-navy text-white py-3" style="background-color: #001d4a !important;">
                    <h5 class="mb-0"><i class="bi bi-currency-exchange me-2"></i>Edit Rate: <?= htmlspecialchars($rate['venue_name']) ?> (<?= htmlspecialchars($rate['duration_label']) ?>)</h5>
                </div>
                <div class="card-body p-4">
                    <form action="../api/update_venue_rate.php" method="POST">
                        <input type="hidden" name="id" value="<?= $rate['id'] ?>">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Duration Label</label>
                                <input type="text" name="duration_label" class="form-control" value="<?= htmlspecialchars($rate['duration_label']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Member Rate (₹)</label>
                                <input type="number" step="0.01" name="member_rate" class="form-control" value="<?= $rate['member_rate'] ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Non-Member Rate (₹)</label>
                                <input type="number" step="0.01" name="non_member_rate" class="form-control" value="<?= $rate['non_member_rate'] ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">RSD Amount (₹)</label>
                                <input type="number" step="0.01" name="rsd_amount" class="form-control" value="<?= $rate['rsd_amount'] ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Late Fee / Hour (₹)</label>
                                <input type="number" step="0.01" name="late_fee_per_hour" class="form-control" value="<?= $rate['late_fee_per_hour'] ?>" required>
                            </div>
                            <div class="col-12 text-end mt-4 border-top pt-3">
                                <a href="venue_rates.php" class="btn btn-light me-2">Cancel</a>
                                <button type="submit" class="btn btn-warning px-5 fw-bold">Update Rate</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>