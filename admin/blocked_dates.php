<?php
include("../includes/config.php");
include("../includes/header_admin.php");
?>

<div class="container-fluid py-4 min-vh-100">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow border-0">
                <div class="card-header bg-danger text-white">Block a Date</div>
                <div class="card-body">
                    <form action="../api/save_blocked_date.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="block_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <input type="text" name="reason" class="form-control" placeholder="e.g. Trust Annual Meeting" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="is_hard_block" class="form-check-input" value="1">
                            <label class="form-check-label">Strict Block (No Overrides)</label>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Reserve Date</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-navy text-white">Reserved/Blocked Dates</div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Reason</th>
                                <th>Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $blocks = $conn->query("SELECT * FROM vms_blocked_dates ORDER BY block_date DESC");
                            while($b = $blocks->fetch_assoc()): ?>
                            <tr>
                                <td><?= date('d-M-Y', strtotime($b['block_date'])) ?></td>
                                <td><?= $b['reason'] ?></td>
                                <td><?= $b['is_hard_block'] ? '<span class="text-danger">Strict</span>' : 'Warning' ?></td>
                                <td><a href="delete_block.php?id=<?= $b['id'] ?>" class="text-muted"><i class="bi bi-x-circle"></i></a></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>