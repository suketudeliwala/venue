<?php
include("../includes/config.php");
include("../includes/header_admin.php");

$res = $conn->query("SELECT e.*, v.venue_name FROM vms_enquiries e LEFT JOIN vms_venues v ON e.venue_id = v.id ORDER BY e.created_at DESC");
?>

<div class="container-fluid py-4 min-vh-100">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-navy text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Incoming Enquiries</h5>
            <span class="badge bg-warning"><?= $res->num_rows ?> New Records</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date Recd</th>
                        <th>Function Name</th>
                        <th>Venue</th>
                        <th>Applicant</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('d-M H:i', strtotime($row['created_at'])) ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($row['function_name']) ?></td>
                        <td><?= htmlspecialchars($row['venue_name']) ?></td>
                        <td><?= htmlspecialchars($row['applicant_name']) ?><br><small><?= $row['applicant_mobile'] ?></small></td>
                        <td><span class="badge bg-secondary"><?= $row['status'] ?></span></td>
                        <td>
                            <a href="enquiry_view.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info text-white">View Details</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>