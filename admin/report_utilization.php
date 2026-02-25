<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

$filter = $_GET['filter'] ?? 'All';
$where = " WHERE 1=1 ";
if($filter == 'Prepared') $where .= " AND ur.id IS NOT NULL ";
if($filter == 'Pending')  $where .= " AND ur.id IS NULL AND CONCAT(s.booking_date, ' ', s.finish_time) < NOW() ";

$sql = "SELECT s.*, v.venue_name, b.tracking_no, c.contact_person, ur.id as report_id, b.status as booking_status
        FROM vms_booking_slots s
        JOIN vms_booking_master b ON s.booking_id = b.id
        JOIN vms_venues v ON s.venue_id = v.id
        JOIN vms_customers c ON b.customer_id = c.id
        LEFT JOIN vms_utilization_reports ur ON s.id = ur.slot_id
        $where ORDER BY s.booking_date DESC";
$res = $conn->query($sql);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between mb-3">
        <h4>Utilization Master Report</h4>
        <form class="d-flex gap-2">
            <select name="filter" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="All" <?= $filter=='All'?'selected':'' ?>>All Reports</option>
                <option value="Prepared" <?= $filter=='Prepared'?'selected':'' ?>>Prepared Only</option>
                <option value="Pending" <?= $filter=='Pending'?'selected':'' ?>>Pending Only</option>
            </select>
            <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-dark">Print</button>
        </form>
    </div>

    <div class="card shadow border-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Event Date</th>
                    <th>Venue</th>
                    <th>Booking Ref</th>
                    <th>Customer</th>
                    <th>Report Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $res->fetch_assoc()): ?>
                <tr>
                    <td><?= date('d-M-y', strtotime($row['booking_date'])) ?></td>
                    <td><?= $row['venue_name'] ?></td>
                    <td><?= $row['tracking_no'] ?></td>
                    <td><?= $row['contact_person'] ?></td>
                    <td>
                        <span class="badge bg-<?= $row['report_id'] ? 'success' : 'danger' ?>">
                            <?= $row['report_id'] ? 'Prepared' : 'Pending' ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <?php if($row['report_id']): ?>
                            <?php if($row['booking_status'] != 'Settled'): ?>
                                <a href="utilization_edit.php?id=<?= $row['report_id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-secondary" disabled>Billed</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="utilization_new.php?slot_id=<?= $row['id'] ?>" class="btn btn-sm btn-danger">File Now</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>