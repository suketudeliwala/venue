<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

// Fetch only bookings that have utilization filed BUT are not yet Billed
$sql = "SELECT b.id, b.tracking_no, b.function_name, c.contact_person 
        FROM vms_booking_master b
        JOIN vms_customers c ON b.customer_id = c.id
        WHERE b.id IN (SELECT DISTINCT booking_id FROM vms_utilization_reports)
        AND b.status NOT IN ('Billed', 'Settled', 'Cancelled')
        ORDER BY b.id DESC";
$ready_bookings = $conn->query($sql);
?>

<div class="container py-4">
    <div class="card shadow border-0">
        <div class="card-header bg-navy text-white" style="background-color:#001d4a;">
            <h5 class="mb-0">Generate Final Settlement Bill</h5>
        </div>
        <div class="card-body">
            <label class="form-label fw-bold">Select Completed Event for Billing:</label>
            <select class="form-select form-select-lg mb-3" onchange="location.href='bill_generate.php?booking_id='+this.value">
                <option value="">-- Choose Event --</option>
                <?php while($row = $ready_bookings->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= $row['tracking_no'] ?> - <?= $row['function_name'] ?> (<?= $row['contact_person'] ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>
</div>