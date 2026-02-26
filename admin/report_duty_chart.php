<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

// 1. Pagination Setup
$limit = 20; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;

// 2. Filter: Only Today's Events
$today = date('Y-m-d');

// 3. Count for Pagination
$count_res = $conn->query("SELECT COUNT(*) as total FROM vms_booking_slots WHERE booking_date = '$today'");
$total_records = $count_res->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// 4. Main Query
$sql = "SELECT s.*, v.venue_name, b.function_name, b.tracking_no, c.contact_person, c.mobile
        FROM vms_booking_slots s
        JOIN vms_venues v ON s.venue_id = v.id
        JOIN vms_booking_master b ON s.booking_id = b.id
        JOIN vms_customers c ON b.customer_id = c.id
        WHERE s.booking_date = '$today'
        ORDER BY s.start_time ASC LIMIT $start, $limit";
$res = $conn->query($sql);
?>

<div class="container-fluid py-4 d-print-none">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Today's Event Duty Chart</h4>
        <div>
            <button type="button" onclick="exportToExcel()" class="btn btn-success btn-sm">Export Excel</button>
            <button type="button" onclick="window.print()" class="btn btn-dark btn-sm">Print A4</button>
        </div>
    </div>
</div>

<div class="p-4 bg-white shadow-sm printable-area" id="reportArea">
    <div class="row mb-3 align-items-center">
        <div class="col-2 text-center">
            <img src="../assets/images/org_logo.png" style="width: 100px;" onerror="this.style.display='none'">
        </div>
        <div class="col-10 text-center">
            <h3 class="mb-0 fw-bold"><?= $org_full_name ?></h3>
            <h5 class="mt-2 text-decoration-underline fw-bold text-uppercase">Daily Event Operations & Duty Chart</h5>
            <p class="mb-0 fw-bold">Date: <?= date('d-M-Y') ?></p>
        </div>
    </div>

    <table class="table table-bordered border-dark table-sm" id="reportTable" style="font-size: 13px;">
        <thead class="bg-light text-center">
            <tr class="border-dark">
                <th class="border-dark">Time Slot</th>
                <th class="border-dark">Venue Name</th>
                <th class="border-dark">Function / Purpose</th>
                <th class="border-dark">Customer Name</th>
                <th class="border-dark">Contact No</th>
                <th class="border-dark">Booking Ref</th>
            </tr>
        </thead>
        <tbody>
            <?php if($res->num_rows > 0): ?>
                <?php while($row = $res->fetch_assoc()): ?>
                <tr class="border-dark">
                    <td class="border-dark text-center fw-bold"><?= $row['start_time'] ?> - <?= $row['finish_time'] ?></td>
                    <td class="border-dark"><?= $row['venue_name'] ?></td>
                    <td class="border-dark"><?= $row['function_name'] ?></td>
                    <td class="border-dark"><?= $row['contact_person'] ?></td>
                    <td class="border-dark text-center"><?= $row['mobile'] ?></td>
                    <td class="border-dark text-center small text-muted"><?= $row['tracking_no'] ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center py-4">No events scheduled for today.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="d-flex justify-content-between align-items-center mt-3 d-print-none">
        <div class="small">Page <?= $page ?> of <?= $total_pages ?></div>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>"><a class="page-link" href="?page=1">First</a></li>
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $page-1 ?>">Prev</a></li>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $page+1 ?>">Next</a></li>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $total_pages ?>">Last</a></li>
            </ul>
        </nav>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
function exportToExcel() {
    var wb = XLSX.utils.table_to_book(document.getElementById('reportTable'), { sheet: "DutyChart" });
    XLSX.writeFile(wb, "Duty_Chart_<?= date('Ymd') ?>.xlsx");
}
</script>
<style>
@media print { 
    @page { size: A4 landscape; margin: 0.5cm; }
    .table td, .table th { border: 1px solid #000 !important; }
}
#reportTable th, #reportTable td { border: 1px solid #000 !important; }
</style>
<?php include("../includes/footer.php"); ?>