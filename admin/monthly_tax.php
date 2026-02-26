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

$where = " WHERE i.invoice_date BETWEEN '$from_date' AND '$to_date' AND i.status = 'Final' ";

// 3. Count for Pagination
$count_res = $conn->query("SELECT COUNT(*) as total FROM vms_invoices i $where");
$total_records = $count_res->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// 4. Fetch Grand Totals for the Footer
$total_sql = "SELECT SUM(taxable_amount) as t_tax, SUM(cgst_amount) as t_cgst, SUM(sgst_amount) as t_sgst, SUM(grand_total) as t_grand 
              FROM vms_invoices i $where";
$totals = $conn->query($total_sql)->fetch_assoc();

// 5. Main Query
$sql = "SELECT i.*, c.contact_person, b.tracking_no 
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
                <div class="col-md-3"><label class="small fw-bold">Tax Period From</label><input type="date" name="from_date" class="form-control" value="<?= $from_date ?>"></div>
                <div class="col-md-3"><label class="small fw-bold">To</label><input type="date" name="to_date" class="form-control" value="<?= $to_date ?>"></div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <button type="button" onclick="exportToExcel()" class="btn btn-success">Export to Excel</button>
                    <button type="button" onclick="window.print()" class="btn btn-dark">Print A4</button>
                </div>
            </form>
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
            <h5 class="mt-2 text-decoration-underline fw-bold">MONTHLY GST REMITTANCE REPORT (OUTPUT TAX)</h5>
            <p class="small mb-0">Period: <?= date('d-M-Y', strtotime($from_date)) ?> to <?= date('d-M-Y', strtotime($to_date)) ?></p>
        </div>
    </div>

    <table class="table table-bordered border-dark table-sm" id="reportTable" style="font-size: 12px;">
        <thead class="bg-light text-center">
            <tr class="border-dark">
                <th class="border-dark">Date</th>
                <th class="border-dark">Invoice No</th>
                <th class="border-dark">Customer Name</th>
                <th class="border-dark">Taxable Amt</th>
                <th class="border-dark">CGST (9%)</th>
                <th class="border-dark">SGST (9%)</th>
                <th class="border-dark">Total GST</th>
                <th class="border-dark">Grand Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $pg_taxable = $pg_cgst = $pg_sgst = 0;
            while($row = $res->fetch_assoc()): 
                $pg_taxable += $row['taxable_amount'];
                $pg_cgst += $row['cgst_amount'];
                $pg_sgst += $row['sgst_amount'];
            ?>
            <tr class="border-dark">
                <td class="border-dark text-center"><?= date('d-m-y', strtotime($row['invoice_date'])) ?></td>
                <td class="border-dark"><?= $row['invoice_no'] ?></td>
                <td class="border-dark"><?= $row['contact_person'] ?></td>
                <td class="border-dark text-end"><?= number_format($row['taxable_amount'], 2) ?></td>
                <td class="border-dark text-end"><?= number_format($row['cgst_amount'], 2) ?></td>
                <td class="border-dark text-end"><?= number_format($row['sgst_amount'], 2) ?></td>
                <td class="border-dark text-end"><?= number_format($row['cgst_amount'] + $row['sgst_amount'], 2) ?></td>
                <td class="border-dark text-end fw-bold"><?= number_format($row['grand_total'], 2) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot class="fw-bold border-dark">
            <tr class="bg-light border-dark">
                <td colspan="3" class="text-end border-dark">PAGE TOTAL:</td>
                <td class="text-end border-dark"><?= number_format($pg_taxable, 2) ?></td>
                <td class="text-end border-dark"><?= number_format($pg_cgst, 2) ?></td>
                <td class="text-end border-dark"><?= number_format($pg_sgst, 2) ?></td>
                <td class="text-end border-dark"><?= number_format($pg_cgst + $pg_sgst, 2) ?></td>
                <td class="border-dark"></td>
            </tr>
            <tr class="table-secondary border-dark">
                <td colspan="3" class="text-end border-dark">GRAND TOTAL LIABILITY:</td>
                <td class="text-end border-dark">₹<?= number_format($totals['t_tax'], 2) ?></td>
                <td class="text-end border-dark">₹<?= number_format($totals['t_cgst'], 2) ?></td>
                <td class="text-end border-dark">₹<?= number_format($totals['t_sgst'], 2) ?></td>
                <td class="text-end border-dark">₹<?= number_format($totals['t_cgst'] + $totals['t_sgst'], 2) ?></td>
                <td class="text-end border-dark">₹<?= number_format($totals['t_grand'], 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="d-flex justify-content-between align-items-center mt-3 d-print-none">
        <div class="small">Page <?= $page ?> of <?= $total_pages ?> (Total: <?= $total_records ?>)</div>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>"><a class="page-link" href="?page=1&from_date=<?= $from_date ?>&to_date=<?= $to_date ?>">First</a></li>
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $page-1 ?>&from_date=<?= $from_date ?>&to_date=<?= $to_date ?>">Prev</a></li>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $page+1 ?>&from_date=<?= $from_date ?>&to_date=<?= $to_date ?>">Next</a></li>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $total_pages ?>&from_date=<?= $from_date ?>&to_date=<?= $to_date ?>">Last</a></li>
            </ul>
        </nav>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
function exportToExcel() {
    var elt = document.getElementById('reportTable');
    var wb = XLSX.utils.table_to_book(elt, { sheet: "TaxReport" });
    return XLSX.writeFile(wb, "Tax_Remittance_<?= date('Ymd') ?>.xlsx");
}
</script>
<style>
@media print { 
    @page { size: A4 landscape; margin: 0.5cm; }
    .table td, .table th { border: 1px solid #000 !important; }
}
#reportTable th, #reportTable td { border: 1px solid #000 !important; }
</style>