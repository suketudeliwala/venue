<?php
include("../includes/config.php");
// Strict Admin Session Check
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
include("../includes/header_admin.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$res = $conn->query("SELECT * FROM vms_enquiries WHERE id = $id");
if ($res->num_rows == 0) { echo "<div class='container mt-5'>Enquiry not found.</div>"; exit; }
$e = $res->fetch_assoc();
?>

<div class="container-fluid py-4 min-vh-100 d-print-block">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="enquiry_list.php">Enquiries</a></li>
                <li class="breadcrumb-item active">View Details</li>
            </ol>
        </nav>
        <div class="btn-group">
            <button onclick="window.print()" class="btn btn-navy"><i class="bi bi-printer me-2"></i>Print Summary</button>
            <a href="enquiry_list.php" class="btn btn-outline-secondary">Back to List</a>
        </div>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-<?= ($_GET['status'] == 'success') ? 'success' : 'danger' ?> alert-dismissible fade show d-print-none" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="card-header p-4" style="background-color: #001d4a !important; color: #ffffff !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><?= htmlspecialchars($e['function_name']) ?></h4>
                    <span class="badge bg-warning text-dark">Ref: <?= $e['tracking_no'] ?></span>
                </div>
            </div>
            <div class="text-end">
                <p class="mb-0 small opacity-75">Received On</p>
                <h6 class="mb-0"><?= date('d-M-Y H:i', strtotime($e['created_at'])) ?></h6>
            </div>
        </div>

        <div class="card-body p-4 p-md-5">
            <div class="row g-4">
                
                <div class="col-md-6">
                    <h5 class="text-primary border-bottom pb-2 mb-3"><i class="bi bi-calendar-event me-2"></i>Event Schedule</h5>
                    <table class="table table-sm table-borderless">
                        <tr><th width="40%">Start Date:</th><td><?= date('d-M-Y', strtotime($e['start_date'])) ?></td></tr>
                        <tr><th>End Date:</th><td><?= date('d-M-Y', strtotime($e['end_date'])) ?></td></tr>
                        <tr><th>Time:</th><td><?= $e['start_time'] ?> to <?= $e['finish_time'] ?></td></tr>
                        <tr><th>Duration:</th><td><?= $e['duration'] ?></td></tr>
                        <tr><th>Function Type:</th><td><?= $e['function_type'] ?></td></tr>
                        <tr><th>Purpose:</th><td><?= $e['purpose'] ?></td></tr>
                        <tr><th>Attendees:</th><td><?= $e['approx_attendees'] ?> Persons</td></tr>
                    </table>
                </div>

                <div class="col-md-6">
                    <h5 class="text-primary border-bottom pb-2 mb-3"><i class="bi bi-person-badge me-2"></i>Applicant Details</h5>
                    <table class="table table-sm table-borderless">
                        <tr><th width="40%">Contact Person:</th><td><?= htmlspecialchars($e['applicant_name']) ?></td></tr>
                        <tr><th>Company/Org:</th><td><?= htmlspecialchars($e['company_name'] ?: 'Individual') ?></td></tr>
                        <tr><th>Email:</th><td><?= $e['applicant_email'] ?></td></tr>
                        <tr><th>Mobile:</th><td><?= $e['applicant_mobile'] ?></td></tr>
                        <tr><th>Membership:</th><td><?= $e['is_member'] ? "Member (ID: ".$e['member_no'].")" : "Non-Member" ?></td></tr>
                        <tr><th>Address:</th><td><?= nl2br(htmlspecialchars($e['applicant_address'])) ?></td></tr>
                    </table>
                </div>

                <div class="col-12 mt-4">
                    <h5 class="text-primary border-bottom pb-2 mb-3"><i class="bi bi-tools me-2"></i>Service & Equipment Requests</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="fw-bold mb-2">Requested Services:</p>
                            <ul class="list-group list-group-horizontal flex-wrap">
                                <li class="list-group-item border-0 ps-0"><i class="bi <?= $e['need_decorator'] ? 'bi-check-square-fill text-success' : 'bi-square' ?> me-2"></i>Decorator</li>
                                <li class="list-group-item border-0 ps-0"><i class="bi <?= $e['need_caterer'] ? 'bi-check-square-fill text-success' : 'bi-square' ?> me-2"></i>Caterer</li>
                                <li class="list-group-item border-0 ps-0"><i class="bi <?= $e['need_sound'] ? 'bi-check-square-fill text-success' : 'bi-square' ?> me-2"></i>Sound</li>
                                <li class="list-group-item border-0 ps-0"><i class="bi <?= $e['need_wifi'] ? 'bi-check-square-fill text-success' : 'bi-square' ?> me-2"></i>Wi-Fi</li>
                                <li class="list-group-item border-0 ps-0"><i class="bi <?= $e['need_manager'] ? 'bi-check-square-fill text-success' : 'bi-square' ?> me-2"></i>Event Manager</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <p class="fw-bold mb-1">Other Providers:</p>
                            <p class="text-muted"><?= htmlspecialchars($e['other_services'] ?: 'None') ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-4">
                    <h5 class="text-primary border-bottom pb-2 mb-3"><i class="bi bi-shield-check me-2"></i>Compliance & Event Details</h5>
                    <div class="bg-light p-3 rounded">
                        <div class="row">
                            <div class="col-md-4"><strong>Joint Organization:</strong> <?= $e['joint_org_name'] ?: 'None' ?></div>
                            <div class="col-md-4"><strong>Political Attachment:</strong> <?= $e['is_political'] ? 'Yes' : 'No' ?></div>
                            <div class="col-md-4"><strong>Ticketed Event:</strong> <?= $e['is_ticketed'] ? 'Yes' : 'No' ?></div>
                        </div>
                        <hr>
                        <p class="fw-bold mb-1">Detailed Description:</p>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($e['function_details'])) ?></p>
                    </div>
                </div>

                <div class="col-12 mt-5 d-print-none">
                    <div class="card bg-warning bg-opacity-10 border-warning">
                        <div class="card-body">
                            <h6 class="fw-bold"><i class="bi bi-gear-fill me-2"></i>Internal Admin Action</h6>
                            <form action="../api/update_enquiry_status.php" method="POST" class="row g-3">
                                <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                <div class="col-md-4">
                                    <select name="status" class="form-select">
                                        <option value="New" <?= $e['status'] == 'New' ? 'selected' : '' ?>>New Enquiry</option>
                                        <option value="Contacted" <?= $e['status'] == 'Contacted' ? 'selected' : '' ?>>Contacted</option>
                                        <option value="Converted" <?= $e['status'] == 'Converted' ? 'selected' : '' ?>>Converted to Booking</option>
                                        <option value="Rejected" <?= $e['status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-warning">Update Status</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
            </div> </div> </div> </div>

<style>
@media print {
    body { background-color: white !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    .bg-navy { background-color: #001d4a !important; color: white !important; -webkit-print-color-adjust: exact; }
    .text-primary { color: #006992 !important; -webkit-print-color-adjust: exact; }
    .btn, nav, .d-print-none { display: none !important; }
}
</style>

<?php include("../includes/footer.php"); ?>