<?php
include("../includes/config.php");

// 1. Strict Admin Session Check
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: login.php");
    exit;
}

include("../includes/header_admin.php");

// 2. Pagination & Filter Logic
$limit_options = [20, 50, 100, 500];
$limit = isset($_GET['limit']) && in_array(intval($_GET['limit']), $limit_options) ? intval($_GET['limit']) : 20;
$page  = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Filters
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-2 days'));
$end_date   = $_GET['end_date'] ?? '';
$venue_filter = $_GET['venue_id'] ?? '';

// Build Query
$query = "SELECT b.*, v.venue_name FROM vms_bookings b 
          JOIN vms_venues v ON b.venue_id = v.id 
          WHERE b.booking_date >= ? ";
$params = [$start_date];
$types = "s";

if ($end_date) {
    $query .= " AND b.booking_date <= ?";
    $params[] = $end_date;
    $types .= "s";
}
if ($venue_filter) {
    $query .= " AND b.venue_id = ?";
    $params[] = $venue_filter;
    $types .= "i";
}

$query .= " ORDER BY b.booking_date ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-light">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="small fw-bold">From Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="<?= $start_date ?>">
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold">To Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="<?= $end_date ?>">
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold">Venue</label>
                    <select name="venue_id" class="form-select form-select-sm">
                        <option value="">All Venues</option>
                        <?php 
                        $v_res = $conn->query("SELECT id, venue_name FROM vms_venues");
                        while($v = $v_res->fetch_assoc()) echo "<option value='{$v['id']}'>{$v['venue_name']}</option>";
                        ?>
                    </select>
                </div>
                <div class="col-md-6 text-end">
                    <button type="submit" class="btn btn-primary btn-sm px-3"><i class="bi bi-search"></i> Search</button>
                    <a href="booking_add.php" class="btn btn-success btn-sm px-3"><i class="bi bi-plus-lg"></i> Add Booking</a>
                    <a href="../reports/booking_reports.php" class="btn btn-dark btn-sm px-3"><i class="bi bi-file-earmark-pdf"></i> Reports</a>
                    
                    <select name="limit" class="form-select form-select-sm w-auto d-inline-block ms-2" onchange="this.form.submit()">
                        <?php foreach ($limit_options as $opt): ?>
                            <option value="<?= $opt ?>" <?= $limit == $opt ? 'selected' : '' ?>><?= $opt ?> per page</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-navy fw-bold">Current & Future Bookings</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Venue Name</th>
                        <th>Customer / Purpose</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($bookings)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">No bookings found for the selected range.</td></tr>
                    <?php endif; ?>
                    
                    <?php foreach ($bookings as $row): ?>
                    <tr>
                        <td class="fw-bold"><?= date('d-M-Y', strtotime($row['booking_date'])) ?></td>
                        <td><?= htmlspecialchars($row['venue_name']) ?></td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($row['customer_name'] ?? 'Walk-in') ?></div>
                            <small class="text-muted"><?= htmlspecialchars($row['purpose'] ?? 'General Event') ?></small>
                        </td>
                        <td>
                            <span class="badge rounded-pill bg-<?= $row['status'] == 'Confirmed' ? 'success' : 'warning' ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group shadow-sm">
                                <a href="booking_view.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i></a>
                                <a href="booking_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i></a>
                                <button onclick="confirmDelete(<?= $row['id'] ?>)" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card-footer bg-white py-3">
            <nav>
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item"><a class="page-link" href="?page=1&limit=<?= $limit ?>">First</a></li>
                    <li class="page-item"><a class="page-link" href="?page=<?= max(1, $page-1) ?>">Prev</a></li>
                    <li class="page-item active"><a class="page-link" href="#">Page <?= $page ?></a></li>
                    <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">Next</a></li>
                    <li class="page-item"><a class="page-link" href="#">Last</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if(confirm("Are you sure you want to cancel and delete this booking?")) {
        window.location.href = "booking_delete.php?id=" + id;
    }
}
</script>

<?php include("../includes/footer.php"); ?>