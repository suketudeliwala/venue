<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

// Fetch only 'New' enquiries
$enq_list = $conn->query("SELECT id, applicant_name, applicant_mobile FROM vms_enquiries WHERE status = 'New' ORDER BY id DESC");
?>

<div class="container py-4">
    <div class="card shadow border-0">
        <div class="card-header bg-info text-white py-3">
            <h5 class="mb-0"><i class="bi bi-person-plus-fill me-2"></i>Register Customer from Enquiry</h5>
        </div>
        <div class="card-body p-4">
            <div class="mb-4">
                <label class="form-label fw-bold">Select Applicant from New Enquiries</label>
                <select id="enq_selector" class="form-select form-select-lg border-info shadow-sm">
                    <option value="">-- Choose Applicant --</option>
                    <?php while($e = $enq_list->fetch_assoc()): ?>
                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['applicant_name']) ?> (<?= $e['applicant_mobile'] ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>

            <form action="../api/save_customer.php" method="POST" id="custForm" style="display:none;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Contact Person Name</label>
                        <input type="text" name="contact_person" id="c_person" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Company Name (Required for Billing)</label>
                        <input type="text" name="company_name" id="c_company" class="form-control" required placeholder="Enter individual name if no company">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Address</label>
                        <textarea name="address" id="c_address" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Mobile (Validation Key)</label>
                        <input type="text" name="mobile" id="c_mobile" class="form-control bg-light" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Email ID</label>
                        <input type="email" name="email" id="c_email" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">PAN No</label>
                        <input type="text" name="pan_no" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">GST No</label>
                        <input type="text" name="gst_no" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Remarks</label>
                        <input type="text" name="remarks" class="form-control">
                    </div>

                    <div class="col-12 text-end mt-4 pt-3 border-top">
                        <a href="customer_list.php" class="btn btn-secondary px-4 me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 shadow-sm">Register Customer Master</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('enq_selector').addEventListener('change', function() {
    const id = this.value;
    const form = document.getElementById('custForm');
    
    if(!id) {
        form.style.display = 'none';
        return;
    }

    // Attempt to fetch from the API
    fetch('../api/get_enquiry_details.php?id=' + id)
        .then(response => {
            if (!response.ok) {
                // If the file is missing or has a PHP error, this will catch it
                throw new Error('Server returned ' + response.status + ' ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            if(data.error) {
                alert("Application Error: " + data.error);
                return;
            }

            // Successfully received data
            form.style.display = 'block';
            
            // Map the enquiry fields to customer form fields
            document.getElementById('c_person').value = data.applicant_name || '';
            document.getElementById('c_mobile').value = data.applicant_mobile || '';
            document.getElementById('c_email').value = data.applicant_email || '';
            document.getElementById('c_address').value = data.applicant_address || '';
            
            // Per your requirement: Use applicant name if company is empty
            document.getElementById('c_company').value = (data.company_name && data.company_name.trim() !== "") 
                ? data.company_name 
                : data.applicant_name;
        })
        .catch(err => {
            console.error('Fetch error:', err);
            alert("Connection Error: " + err.message + "\n\nPossible causes:\n1. api/get_enquiry_details.php has a syntax error.\n2. The path ../api/ is incorrect for your setup.");
        });
});
</script>