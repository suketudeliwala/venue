<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

$limit = 20; 
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';

$where = " WHERE 1=1 ";
if(!empty($search)) { $where .= " AND equipment_name LIKE '%$search%' "; }

$count_res = $conn->query("SELECT COUNT(*) as total FROM vms_equipments $where");
$total_records = $count_res->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

$sql = "SELECT * FROM vms_equipments $where ORDER BY equipment_name ASC LIMIT $start, $limit";
$res = $conn->query($sql);
?>

<div class="container-fluid py-4 d-print-none">
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body d-flex justify-content-between align-items-end">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" value="<?= $search ?>" placeholder="Search Equipment...">
                <button class="btn btn-primary">Search</button>
            </form>
            <div>
                <button type="button" onclick="exportToExcel()" class="btn btn-success">Excel</button>
                <button type="button" onclick="window.print()" class="btn btn-dark">Print A4</button>
            </div>
        </div>
    </div>
</div>

<div class="p-4 bg-white shadow-sm printable-area" id="reportArea">
    <div class="row mb-3 align-items-center">
        <div class="col-2 text-center"><img src="../assets/images/org_logo.png" style="width: 100px;" onerror="this.style.display='none'"></div>
        <div class="col-10 text-center">
            <h3 class="mb-0 fw-bold"><?= $org_full_name ?></h3>
            <h5 class="mt-2 text-decoration-underline fw-bold">EQUIPMENT MASTER INVENTORY REPORT</h5>
        </div>
    </div>

    <table class="table table-bordered border-dark table-sm" id="reportTable">
        <thead class="bg-light text-center border-dark">
            <tr>
                <th class="border-dark">Equipment Name</th>
                <!-- <th class="border-dark">Description</th>                    <a href=".php" class="nav-link text-light small">Cancellation Policy</a> -->
                <th class="border-dark">Daily Rate (₹)</th>
                <th class="border-dark">Quantity Available</th>
                <th class="border-dark">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $res->fetch_assoc()): ?>
            <tr class="border-dark">
                <td class="border-dark fw-bold"><?= $row['equipment_name'] ?></td>
                <!-- <td class="border-dark"><?= $row['description'] ?></td> -->
                <td class="border-dark text-end"><?= number_format($row['daily_rent'], 2) ?></td>
                <td class="border-dark text-center"><?= $row['total_quantity'] ?></td>
                <td class="border-dark text-center"><?= $row['status'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
function exportToExcel() {
    var wb = XLSX.utils.table_to_book(document.getElementById('reportTable'), { sheet: "EquipmentMaster" });
    XLSX.writeFile(wb, "Equipment_Report_<?= date('Ymd') ?>.xlsx");
}
</script>
<style>
@media print { @page { size: A4 Portrait; margin: 0.5cm; } .table td, .table th { border: 1px solid #000 !important; } }
#reportTable th, #reportTable td { border: 1px solid #000 !important; }
</style>