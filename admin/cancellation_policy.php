<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

$policies = $conn->query("SELECT * FROM vms_cancellation_policy ORDER BY days_before_min DESC");
?>

<div class="container-fluid py-4">
    <div class="card shadow border-0">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Cancellation Policy Master</h5>
            <button type="button" class="btn btn-sm btn-light fw-bold" data-bs-toggle="modal" data-bs-target="#addPolicyModal">
                <i class="bi bi-plus-circle me-1"></i>Add New Rule
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Days Range (Before Event)</th>
                            <th>Rent Deduction (%)</th>
                            <th>Security (RSD) Refund</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($policies->num_rows > 0): ?>
                            <?php while($p = $policies->fetch_assoc()): ?>
                            <tr>
                                <td>Between <span class="badge bg-secondary"><?= $p['days_before_min'] ?></span> and <span class="badge bg-secondary"><?= $p['days_before_max'] ?></span> Days</td>
                                <td class="fw-bold text-danger"><?= $p['deduction_percent'] ?>% Deduction</td>
                                <td class="text-success fw-bold">100% Refund</td>
                                <td class="text-end">
                                    <a href="../api/delete_policy.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this policy rule?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">No policy rules defined yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addPolicyModal" tabindex="-1" aria-labelledby="addPolicyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="../api/save_policy.php" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="addPolicyModalLabel">Add Cancellation Rule</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Days Range Before Event</label>
                    <div class="input-group">
                        <span class="input-group-text">Min Days</span>
                        <input type="number" name="days_min" class="form-control" placeholder="e.g. 60" required>
                        <span class="input-group-text">Max Days</span>
                        <input type="number" name="days_max" class="form-control" placeholder="e.g. 89" required>
                    </div>
                    <small class="text-muted">Example: 60 to 89 days before the event date.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Rent Deduction Percentage (%)</label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="percent" class="form-control" placeholder="e.g. 25" required>
                        <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted text-danger">Note: RSD is always fully refunded as per VMS policy.</small>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger px-4">Save Policy Rule</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include("../includes/footer.php"); ?>