<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

// Pagination Settings
$limit = 10; 
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;

// Filters
$where = " WHERE 1=1 ";
if(!empty($_GET['from_date'])) { $where .= " AND r.receipt_date >= '{$_GET['from_date']}' "; }
if(!empty($_GET['to_date'])) { $where .= " AND r.receipt_date <= '{$_GET['to_date']}' "; }
if(!empty($_GET['search'])) { 
    $s = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND (b.tracking_no LIKE '%$s%' OR c.contact_person LIKE '%$s%') "; 
}

// Total Count for Pagination
$total_res = $conn->query("SELECT COUNT(r.id) as id FROM vms_receipts r JOIN vms_booking_master b ON r.booking_id = b.id JOIN vms_customers c ON b.customer_id = c.id $where");
$total_rows = $total_res->fetch_assoc()['id'];
$total_pages = ceil($total_rows / $limit);

// Data Query
$sql = "SELECT r.*, b.tracking_no, b.net_payable, c.contact_person,
        (SELECT SUM(total_amount) FROM vms_receipts WHERE booking_id = b.id) as total_paid
        FROM vms_receipts r 
        JOIN vms_booking_master b ON r.booking_id = b.id 
        JOIN vms_customers c ON b.customer_id = c.id 
        $where ORDER BY r.receipt_date DESC LIMIT $start, $limit";

$res = $conn->query($sql);
?>

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 mb-4 d-print-none">
        <form class="card-body row g-3">
            <div class="col-md-2"><label>From</label><input type="date" name="from_date" class="form-control" value="<?= $_GET['from_date']??'' ?>"></div>
            <div class="col-md-2"><label>To</label><input type="date" name="to_date" class="form-control" value="<?= $_GET['to_date']??'' ?>"></div>
            <div class="col-md-4"><label>Search (Ref / Name)</label><input type="text" name="search" class="form-control" placeholder="Booking ID or Name" value="<?= $_GET['search']??'' ?>"></div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <button type="button" onclick="window.print()" class="btn btn-outline-dark w-100"><i class="bi bi-printer me-2"></i>Print List</button>
            </div>
        </form>
    </div>

    <div class="card shadow border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Receipt No</th>
                        <th>Booking Ref</th>
                        <th>Customer</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end d-print-none">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['receipt_no'] ?><br><small class="text-muted"><?= date('d-M-Y', strtotime($row['receipt_date'])) ?></small></td>
                        <td><?= $row['tracking_no'] ?></td>
                        <td><?= $row['contact_person'] ?></td>
                        <td class="text-end fw-bold">₹<?= number_format($row['total_amount'], 2) ?></td>
                        <td class="text-end d-print-none">
                            <a href="receipt_print_a5.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-outline-dark"><i class="bi bi-printer"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card-footer d-print-none">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <?php for($i=1; $i<=$total_pages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&from_date=<?= $_GET['from_date']??'' ?>&to_date=<?= $_GET['to_date']??'' ?>&search=<?= $_GET['search']??'' ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>
