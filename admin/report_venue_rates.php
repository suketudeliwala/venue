<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

$search = $_GET['search'] ?? '';
$where = !empty($search) ? " WHERE v.venue_name LIKE '%$search%' " : "";

$sql = "SELECT v.venue_name, v.sac_code, r.duration_label, r.member_rate, r.non_member_rate, r.rsd_amount, r.late_fee_per_hour 
        FROM vms_venues v
        JOIN vms_venue_rates r ON v.id = r.venue_id
        $where ORDER BY v.venue_name ASC, r.duration_label ASC";
$res = $conn->query($sql);
?>

<div class="container-fluid py-4 d-print-none">
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body d-flex justify-content-between align-items-end">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" value="<?= $search ?>" placeholder="Filter by Venue...">
                <button class="btn btn-primary">Filter</button>
            </form>
            <div>
                <button type="button" onclick="exportToExcel()" class="btn btn-success">Excel</button>
                <button type="button" onclick="window.print()" class="btn btn-dark">Print</button>
            </div>
        </div>
    </div>
</div>

<div class="p-4 bg-white shadow-sm printable-area" id="reportArea">
    <div class="row mb-3 align-items-center">
        <div class="col-2 text-center"><img src="../assets/images/org_logo.png" style="width: 100px;"></div>
        <div class="col-10 text-center">
            <h3 class="mb-0 fw-bold"><?= $org_full_name ?></h3>
            <h5 class="mt-2 text-decoration-underline fw-bold">VENUE CATEGORY & RATE STRUCTURE REPORT</h5>
        </div>
    </div>

    <table class="table table-bordered border-dark table-sm" id="reportTable">
        <thead class="bg-light text-center">
            <tr>
                <th class="border-dark">Venue Name</th>
                <th class="border-dark">SAC Code</th>
                <th class="border-dark">Duration/Slot</th>
                <th class="border-dark">Member's Rent(₹)</th>
                <th class="border-dark">Non-Member's Rent (₹)</th>
                <th class="border-dark">RSD Deposit (₹)</th>
                <th class="border-dark">Late Fee</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $res->fetch_assoc()): ?>
            <tr class="border-dark">
                <td class="border-dark fw-bold"><?= $row['venue_name'] ?></td>
                <td class="border-dark text-center"><?= $row['sac_code'] ?></td>
                <td class="border-dark"><?= $row['duration_label'] ?></td>
                <td class="border-dark text-end"><?= number_format($row['member_rate'], 2) ?></td>
                <td class="border-dark text-end"><?= number_format($row['non_member_rate'], 2) ?></td>
                <td class="border-dark text-end"><?= number_format($row['rsd_amount'], 2) ?></td>
                <td class="border-dark text-center"><?= number_format($row['late_fee_per_hour'], 2) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>