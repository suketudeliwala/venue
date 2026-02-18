<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

if (!isset($_GET['id'])) { header("Location: blocked_dates.php"); exit; }
$id = intval($_GET['id']);
$block = $conn->query("SELECT * FROM vms_blocked_dates WHERE id = $id")->fetch_assoc();
?>

<div class="container-fluid py-4 min-vh-100">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow border-0">
                <div class="card-header bg-danger text-white py-3">
                    <h5 class="mb-0"><i class="bi bi-calendar-x me-2"></i>Edit Reserved Date</h5>
                </div>
                <div class="card-body p-4">
                    <form action="../api/update_blocked_dates.php" method="POST">
                        <input type="hidden" name="id" value="<?= $block['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Date</label>
                            <input type="date" name="block_date" class="form-control" value="<?= $block['block_date'] ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Reason for Blocking</label>
                            <input type="text" name="reason" class="form-control" value="<?= htmlspecialchars($block['reason']) ?>" required>
                        </div>
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_hard_block" id="hardBlock" value="1" <?= $block['is_hard_block'] ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold" for="hardBlock">Strict Block (No Admin Override allowed)</label>
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="blocked_dates.php" class="btn btn-light">Back</a>
                            <button type="submit" class="btn btn-danger px-4">Update Restriction</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>