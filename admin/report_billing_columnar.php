<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

// 1. Pagination Setup
$limit = 20; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page - 1) * $limit;

// 2. Filters
$from_date = $_GET['from_date'] ?? date('Y-m-01');
$to_date   = $_GET['to_date'] ?? date('Y-m-d');
$search    = $_GET['search'] ?? '';

$where = " WHERE i.invoice_date BETWEEN '$from_date' AND '$to_date' ";
if(!empty($search)) {
    $where .= " AND (c.contact_person LIKE '%$search%' OR b.tracking_no LIKE '%$search%') ";
}

// 3. Count Total Records for Pagination
$count_res = $conn->query("SELECT COUNT(*) as total FROM vms_invoices i JOIN vms_customers c ON i.booking_id = c.id $where");
$total_records = $count_res->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// 4. Fetch Grand Totals (Entire Date Range)
$total_sql = "SELECT SUM(base_rent) as r, SUM(extra_charges) as e, SUM(total_damages) as d, 
              SUM(discount_amount) as disc, SUM(cgst_amount) as c, SUM(sgst_amount) as s, 
              SUM(grand_total) as g, SUM(total_advance_paid) as adv, SUM(final_balance) as bal
              FROM vms_invoices i JOIN vms_customers c ON i.booking_id = c.id $where";
$totals = $conn->query($total_sql)->fetch_assoc();

// 5. Main Query (Fetch Records for Current Page)
$sql = "SELECT i.*, b.tracking_no, c.contact_person 
        FROM vms_invoices i
        JOIN vms_booking_master b ON i.booking_id = b.id
        JOIN vms_customers c ON b.customer_id = c.id
        $where ORDER BY i.invoice_date ASC LIMIT $start, $limit";
$res = $conn->query($sql);
?>

<div class="container-fluid py-4 d-print-none">
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2"><label class="small fw-bold">From</label><input type="date" name="from_date" class="form-control" value="<?= $from_date ?>"></div>
                <div class="col-md-2"><label class="small fw-bold">To</label><input type="date" name="to_date" class="form-control" value="<?= $to_date ?>"></div>
                <div class="col-md-3"><label class="small fw-bold">Search</label><input type="text" name="search" class="form-control" value="<?= $search ?>" placeholder="Name/Ref..."></div>
                <div class="col-md-5">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <button type="button" onclick="exportToExcel()" class="btn btn-success">Excel Export</button>
                    <button type="button" onclick="window.print()" class="btn btn-dark">Print A4</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="p-4 bg-white shadow-sm printable-area" id="reportArea">
    <div class="row mb-3 align-items-center">
        <div class="col-2 text-center">
            <img src="../assets/images/org_logo.png" style="width: 100px;" alt="Logo" onerror="this.style.display='none'">
        </div>
        <div class="col-10 text-center">
            <h3 class="mb-0 fw-bold"><?= $org_full_name ?></h3>
            <p class="mb-0 small"><?= $org_address ?></p>
            <h5 class="mt-2 text-decoration-underline fw-bold">COLUMNAR SALES REGISTER (BILLING REPORT)</h5>
            <p class="small mb-0">Period: <?= date('d-M-Y', strtotime($from_date)) ?> to <?= date('d-M-Y', strtotime($to_date)) ?></p>
        </div>
    </div>

    <table class="table table-bordered border-dark table-sm" id="reportTable" style="font-size: 11px;">
        <thead class="bg-light text-center align-middle">
            <tr>
                <th class="border-dark">Date</th>
                <th class="border-dark">Inv No</th>
                <th class="border-dark">Party Name</th>
                <th class="border-dark">Base Rent</th>
                <th class="border-dark">Extra</th>
                <th class="border-dark">Damage</th>
                <th class="border-dark">Disc</th>
                <th class="border-dark">CGST</th>
                <th class="border-dark">SGST</th>
                <th class="border-dark">Grand Total</th>
                <th class="border-dark">Receipts</th>
                <th class="border-dark">Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $pg_rent = $pg_extra = $pg_dmg = $pg_disc = $pg_cgst = $pg_sgst = $pg_grand = $pg_rec = $pg_bal = 0;
            if($res->num_rows > 0):
                while($row = $res->fetch_assoc()): 
                    // Accumulate Page Totals
                    $pg_rent += $row['base_rent']; $pg_extra += $row['extra_charges'];
                    $pg_dmg  += $row['total_damages']; $pg_disc += $row['discount_amount'];
                    $pg_cgst += $row['cgst_amount']; $pg_sgst += $row['sgst_amount'];
                    $pg_grand += $row['grand_total']; $pg_rec += $row['total_advance_paid'];
                    $pg_bal += $row['final_balance'];
            ?>
            <tr>
                <td class="border-dark"><?= date('d-m-y', strtotime($row['invoice_date'])) ?></td>
                <td class="border-dark"><?= $row['invoice_no'] ?></td>
                <td class="border-dark"><?= htmlspecialchars($row['contact_person']) ?></td>
                <td class="border-dark text-end"><?= number_format($row['base_rent'], 2) ?></td>
                <td class="border-dark text-end"><?= number_format($row['extra_charges'], 2) ?></td>
                <td class="border-dark text-end"><?= number_format($row['total_damages'], 2) ?></td>
                <td class="border-dark text-end text-danger"><?= number_format($row['discount_amount'], 2) ?></td>
                <td class="border-dark text-end"><?= number_format($row['cgst_amount'], 2) ?></td>
                <td class="border-dark text-end"><?= number_format($row['sgst_amount'], 2) ?></td>
                <td class="border-dark text-end fw-bold"><?= number_format($row['grand_total'], 2) ?></td>
                <td class="border-dark text-end text-success"><?= number_format($row['total_advance_paid'], 2) ?></td>
                <td class="border-dark text-end fw-bold"><?= number_format($row['final_balance'], 2) ?></td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="12" class="text-center py-4">No records found for selected criteria.</td></tr>
            <?php endif; ?>
        </tbody>
        <tfoot class="bg-light fw-bold border-dark">
            <tr class="border-dark">
                <td colspan="3" class="text-end border-dark">PAGE TOTAL (RUNNING):</td>
                <td class="text-end border-dark"><?= number_format($pg_rent, 2) ?></td>
                <td class="text-end border-dark"><?= number_format($pg_extra, 2) ?></td>
                <td class="text-end border-dark"><?= number_format($pg_dmg, 2) ?></td>
                <td class="text-end border-dark text-danger"><?= number_format($pg_disc, 2) ?></td>
                <td class="text-end border-dark"><?= number_format($pg_cgst, 2) ?></td>
                <td class="text-end border-dark"><?= number_format($pg_sgst, 2) ?></td>
                <td class="text-end border-dark"><?= number_format($pg_grand, 2) ?></td>
                <td class="text-end border-dark"><?= number_format($pg_rec, 2) ?></td>
                <td class="text-end border-dark"><?= number_format($pg_bal, 2) ?></td>
            </tr>
            <tr class="table-secondary border-dark">
                <td colspan="3" class="text-end border-dark">GRAND TOTAL:</td>
                <td class="border-dark text-end">₹<?= number_format($totals['r'], 2) ?></td>
                <td class="border-dark text-end">₹<?= number_format($totals['e'], 2) ?></td>
                <td class="border-dark text-end">₹<?= number_format($totals['d'], 2) ?></td>
                <td class="border-dark text-end text-danger">₹<?= number_format($totals['disc'], 2) ?></td>
                <td class="border-dark text-end">₹<?= number_format($totals['c'], 2) ?></td>
                <td class="border-dark text-end">₹<?= number_format($totals['s'], 2) ?></td>
                <td class="border-dark text-end">₹<?= number_format($totals['g'], 2) ?></td>
                <td class="border-dark text-end text-success">₹<?= number_format($totals['adv'], 2) ?></td>
                <td class="border-dark text-end">₹<?= number_format($totals['bal'], 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="d-flex justify-content-between align-items-center mt-3 d-print-none">
        <div class="small">Showing Page <?= $page ?> of <?= $total_pages ?> (Total: <?= $total_records ?> records)</div>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=1&from_date=<?= $from_date ?>&to_date=<?= $to_date ?>&search=<?= $search ?>">First</a>
                </li>
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&from_date=<?= $from_date ?>&to_date=<?= $to_date ?>&search=<?= $search ?>">Previous</a>
                </li>
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&from_date=<?= $from_date ?>&to_date=<?= $to_date ?>&search=<?= $search ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&from_date=<?= $from_date ?>&to_date=<?= $to_date ?>&search=<?= $search ?>">Next</a>
                </li>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $total_pages ?>&from_date=<?= $from_date ?>&to_date=<?= $to_date ?>&search=<?= $search ?>">Last</a>
                </li>
            </ul>
        </nav>
    </div>
    
    <div class="text-end small mt-2">Generated on: <?= date('d-M-Y H:i') ?></div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
<script>
function exportToExcel() {
    // Generate filename with date
    var filename = "Billing_Register_<?= date('Ymd') ?>.xlsx";
    var wb = XLSX.utils.table_to_book(document.getElementById('reportTable'), {sheet: "Sales Register"});
    XLSX.writeFile(wb, filename);
}
</script>

<style>
@media print {
    @page { size: A4 landscape; margin: 0.5cm; }
    body { background-color: #fff !important; }
    .printable-area { width: 100% !important; box-shadow: none !important; }
    .table td, .table th { border: 1px solid #000 !important; }
    .border-dark { border: 1px solid #000 !important; }
}
#reportTable th, #reportTable td { border: 1px solid #333 !important; }
</style>

<?php include("../includes/footer.php"); ?>