<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

// 1. Handle Filters
$from_date = $_GET['from_date'] ?? date('Y-m-01');
$to_date   = $_GET['to_date'] ?? date('Y-m-d');
$search    = $_GET['search'] ?? '';
$status    = $_GET['status'] ?? 'All';

$where = " WHERE i.invoice_date BETWEEN '$from_date' AND '$to_date' ";

if(!empty($search)) {
    $where .= " AND (c.contact_person LIKE '%$search%' OR b.tracking_no LIKE '%$search%' OR i.invoice_no LIKE '%$search%') ";
}

if($status == 'Paid') $where .= " AND i.final_balance <= 0 ";
if($status == 'Balance') $where .= " AND i.final_balance > 0 ";

$sql = "SELECT i.*, b.tracking_no, c.contact_person, c.mobile 
        FROM vms_invoices i
        JOIN vms_booking_master b ON i.booking_id = b.id
        JOIN vms_customers c ON b.customer_id = c.id
        $where ORDER BY i.id DESC";
$res = $conn->query($sql);
?>

<div class="container-fluid py-4">
    <div class="card shadow border-0 mb-4">
        <div class="card-header bg-navy text-white" style="background-color:#001d4a;">
            <h5 class="mb-0">Billing & Invoice Master Report</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="small fw-bold">From Date</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="<?= $from_date ?>">
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold">To Date</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="<?= $to_date ?>">
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold">Search (Name/Booking/Inv)</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="<?= $search ?>" placeholder="Search...">
                </div>
                <div class="col-md-2">
                    <label class="small fw-bold">Payment Filter</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="All" <?= $status=='All'?'selected':'' ?>>All Bills</option>
                        <option value="Paid" <?= $status=='Paid'?'selected':'' ?>>Fully Paid</option>
                        <option value="Balance" <?= $status=='Balance'?'selected':'' ?>>Bill Payment Balance</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Filter Records</button>
                    <a href="billing_list_report.php" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date & Invoice</th>
                        <th>Booking / Customer</th>
                        <th class="text-end">Grand Total</th>
                        <th class="text-end">Advance</th>
                        <th class="text-end">Net Balance</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <span class="fw-bold"><?= date('d-M-y', strtotime($row['invoice_date'])) ?></span><br>
                            <small class="text-primary"><?= $row['invoice_no'] ?></small>
                        </td>
                        <td>
                            <div class="fw-bold"><?= $row['contact_person'] ?></div>
                            <small class="text-muted">Booking: <?= $row['tracking_no'] ?></small>
                        </td>
                        <td class="text-end">₹<?= number_format($row['grand_total'], 2) ?></td>
                        <td class="text-end text-success">₹<?= number_format($row['total_advance_paid'], 2) ?></td>
                        <td class="text-end fw-bold <?= $row['final_balance'] > 0 ? 'text-danger' : 'text-success' ?>">
                            ₹<?= number_format(abs($row['final_balance']), 2) ?>
                            <br><small class="badge <?= $row['final_balance'] <= 0 ? 'bg-success' : 'bg-warning text-dark' ?>">
                                <?= $row['final_balance'] <= 0 ? 'Fully Paid' : 'Due' ?>
                            </small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown">Print</button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="invoice_view.php?id=<?= $row['id'] ?>&mode=without" target="_blank">Plain Paper</a></li>
                                        <li><a class="dropdown-item" href="invoice_view.php?id=<?= $row['id'] ?>&mode=with" target="_blank">Letterhead</a></li>
                                    </ul>
                                </div>

                                <?php if($row['final_balance'] > 0): ?>
                                    <a href="bill_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary ms-1">Edit</a>
                                    <button onclick="deleteBill(<?= $row['id'] ?>)" class="btn btn-sm btn-outline-danger ms-1">Delete</button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-light ms-1" disabled title="Fully Paid">Edit</button>
                                    <button class="btn btn-sm btn-light ms-1" disabled title="Fully Paid">Delete</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>