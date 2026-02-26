<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

$sql = "SELECT * FROM vms_cancellation_policy ORDER BY days_before_min DESC";
$res = $conn->query($sql);
?>

<div class="container py-4">
    <div class="p-5 bg-white shadow-sm printable-area" id="reportArea">
        <div class="row mb-5 align-items-center">
            <div class="col-2 text-center"><img src="../assets/images/org_logo.png" style="width: 100px;"></div>
            <div class="col-10 text-center">
                <h3 class="mb-0 fw-bold"><?= $org_full_name ?></h3>
                <h4 class="mt-2 text-decoration-underline fw-bold">OFFICIAL CANCELLATION & REFUND POLICY</h4>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <table class="table table-bordered border-dark text-center" id="reportTable">
                    <thead class="bg-light fw-bold">
                        <tr>
                            <th class="border-dark">Days Prior to Event</th>
                            <th class="border-dark">Deduction Percentage</th>
                            <th class="border-dark">Refund Eligibility</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $res->fetch_assoc()): ?>
                        <tr class="border-dark" style="height: 60px; vertical-align: middle;">
                            <td class="border-dark">
                                <?php if($row['days_before_max'] > 500): ?>
                                    Above <?= $row['days_before_min'] ?> Days
                                <?php else: ?>
                                    Between <?= $row['days_before_min'] ?> to <?= $row['days_before_max'] ?> Days
                                <?php endif; ?>
                            </td>
                            <td class="border-dark fw-bold text-danger"><?= $row['deduction_percent'] ?>%</td>
                            <td class="border-dark"><?= (100 - $row['deduction_percent']) ?>% of Rent</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="mt-4 border p-3 rounded bg-light">
                    <p class="small mb-1"><strong>Note:</strong></p>
                    <ul class="small mb-0">
                        <li>The Refundable Security Deposit (RSD) is always refunded 100% regardless of cancellation timing.</li>
                        <li>Cancellation requests must be submitted in writing with the original booking receipt.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center mt-3 d-print-none">
        <button onclick="window.print()" class="btn btn-dark">Print Policy to A4</button>
    </div>
</div>
