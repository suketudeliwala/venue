<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

$limit = 50; 
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';

$where = " WHERE customer_type = 'Customer' ";
if(!empty($search)) { $where .= " AND (contact_person LIKE '%$search%' OR company_name LIKE '%$search%' OR mobile LIKE '%$search%') "; }

$count_res = $conn->query("SELECT COUNT(*) as total FROM vms_customers $where");
$total_records = $count_res->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

$sql = "SELECT * FROM vms_customers $where ORDER BY contact_person ASC LIMIT $start, $limit";
$res = $conn->query($sql);
?>

<div class="container-fluid py-4 d-print-none">
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body row g-2">
            <div class="col-md-6">
                <form method="GET" class="input-group">
                    <input type="text" name="search" class="form-control" value="<?= $search ?>" placeholder="Search by Name, Company or Mobile...">
                    <button class="btn btn-primary">Search</button>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" onclick="exportToExcel()" class="btn btn-success">Excel</button>
                <button type="button" onclick="window.print()" class="btn btn-dark">Print A4</button>
            </div>
        </div>
    </div>
</div>

<div class="p-4 bg-white shadow-sm printable-area" id="reportArea">
    <div class="row mb-3 align-items-center">
        <div class="col-2 text-center"><img src="../assets/images/org_logo.png" style="width: 100px;"></div>
        <div class="col-10 text-center">
            <h3 class="mb-0 fw-bold"><?= $org_full_name ?></h3>
            <h5 class="mt-2 text-decoration-underline fw-bold">CUSTOMER MASTER DIRECTORY</h5>
        </div>
    </div>

    <table class="table table-bordered border-dark table-sm" id="reportTable" style="font-size: 12px;">
        <thead class="bg-light text-center">
            <tr>
                <th class="border-dark">Name</th>
                <th class="border-dark">Company/Org</th>
                <th class="border-dark">Mobile</th>
                <th class="border-dark">Email</th>
                <th class="border-dark">Full Address</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $res->fetch_assoc()): ?>
            <tr class="border-dark">
                <td class="border-dark fw-bold"><?= $row['contact_person'] ?></td>
                <td class="border-dark"><?= $row['company_name'] ?></td>
                <td class="border-dark"><?= $row['mobile'] ?></td>
                <td class="border-dark"><?= $row['email'] ?></td>
                <td class="border-dark small"><?= $row['address'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>
