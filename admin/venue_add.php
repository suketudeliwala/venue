<?php 
include("../includes/config.php"); // Your DB connection
include("../includes/header_admin.php"); 
?>

<div class="container mt-5">
    <div class="card vms-card border-0">
        <div class="card-header bg-primary text-white py-3">
            <h4 class="mb-0">Create New Venue Master</h4>
        </div>
        <div class="card-body bg-light">
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-<?= ($_GET['status'] == 'success') ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-2"></i> <?= htmlspecialchars($_GET['msg']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

            <form action="../api/save_venue.php" method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Venue Name</label>
                        <input type="text" name="venue_name" class="form-control" placeholder="e.g. Grand Banquet Hall" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Capacity (Persons)</label>
                        <input type="number" name="capacity" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">A/C Available?</label>
                        <select name="is_ac" class="form-select">
                            <option value="1">Yes (A/C)</option>
                            <option value="0">No (Non-A/C)</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Base Deposit (₹)</label>
                        <input type="number" name="base_deposit" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tax (%)</label>
                        <input type="number" name="tax_percentage" class="form-control" value="18.00">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Venue Status</label>
                        <select name="status" class="form-select">
                            <option value="Active">Active</option>
                            <option value="Maintenance">Maintenance</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Amenities</label>
                        <textarea name="amenities" class="form-control" rows="2" placeholder="Projector, Sound System, WiFi..."></textarea>
                    </div>

                    <div class="col-12 text-end mt-4">
                        <button type="reset" class="btn btn-outline-secondary me-2">Clear</button>
                        <button type="submit" class="btn btn-primary px-5">Save Venue Master</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>