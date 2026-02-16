<?php 
include("../includes/config.php"); 
include("../includes/header.php"); 
?>

<div class="container py-5 min-vh-100">
    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-header bg-primary text-white p-4" style="background-color: #001d4a !important;">
            <h3 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Venue Booking Enquiry Form</h3>
        </div>
        <div class="card-body p-4 p-md-5">
            <form id="enquiryForm" action="../api/save_enquiry.php" method="POST">
                
                <div class="row g-3 mb-5">
                    <div class="col-12"><h5 class="text-primary border-bottom pb-2">Function Details</h5></div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Name of Function</label>
                        <input type="text" name="function_name" class="form-control" placeholder="Complete name of the event" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Starting Time</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Finishing Time</label>
                        <input type="time" name="finish_time" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Duration</label>
                        <select name="duration" class="form-select">
                            <option>3 Hours</option><option>4 Hours</option><option>Half day</option><option>Full day</option><option>Multiple Days</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Type of Function</label>
                        <select name="function_type" class="form-select">
                            <option>Personal</option><option>Business</option><option>Charitable</option><option>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Purpose</label>
                        <select name="purpose" class="form-select">
                            <option>Marriage</option><option>Birthday Party</option><option>Condolence Meeting</option>
                            <option>Conference</option><option>Seminar</option><option>Sports Event</option><option>Fun Fair</option><option>Other</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Full Details of Function (Rich Text Details)</label>
                        <textarea name="function_details" class="form-control" rows="5" placeholder="Describe the event flow..."></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Approximate Number Attending</label>
                        <input type="number" name="approx_attendees" class="form-control">
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-12"><h5 class="text-primary border-bottom pb-2">Service Requirements</h5></div>
                    <div class="col-md-12">
                        <p class="fw-bold mb-2">Do you need services from following providers?</p>
                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="need_decorator" value="1"> <label>Decorators</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="need_caterer" value="1"> <label>Caterers</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="need_sound" value="1"> <label>Sound System</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="need_wifi" value="1"> <label>Wi-Fi</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="need_manager" value="1"> <label>Event Manager</label></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Any Other Provider</label>
                        <input type="text" name="other_services" class="form-control" placeholder="e.g. Florist, Photographer">
                    </div>


                    <div class="col-md-6">
                        <label class="form-label fw-bold">Additional Equipment Needed (Hold Ctrl to select)</label>
                        <select name="equipment_requested[]" class="form-select" multiple>
                            <?php 
                            $e_res = $conn->query("SELECT id, equipment_name FROM vms_equipments WHERE status='Available'");
                            while($e = $e_res->fetch_assoc()) echo "<option value='{$e['id']}'>{$e['equipment_name']}</option>";
                            ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-12"><h5 class="text-primary border-bottom pb-2">Compliance & Security</h5></div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Arranging in conjunction with other organizations?</label>
                        <div class="d-flex gap-3">
                            <div class="form-check"><input class="form-check-input" type="radio" name="joint_org" value="Yes" id="jointYes"> <label for="jointYes">Yes</label></div>
                            <div class="form-check"><input class="form-check-input" type="radio" name="joint_org" value="No" id="jointNo" checked> <label for="jointNo">No</label></div>
                        </div>
                        <input type="text" name="joint_org_name" class="form-control mt-2" placeholder="Organization Name">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Political Attachment?</label>
                        <select name="is_political" class="form-select">
                            <option value="0">No</option><option value="1">Yes</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Ticketed Event?</label>
                        <select name="is_ticketed" class="form-select">
                            <option value="0">No</option><option value="1">Yes</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-12"><h5 class="text-primary border-bottom pb-2">Your Details</h5></div>
                    <div class="col-md-6"><label class="form-label fw-bold">Company Name</label><input type="text" name="company_name" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label fw-bold">Person Name</label><input type="text" name="applicant_name" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label fw-bold">Email</label><input type="email" name="applicant_email" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label fw-bold">Mobile</label><input type="text" name="applicant_mobile" class="form-control" required></div>
                    <div class="col-md-12"><label class="form-label fw-bold">Postal Address</label><textarea name="applicant_address" class="form-control" rows="2"></textarea></div>
                    
                    <div class="col-md-6">
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="is_member" id="memberSwitch" onchange="document.getElementById('memberNoDiv').style.display = this.checked ? 'block' : 'none'">
                            <label class="form-check-label fw-bold" for="memberSwitch">I am a Member of <?= $org_short_name ?></label>
                        </div>
                    </div>
                    <div class="col-md-6" id="memberNoDiv" style="display:none;">
                        <label class="form-label fw-bold">Membership No</label>
                        <input type="text" name="member_no" class="form-control">
                    </div>
                </div>

                <div class="text-center">
                    <button type="button" class="btn btn-primary btn-lg px-5 shadow rounded-pill" onclick="showTermsModal()">Review Terms & Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="rulesModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-navy text-white">
                <h5 class="modal-title">Rules & Regulations for Booking</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="p-3 bg-white border rounded">
                    <h6 class="text-center fw-bold text-navy mb-3"><?= $org_full_name ?></h6>
                    <p class="small text-center mb-4"><?= $org_address ?></p>
                    <div style="font-size: 0.85rem; line-height: 1.6;">
                        <ol>
                            <li>Office hours: 11.00 am to 5.00 pm (Excl. Sundays/Holidays).</li>
                            <li>Venue is non-transferable.</li>
                            <li><strong>Only vegetarian food is allowed.</strong></li>
                            <li>Settle accounts within 7 days of function.</li>
                            <li>Only authorized decorators permitted.</li>
                            <li>Applicant is responsible for behavior and damages/cleaning.</li>
                            <li>Maximum 3 vehicles parking allowed.</li>
                            <li>GST and statutory levies applicable at prevailing rates.</li>
                            <li>A.C. switch on 15 mins before/off 15 mins before.</li>
                            <li>No refund for electricity failure or causes beyond management control.</li>
                            <li>Venue booking confirmed only after receipt of deposit.</li>
                            <li>Sound system use subject to Gov. regulations and permissions.</li>
                            <li>Management not responsible for theft of valuables/jewelry.</li>
                            <li>Trust decision is final for all disputes/amendments.</li>
                            <li>Rules are binding on applicant and attendees.</li>
                            <li>Applicant indemnifies <?= $org_short_name ?> against claims.</li>
                            <li>GST once Paid is not refundable against program cancellation.</li>
                            <li>Strictly prohibited to stick pamphlets/posters in premises.</li>
                        </ol>
                    </div>
                    <hr>
                    <div class="form-check bg-warning bg-opacity-10 p-3 rounded border border-warning">
                        <input class="form-check-input ms-0 me-2" type="checkbox" id="confirmRules">
                        <label class="form-check-label fw-bold" for="confirmRules">
                            We have read the above rules & regulations and understood the same & its implication & shall abide by the same.
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success px-4" onclick="validateAndSubmit()">Acknowledge & Save Enquiry</button>
            </div>
        </div>
    </div>
</div>

<script>
// 1. Show the Modal
function showTermsModal() {
    // Basic validation before showing modal
    const form = document.getElementById('enquiryForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    const myModal = new bootstrap.Modal(document.getElementById('rulesModal'));
    myModal.show();
}

// 2. Final Submit after Checkbox
function validateAndSubmit() {
    if (document.getElementById('confirmRules').checked) {
        document.getElementById('enquiryForm').submit();
    } else {
        alert("Please check the box to acknowledge and accept the Rules & Regulations.");
    }
}
</script>

<?php include("../includes/footer.php"); ?>