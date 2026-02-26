<?php 
include("../includes/config.php"); 
include("../includes/header_admin.php"); 

$from_date = $_GET['from_date'] ?? date('Y-m-01');
$to_date   = $_GET['to_date'] ?? date('Y-m-d');
$v_name    = $_GET['vendor_name'] ?? '';

$where = " WHERE i.invoice_date BETWEEN '$from_date' AND '$to_date' AND c.customer_type = 'Vendor' ";
if(!empty($v_name)) { $where .= " AND c.contact_person LIKE '%$v_name%' "; }

$sql = "SELECT c.contact_person as v_name, c.company_name, 
               COUNT(ur.id) as total_events, 
               SUM(i.taxable_amount) as total_business
        FROM vms_customers c
        JOIN vms_utilization_reports ur ON (c.id = ur.decorator_id OR c.id = ur.caterer_id)
        JOIN vms_invoices i ON ur.booking_id = i.booking_id
        $where GROUP BY c.id ORDER BY total_events DESC";
$res = $conn->query($sql);
?>