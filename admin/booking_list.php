<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

$sql = "SELECT b.*, c.contact_person, 
        (SELECT COUNT(*) FROM vms_booking_slots WHERE booking_id = b.id) as slot_count 
        FROM vms_booking_master b 
        JOIN vms_customers c ON b.customer_id = c.id 
        ORDER BY b.id DESC";
$res = $conn->query($sql);
?>

<div class="container-fluid py-4">
    <div class="card shadow border-0">
        <div class="card-header bg-navy text-white d-flex justify-content-between align-items-center" style="background-color: #001d4a !important;">
            <h5 class="mb-0">Venue Bookings Master List</h5>
            <a href="booking_new.php" class="btn btn-sm btn-warning fw-bold">+ New Booking</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ref No</th>
                        <th>Event & Customer</th>
                        <th>Venues/Slots</th>
                        <th>Total Rent</th>
                        <th>Total RSD</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><span class="fw-bold text-primary"><?= $row['tracking_no'] ?></span></td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($row['function_name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($row['contact_person']) ?></small>
                        </td>
                        <td><span class="badge bg-info text-dark"><?= $row['slot_count'] ?> Slot(s)</span></td>
                        <td>₹<?= number_format($row['total_rent'], 2) ?></td>
                        <td>₹<?= number_format($row['total_rsd'], 2) ?></td>
                        <td>
                            <span class="badge bg-<?= $row['status'] == 'Confirmed' ? 'success' : 'danger' ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="booking_view.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                            <a href="booking_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <button class="btn btn-sm btn-outline-danger" onclick="cancelBooking(<?= $row['id'] ?>)"><i class="bi bi-x-circle"></i></button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function cancelBooking(id) {
    if(confirm("Are you sure you want to cancel this booking? Cancellation charges will apply as per policy.")) {
        window.location.href = "booking_cancel_process.php?id=" + id;
    }
}
</script>

<?php include("../includes/footer.php"); ?>