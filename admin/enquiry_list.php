<?php
include("../includes/config.php");
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
include("../includes/header_admin.php");

// 1. Pagination Configuration
$limit = 10; // Records per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;

// 2. Search Logic
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where = "WHERE 1=1";
if ($search) {
    $where .= " AND (tracking_no LIKE '%$search%' OR function_name LIKE '%$search%' OR applicant_name LIKE '%$search%')";
}

// 3. Fetch Data with Pagination
$sql = "SELECT e.*, v.venue_name FROM vms_enquiries e 
        LEFT JOIN vms_venues v ON e.venue_id = v.id 
        $where ORDER BY e.created_at DESC LIMIT $start, $limit";
$res = $conn->query($sql);

// 4. Get total records for pagination links
$total_res = $conn->query("SELECT COUNT(*) as id FROM vms_enquiries $where");
$total_count = $total_res->fetch_assoc()['id'];
$total_pages = ceil($total_count / $limit);
?>

<div class="container-fluid py-4 min-vh-100">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-light">
            <form method="GET" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search by Ref No, Function, or Applicant..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-4 d-grid">
                    <button type="submit" class="btn btn-primary shadow-sm">Filter Results</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark" style="background-color: #001d4a;">
                    <tr>
                        <th>Ref Number</th>
                        <th>Function Details</th>
                        <th>Venue</th>
                        <th>Applicant</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($res->num_rows > 0): ?>
                        <?php while($row = $res->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <span class="fw-bold text-primary"><?= $row['tracking_no'] ?></span><br>
                                <small class="text-muted"><?= date('d-M-Y', strtotime($row['created_at'])) ?></small>
                            </td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($row['function_name']) ?></div>
                                <small class="badge bg-light text-dark border"><?= $row['purpose'] ?></small>
                            </td>
                            <td><?= htmlspecialchars($row['venue_name'] ?? 'Not Assigned') ?></td>
                            <td>
                                <?= htmlspecialchars($row['applicant_name']) ?><br>
                                <small class="text-muted"><i class="bi bi-phone"></i> <?= $row['applicant_mobile'] ?></small>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-<?= ($row['status'] == 'New') ? 'info' : (($row['status'] == 'Rejected') ? 'danger' : 'success') ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="enquiry_view.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4">No enquiries found matching your search.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if($total_pages > 1): ?>
        <div class="card-footer bg-white border-top-0 py-3">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">Previous</a>
                    </li>
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include("../includes/footer.php"); ?>