<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

$limit = 20; 
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;

// Filters
$search = $_GET['search'] ?? '';
$util_filter = $_GET['util_filter'] ?? 'All';
$status_filter = $_GET['status_filter'] ?? 'All';
$member_filter = $_GET['member_filter'] ?? 'All';

$where = " WHERE 1=1 ";
if(!empty($search)) { $where .= " AND (c.contact_person LIKE '%$search%' OR b.tracking_no LIKE '%$search%') "; }
if($util_filter == 'Pending') { $where .= " AND b.id NOT IN (SELECT booking_id FROM vms_utilization_reports) "; }
if($status_filter != 'All') { $where .= " AND b.status = '$status_filter' "; }
if($member_filter != 'All') { $is_mem = ($member_filter == 'Member') ? 1 : 0; $where .= " AND c.is_member = $is_mem "; }

$count_res = $conn->query("SELECT COUNT(*) as total FROM vms_booking_master b JOIN vms_customers c ON b.customer_id = c.id $where");
$total_records = $count_res->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

$sql = "SELECT b.*, c.contact_person, c.is_member 
        FROM vms_booking_master b 
        JOIN vms_customers c ON b.customer_id = c.id 
        $where ORDER BY b.id DESC LIMIT $start, $limit";
$res = $conn->query($sql);
?>

<div class="container-fluid py-4 d-print-none">
    <div class="card shadow-sm border-0 mb-3">
        <form class="card-body row g-2 align-items-end">
            <div class="col-md-3"><label class="small fw-bold">Search</label><input type="text" name="search" class="form-control" value="<?= $search ?>" placeholder="Name/Ref..."></div>
            <div class="col-md-2">
                <label class="small fw-bold">Utilization</label>
                <select name="util_filter" class="form-select">
                    <option value="All">All</option>
                    <option value="Pending" <?= $util_filter=='Pending'?'selected':'' ?>>Pending Utilization</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Status</label>
                <select name="status_filter" class="form-select">
                    <option value="All">All Status</option>
                    <option value="Confirmed" <?= $status_filter=='Confirmed'?'selected':'' ?>>Confirmed</option>
                    <option value="Billed" <?= $status_filter=='Billed'?'selected':'' ?>>Billed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Member Type</label>
                <select name="member_filter" class="form-select">
                    <option value="All">All</option>
                    <option value="Member" <?= $member_filter=='Member'?'selected':'' ?>>Members</option>
                    <option value="Non-Member" <?= $member_filter=='Non-Member'?'selected':'' ?>>Non-Members</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <button type="button" onclick="exportToExcel()" class="btn btn-success btn-sm">Excel</button>
                <button type="button" onclick="window.print()" class="btn btn-dark btn-sm">Print</button>
            </div>
        </form>
    </div>
</div>

<div class="p-4 bg-white shadow-sm printable-area" id="reportArea">
    <div class="row mb-3 align-items-center">
        <div class="col-2 text-center"><img src="../assets/images/org_logo.png" style="width: 100px;"></div>
        <div class="col-10 text-center">
            <h3 class="mb-0 fw-bold"><?= $org_full_name ?></h3>
            <h5 class="mt-2 text-decoration-underline fw-bold">MASTER BOOKING TRANSACTION REPORT</h5>
        </div>
    </div>
    <table class="table table-bordered border-dark table-sm" id="reportTable" style="font-size: 12px;">
        <thead class="bg-light text-center border-dark">
            <tr>
                <th class="border-dark">Booking Ref</th>
                <th class="border-dark">Customer Name</th>
                <th class="border-dark">Function</th>
                <th class="border-dark">Rent Amount</th>
                <th class="border-dark">Type</th>
                <th class="border-dark">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $res->fetch_assoc()): ?>
            <tr class="border-dark">
                <td class="border-dark"><?= $row['tracking_no'] ?></td>
                <td class="border-dark"><?= $row['contact_person'] ?></td>
                <td class="border-dark"><?= $row['function_name'] ?></td>
                <td class="border-dark text-end"><?= number_format($row['total_rent'], 2) ?></td>
                <td class="border-dark text-center"><?= $row['is_member'] ? 'Member' : 'General' ?></td>
                <td class="border-dark text-center fw-bold"><?= $row['status'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>