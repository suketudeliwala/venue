<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

if (!isset($_GET['id'])) { header("Location: equipment_list.php"); exit; }
$id = intval($_GET['id']);
$res = $conn->query("SELECT * FROM vms_equipments WHERE id = $id");
$eq = $res->fetch_assoc();
?>

<div class="container-fluid d-flex flex-column min-vh-100">
    <div class="row flex-grow-1 p-4">
        <div class="col-md-6 mx-auto">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">Edit Equipment: <?= htmlspecialchars($eq['equipment_name']) ?></h5>
                </div>
                <div class="card-body p-4">
                    <form action="../api/update_equipment.php" method="POST">
                        <input type="hidden" name="id" value="<?= $eq['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Equipment Name</label>
                            <input type="text" name="equipment_name" class="form-control" value="<?= htmlspecialchars($eq['equipment_name']) ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Quantity</label>
                                <input type="number" name="total_quantity" class="form-control" value="<?= $eq['total_quantity'] ?>" min="1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Daily Rent (₹)</label>
                                <input type="number" step="0.01" name="daily_rent" class="form-control" value="<?= $eq['daily_rent'] ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="Available" <?= $eq['status'] == 'Available' ? 'selected' : '' ?>>Available</option>
                                <option value="Under Repair" <?= $eq['status'] == 'Under Repair' ? 'selected' : '' ?>>Under Repair</option>
                                <option value="Damaged" <?= $eq['status'] == 'Damaged' ? 'selected' : '' ?>>Damaged</option>
                            </select>
                        </div>
                        <div class="text-end border-top pt-3">
                            <a href="equipment_list.php" class="btn btn-light me-2">Cancel</a>
                            <button type="submit" class="btn btn-warning px-4 fw-bold">Update Equipment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>