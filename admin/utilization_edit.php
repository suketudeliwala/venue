<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

if(!isset($_GET['id'])) { header("Location: report_utilization.php"); exit; }
$report_id = intval($_GET['id']);

// 1. Fetch Existing Report and linked Booking/Slot details
$sql = "SELECT ur.*, s.booking_date, s.start_time as sch_start, s.finish_time as sch_end, 
               v.venue_name, b.tracking_no as b_no, b.function_name, b.net_payable, c.contact_person
        FROM vms_utilization_reports ur
        JOIN vms_booking_slots s ON ur.slot_id = s.id
        JOIN vms_booking_master b ON ur.booking_id = b.id
        JOIN vms_venues v ON s.venue_id = v.id
        JOIN vms_customers c ON b.customer_id = c.id
        WHERE ur.id = $report_id";

$res = $conn->query($sql);
if($res->num_rows == 0) { die("Report not found."); }
$data = $res->fetch_assoc();

// 2. Fetch Vendors for dropdowns
$vendor_res = $conn->query("SELECT id, contact_person, company_name FROM vms_customers WHERE customer_type = 'Vendor'");
$all_vendors = [];
while($v = $vendor_res->fetch_assoc()) { $all_vendors[] = $v; }

// 3. Calculate Outstanding
$paid_data = $conn->query("SELECT SUM(total_amount) as paid FROM vms_receipts WHERE booking_id = ".$data['booking_id'])->fetch_assoc();
$outstanding = $data['net_payable'] - ($paid_data['paid'] ?? 0);
?>

<div class="container py-4">
    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between">
            <h5 class="mb-0">Edit Utilization: <?= htmlspecialchars($data['venue_name']) ?></h5>
            <span>Ref: <?= $data['b_no'] ?></span>
        </div>
        <form action="../api/update_utilization.php" method="POST" class="card-body">
            <input type="hidden" name="report_id" value="<?= $report_id ?>">
            <input type="hidden" name="booking_id" value="<?= $data['booking_id'] ?>">

            <div class="row g-3 mb-4 bg-light p-3 rounded border">
                <div class="col-md-6">
                    <strong>Customer:</strong> <?= $data['contact_person'] ?> | 
                    <strong>Function:</strong> <?= $data['function_name'] ?>
                    <?php if($outstanding > 0): ?>
                        <span class="badge bg-danger ms-2">Bal: ₹<?= number_format($outstanding, 2) ?></span>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 text-end">
                    <strong>Scheduled:</strong> <?= date('d-M-y', strtotime($data['booking_date'])) ?> 
                    (<?= $data['sch_start'] ?> - <?= $data['sch_end'] ?>)
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="text-primary fw-bold border-bottom pb-2">1. Timings & Electricity</h6>
                    <div class="row g-2">
                        <div class="col-6"><label class="small">Actual Entry</label>
                            <input type="time" name="actual_start" class="form-control" value="<?= $data['actual_start_time'] ?>" required></div>
                        <div class="col-6"><label class="small">Actual Exit</label>
                            <input type="time" name="actual_end" class="form-control" value="<?= $data['actual_end_time'] ?>" required></div>
                        <div class="col-6"><label class="small">EB Start</label>
                            <input type="number" step="0.01" name="eb_start" class="form-control" value="<?= $data['eb_reading_start'] ?>"></div>
                        <div class="col-6"><label class="small">EB End</label>
                            <input type="number" step="0.01" name="eb_end" class="form-control" value="<?= $data['eb_reading_end'] ?>"></div>
                    </div>
                </div>

                <div class="col-md-6">
                    <h6 class="text-primary fw-bold border-bottom pb-2">2. Service Providers</h6>
                    <label class="small">Decorator</label>
                    <select name="decorator_id" class="form-select mb-2">
                        <option value="">-- Select --</option>
                        <?php foreach($all_vendors as $v): ?>
                            <option value="<?= $v['id'] ?>" <?= ($data['decorator_id'] == $v['id']) ? 'selected' : '' ?>>
                                <?= $v['contact_person'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label class="small">Caterer</label>
                    <select name="caterer_id" class="form-select">
                        <option value="">-- Select --</option>
                        <?php foreach($all_vendors as $v): ?>
                            <option value="<?= $v['id'] ?>" <?= ($data['caterer_id'] == $v['id']) ? 'selected' : '' ?>>
                                <?= $v['contact_person'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-12">
                    <h6 class="text-danger fw-bold border-bottom pb-2">3. Damages & Extra Charges</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <textarea name="damage_details" class="form-control mb-2" rows="2"><?= $data['damage_details'] ?></textarea>
                            <input type="number" name="damage_charges" class="form-control" value="<?= $data['damage_charges'] ?>">
                        </div>
                        <div class="col-md-6">
                            <textarea name="extra_details" class="form-control mb-2" rows="2"><?= $data['extra_services_details'] ?></textarea>
                            <div class="input-group">
                                <input type="number" name="extra_charges" class="form-control" value="<?= $data['extra_services_charges'] ?>">
                                <div class="input-group-text">
                                    <input type="checkbox" name="overtime_flag" value="1" class="form-check-input mt-0 me-2" <?= $data['overtime_flag'] ? 'checked' : '' ?>> Overtime?
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <h6 class="text-secondary fw-bold border-bottom pb-2">4. Manager Remarks</h6>
                    <textarea name="manager_remarks" class="form-control" rows="4"><?= $data['manager_remarks'] ?></textarea>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary px-5">Update Utilization Report</button>
            </div>
        </form>
    </div>
</div>

<?php include("../includes/footer.php"); ?>