<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 
?>

<div class="container-fluid d-flex flex-column min-vh-100">
    <div class="row flex-grow-1 p-4">
        <div class="col-md-6 mx-auto">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">Add New Equipment</h5>
                </div>
                <div class="card-body p-4">
                    <form action="../api/save_equipment.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Equipment Name</label>
                            <input type="text" name="equipment_name" class="form-control" placeholder="e.g. Projector, Whiteboard" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Quantity</label>
                                <input type="number" name="total_quantity" class="form-control" value="1" min="1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Daily Rent (₹)</label>
                                <input type="number" step="0.01" name="daily_rent" class="form-control" placeholder="0.00">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Initial Status</label>
                            <select name="status" class="form-select">
                                <option value="Available">Available</option>
                                <option value="Under Repair">Under Repair</option>
                            </select>
                        </div>
                        <div class="text-end border-top pt-3">
                            <a href="equipment_list.php" class="btn btn-light me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Save Equipment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>