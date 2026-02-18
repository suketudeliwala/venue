<?php
include("../includes/config.php");
include("../includes/header_admin.php");



$venues = $conn->query("SELECT id, venue_name FROM vms_venues ORDER BY venue_name");
?>

<div class="container-fluid py-4 min-vh-100">
    <div class="card shadow border-0">
        <div class="card-header bg-navy text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Venue Rate Configuration</h5>
            <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addRateModal">Add New Rate</button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Venue</th>
                        <th>Duration</th>
                        <th>Member Rate</th>
                        <th>Non-Member Rate</th>
                        <th>RSD</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rates = $conn->query("SELECT r.*, v.venue_name FROM vms_venue_rates r JOIN vms_venues v ON r.venue_id = v.id");
                    while($row = $rates->fetch_assoc()): ?>
                    <tr>
                        <td class="fw-bold"><?= $row['venue_name'] ?></td>
                        <td><?= $row['duration_label'] ?></td>
                        <td>₹<?= number_format($row['member_rate'], 2) ?></td>
                        <td>₹<?= number_format($row['non_member_rate'], 2) ?></td>
                        <td>₹<?= number_format($row['rsd_amount'], 2) ?></td>
                        <td>
                            <a href="delete_rate.php?id=<?= $row['id'] ?>" class="text-danger" onclick="return confirm('Delete rate?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addRateModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="../api/save_venue_rate.php" method="POST" class="modal-content">
            <div class="modal-header"><h5>Add Venue Rate</h5></div>
            <div class="modal-body row g-3">
                <div class="col-12">
                    <label class="form-label">Venue</label>
                    <select name="venue_id" class="form-select" required>
                        <?php while($v = $venues->fetch_assoc()) echo "<option value='{$v['id']}'>{$v['venue_name']}</option>"; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Duration Label</label>
                    <input type="text" name="duration_label" class="form-control" placeholder="e.g. 4 Hours" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">RSD Amount</label>
                    <input type="number" name="rsd_amount" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Member Rate</label>
                    <input type="number" name="member_rate" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Non-Member Rate</label>
                    <input type="number" name="non_member_rate" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save Rate</button>
            </div>
        </form>
    </div>
</div>

<?php include("../includes/footer.php"); ?>