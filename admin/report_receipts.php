<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

$where = " WHERE 1=1 ";
if(!empty($_GET['from_date'])) { $where .= " AND r.receipt_date >= '{$_GET['from_date']}' "; }
if(!empty($_GET['to_date'])) { $where .= " AND r.receipt_date <= '{$_GET['to_date']}' "; }
if(!empty($_GET['search'])) { 
    $s = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND (b.tracking_no LIKE '%$s%' OR c.contact_person LIKE '%$s%') "; 
}

$sql = "SELECT r.*, b.tracking_no, b.net_payable, c.contact_person,
        (SELECT SUM(total_amount) FROM vms_receipts WHERE booking_id = b.id) as total_paid
        FROM vms_receipts r 
        JOIN vms_booking_master b ON r.booking_id = b.id 
        JOIN vms_customers c ON b.customer_id = c.id 
        $where ORDER BY r.receipt_date DESC";

$res = $conn->query($sql);
?>

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 mb-4 d-print-none">
        <form class="card-body row g-3">
            <div class="col-md-3"><label>From</label><input type="date" name="from_date" class="form-control" value="<?= $_GET['from_date']??'' ?>"></div>
            <div class="col-md-3"><label>To</label><input type="date" name="to_date" class="form-control" value="<?= $_GET['to_date']??'' ?>"></div>
            <div class="col-md-4"><label>Search (Ref / Name)</label><input type="text" name="search" class="form-control" placeholder="Booking ID or Name" value="<?= $_GET['search']??'' ?>"></div>
            <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100">Filter</button></div>
        </form>
    </div>

    <div class="card shadow border-0">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Receipt</th>
                        <th>Booking Ref</th>
                        <th>Customer</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Balance Left</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res->fetch_assoc()): 
                        $balance = $row['net_payable'] - $row['total_paid'];
                        if(isset($_GET['has_balance']) && $balance <= 0) continue; // Filter Balance > 0
                    ?>
                    <tr>
                        <td><?= $row['receipt_no'] ?><br><small><?= $row['receipt_date'] ?></small></td>
                        <td><?= $row['tracking_no'] ?></td>
                        <td><?= $row['contact_person'] ?></td>
                        <td class="text-end fw-bold text-success">₹<?= number_format($row['total_amount'], 2) ?></td>
                        <td class="text-end text-danger">₹<?= number_format($balance, 2) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>