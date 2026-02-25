<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

// 1. Validate Access
if(!isset($_GET['slot_id']) || empty($_GET['slot_id'])) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>
            <h5><i class='bi bi-exclamation-triangle-fill me-2'></i> Access Denied</h5>
            <p>Please go to the <strong>Pending Utilization</strong> tab to file a report.</p>
            <a href='booking_list.php?tab=pending_utilization' class='btn btn-danger'>Go to List</a>
          </div></div>";
    include("../includes/footer.php"); exit;
}

$slot_id = intval($_GET['slot_id']);

// 2. Fetch All Vendors
$vendor_sql = "SELECT id, contact_person, company_name 
               FROM vms_customers 
               WHERE customer_type = 'Vendor'
               ORDER BY contact_person ASC";
$vendor_res = $conn->query($vendor_sql);

$all_vendors = [];
while($v = $vendor_res->fetch_assoc()) {
    $all_vendors[] = $v;
}

// 3. Fetch Specific Slot & Booking Details (NOW INCLUDING net_payable)
$sql = "SELECT s.*, v.venue_name, b.tracking_no as b_no, b.function_name, b.net_payable, c.contact_person, e.tracking_no as enq_no 
        FROM vms_booking_slots s
        JOIN vms_booking_master b ON s.booking_id = b.id
        JOIN vms_venues v ON s.venue_id = v.id
        JOIN vms_customers c ON b.customer_id = c.id
        LEFT JOIN vms_enquiries e ON b.enquiry_id = e.id
        WHERE s.id = $slot_id";

$data_res = $conn->query($sql);
if($data_res->num_rows == 0) { die("Error: No data found for this slot."); }
$row = $data_res->fetch_assoc();

// 4. CALCULATE OUTSTANDING BALANCE
$paid_res = $conn->query("SELECT SUM(total_amount) as paid FROM vms_receipts WHERE booking_id = ".$row['booking_id']);
$paid_data = $paid_res->fetch_assoc();
$total_paid = $paid_data['paid'] ?? 0;
$outstanding = $row['net_payable'] - $total_paid;
?>

<div class="container py-4">
    <div class="card shadow border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Utilization Report for: <span class="text-warning"><?= htmlspecialchars($row['venue_name']) ?></span></h5>
            <span class="badge bg-primary">Ref: <?= htmlspecialchars($row['b_no']) ?></span>
        </div>
        <form action="../api/save_utilization.php" method="POST" class="card-body">
            <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">
            <input type="hidden" name="slot_id" value="<?= $slot_id ?>">

            <div class="row g-3 mb-4 bg-light p-3 rounded border">
                <div class="col-md-3">
                    <label class="small text-muted d-block">Customer / Enquiry</label>
                    <strong><?= htmlspecialchars($row['contact_person']) ?></strong> <br>
                    <small class="text-primary"><?= $row['enq_no'] ?: 'Direct Booking' ?></small>
                </div>
                <div class="col-md-3">
                    <label class="small text-muted d-block">Scheduled Time</label>
                    <strong><?= date('d-M-y', strtotime($row['booking_date'])) ?></strong><br>
                    <?= $row['start_time'] ?> to <?= $row['finish_time'] ?>
                </div>
                <div class="col-md-6 text-end">
                    <label class="small text-muted d-block">Function</label>
                    <span class="h6"><?= htmlspecialchars($row['function_name']) ?></span>
                    <?php if($outstanding > 0): ?>
                        <span class="ms-3 badge bg-danger" style="font-size: 0.9rem;">
                            Outstanding: ₹<?= number_format($outstanding, 2) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="text-primary fw-bold border-bottom pb-2">1. Timings & Electricity</h6>
                    <div class="row g-2">
                        <div class="col-6"><label class="small">Actual Entry</label><input type="time" name="actual_start" class="form-control" required></div>
                        <div class="col-6"><label class="small">Actual Exit</label><input type="time" name="actual_end" class="form-control" required></div>
                        <div class="col-6"><label class="small">EB Reading Start</label><input type="number" step="0.01" name="eb_start" class="form-control"></div>
                        <div class="col-6"><label class="small">EB Reading End</label><input type="number" step="0.01" name="eb_end" class="form-control"></div>
                    </div>
                </div>

                <div class="col-md-6">
                    <h6 class="text-primary fw-bold border-bottom pb-2">2. Service Providers</h6>
                    <div class="mb-2">
                        <label class="small">Decorator</label>
                        <select name="decorator_id" class="form-select">
                            <option value="">-- Select Decorator --</option>
                            <?php foreach($all_vendors as $v): ?>
                                <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['contact_person']) ?> (<?= htmlspecialchars($v['company_name']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="small">Caterer</label>
                        <select name="caterer_id" class="form-select">
                            <option value="">-- Select Caterer --</option>
                            <?php foreach($all_vendors as $v): ?>
                                <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['contact_person']) ?> (<?= htmlspecialchars($v['company_name']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-12">
                    <h6 class="text-danger fw-bold border-bottom pb-2">3. Incidents, Overtime & Damages</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small fw-bold">Loss / Breakage Details</label>
                            <textarea name="damage_details" class="form-control mb-2" rows="2" placeholder="List items damaged..."></textarea>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" name="damage_charges" class="form-control" placeholder="Charges for Damage">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold">Extra Services / Overtime</label>
                            <textarea name="extra_details" class="form-control mb-2" rows="2" placeholder="Details of extra hours or services..."></textarea>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" name="extra_charges" class="form-control" placeholder="Extra Charges">
                                <div class="input-group-text">
                                    <input type="checkbox" name="overtime_flag" value="1" class="form-check-input mt-0 me-2"> Overtime?
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <h6 class="text-secondary fw-bold border-bottom pb-2">4. Manager's Review</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="small fw-bold d-block mb-2">Facility Checklist</label>
                            <div class="form-check"><input class="form-check-input" type="checkbox" checked> <label class="form-check-label">A/C Remotes OK</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" checked> <label class="form-check-label">Kitchen Cleaned</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" checked> <label class="form-check-label">Washrooms Checked</label></div>
                        </div>
                        <div class="col-md-8">
                            <label class="small fw-bold">Detailed Remarks / Observations</label>
                            <textarea name="manager_remarks" class="form-control" rows="4" placeholder="Overall event summary..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-end border-top pt-3">
                <button type="submit" class="btn btn-primary btn-lg px-5">Submit Utilization Report</button>
            </div>
        </form>
    </div>
</div>

<?php include("../includes/footer.php"); ?>