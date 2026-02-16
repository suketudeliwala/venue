<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

if (!isset($_GET['id'])) { header("Location: venue_list.php"); exit; }
$id = intval($_GET['id']);
$res = $conn->query("SELECT * FROM vms_venues WHERE id = $id");
$v = $res->fetch_assoc();
?>

<div class="container-fluid d-flex flex-column min-vh-100">
    <div class="row flex-grow-1">
        <div class="col-12 p-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Venue: <?= htmlspecialchars($v['venue_name']) ?></h5>
                    <a href="venue_list.php" class="btn btn-sm btn-light">Back to List</a>
                </div>
                <div class="card-body p-4">
                    <form action="../api/update_venue.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $v['id'] ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Venue Name</label>
                                <input type="text" name="venue_name" class="form-control" value="<?= htmlspecialchars($v['venue_name']) ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Capacity</label>
                                <input type="number" name="capacity" class="form-control" value="<?= $v['capacity_person'] ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Size (Sq. Ft)</label>
                                <input type="number" name="sq_ft" class="form-control" value="<?= $v['sq_ft'] ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">A/C Status</label>
                                <select name="is_ac" class="form-select">
                                    <option value="1" <?= $v['is_ac'] ? 'selected' : '' ?>>A/C</option>
                                    <option value="0" <?= !$v['is_ac'] ? 'selected' : '' ?>>Non A/C</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">SGST (%)</label>
                                <input type="number" step="0.01" name="sgst_percent" class="form-control" value="<?= $v['sgst_percent'] ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">CGST (%)</label>
                                <input type="number" step="0.01" name="cgst_percent" class="form-control" value="<?= $v['cgst_percent'] ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Base Deposit (₹)</label>
                                <input type="number" name="base_deposit" class="form-control" value="<?= $v['base_deposit'] ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Update Photo (Optional)</label>
                                <input type="file" name="venue_image" class="form-control" accept="image/*">
                                <div class="mt-2">
                                    <small>Current: <?= $v['venue_image'] ?></small>
                                </div>
                            </div>

                            <div class="col-12 text-end mt-4">
                                <button type="submit" class="btn btn-warning px-5 fw-bold">Update Venue Record</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>