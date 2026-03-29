<?php
require_once '../config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

$student_id = $_SESSION['student_id'];
$student = $conn->query("SELECT s.*, c.code as course_code FROM students s LEFT JOIN courses c ON s.course_id = c.id WHERE s.id = $student_id")->fetch_assoc();

if (!$student) {
    echo '<div class="alert alert-danger">Student not found.</div>';
    exit;
}

$course_code = $student['course_code'] ?? '';
$year_level = $student['year_level'] ?? 1;

$tuition = $conn->query("SELECT * FROM tuition_fees WHERE course_code = '$course_code' AND year_level = $year_level LIMIT 1")->fetch_assoc();

$course_fees = $conn->query("SELECT * FROM course_fees WHERE course_code = '$course_code'");

$payments = $conn->query("
    SELECT * FROM payments 
    WHERE student_id = $student_id 
    ORDER BY payment_date DESC
");

$fees_data = [];
$total_amount = 0;

if ($tuition) {
    $fees_data[] = [
        'fee_name' => 'Tuition Fee',
        'amount' => $tuition['tuition_amount'] ?? 0
    ];
    $total_amount += $tuition['tuition_amount'] ?? 0;
    
    $fees_data[] = [
        'fee_name' => 'Miscellaneous Fee',
        'amount' => $tuition['miscellaneous_amount'] ?? 0
    ];
    $total_amount += $tuition['miscellaneous_amount'] ?? 0;
    
    $fees_data[] = [
        'fee_name' => 'Laboratory Fee',
        'amount' => $tuition['laboratory_amount'] ?? 0
    ];
    $total_amount += $tuition['laboratory_amount'] ?? 0;
    
    $fees_data[] = [
        'fee_name' => 'Other Fees',
        'amount' => $tuition['other_fees'] ?? 0
    ];
    $total_amount += $tuition['other_fees'] ?? 0;
}

if ($course_fees) {
    while ($cf = $course_fees->fetch_assoc()) {
        $fees_data[] = [
            'fee_name' => $cf['fee_name'],
            'amount' => $cf['amount']
        ];
        $total_amount += $cf['amount'];
    }
}

$total_paid = $conn->query("SELECT COALESCE(SUM(payment_amount), 0) as total FROM payments WHERE student_id = $student_id")->fetch_assoc()['total'];
$balance = $total_amount - $total_paid;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Statement of Account - <?php echo htmlspecialchars($student['student_number']); ?></title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 12px; max-width: 800px; margin: 0 auto; padding: 20px; background: #fff; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #2563eb; padding-bottom: 15px; }
        .header h1 { margin: 0; font-size: 22px; color: #1e293b; }
        .header h2 { margin: 5px 0 0; font-size: 14px; font-weight: normal; color: #64748b; }
        .school-info { margin-bottom: 20px; text-align: center; }
        .school-info h3 { margin: 0; font-size: 16px; color: #1e293b; }
        .info { margin-bottom: 20px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 8px; padding: 8px; background: #f8fafc; border-radius: 4px; }
        .info-label { font-weight: 600; color: #475569; }
        .info-value { color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #e2e8f0; padding: 10px; }
        th { background-color: #2563eb; color: white; text-align: left; font-weight: 600; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; background-color: #f1f5f9; }
        .balance-box { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; margin-top: 20px; }
        .balance-box .label { font-size: 14px; opacity: 0.9; }
        .balance-box .amount { font-size: 32px; font-weight: bold; margin-top: 5px; }
        .balance-box.paid { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); }
        .footer { margin-top: 40px; text-align: center; font-size: 11px; color: #94a3b8; }
        .signature { margin-top: 50px; display: flex; justify-content: space-between; padding: 0 40px; }
        .signature-box { text-align: center; width: 200px; }
        .signature-line { border-top: 1px solid #1e293b; margin-top: 40px; padding-top: 8px; font-size: 12px; color: #475569; }
        .btn-print { display: inline-block; padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; margin-right: 10px; }
        .btn-print:hover { background: #1d4ed8; }
        .btn-close { display: inline-block; padding: 10px 20px; background: #64748b; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .btn-close:hover { background: #475569; }
        .actions { text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #e2e8f0; }
        @media print {
            .actions { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ENROLLMENT SYSTEM</h1>
        <h2>OFFICIAL STATEMENT OF ACCOUNT</h2>
    </div>
    
    <div class="info">
        <div class="info-row">
            <span><span class="info-label">Student Number:</span> <span class="info-value"><?php echo htmlspecialchars($student['student_number']); ?></span></span>
            <span><span class="info-label">Date:</span> <span class="info-value"><?php echo date('F d, Y'); ?></span></span>
        </div>
        <div class="info-row">
            <span><span class="info-label">Name:</span> <span class="info-value"><?php echo htmlspecialchars($student['lastname'] . ', ' . $student['firstname']); ?></span></span>
            <span><span class="info-label">Course:</span> <span class="info-value"><?php echo htmlspecialchars($student['course_code'] ?? 'N/A'); ?></span></span>
        </div>
        <div class="info-row">
            <span><span class="info-label">Year Level:</span> <span class="info-value"><?php echo htmlspecialchars($student['year_level']); ?></span></span>
            <span><span class="info-label">Semester:</span> <span class="info-value"><?php echo date('Y'); ?></span></span>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Fee Description</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($fees_data as $fee): ?>
            <tr>
                <td><?php echo htmlspecialchars($fee['fee_name']); ?></td>
                <td class="text-right">₱<?php echo number_format($fee['amount'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($fees_data)): ?>
            <tr>
                <td colspan="2" class="text-center" style="color: #94a3b8;">No fees assessed</td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td>TOTAL FEES:</td>
                <td class="text-right">₱<?php echo number_format($total_amount, 2); ?></td>
            </tr>
        </tbody>
    </table>
    
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>OR Number</th>
                <th>Method</th>
                <th class="text-right">Amount Paid</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($payments) {
                while ($p = $payments->fetch_assoc()): 
            ?>
            <tr>
                <td><?php echo date('M d, Y', strtotime($p['payment_date'])); ?></td>
                <td><?php echo htmlspecialchars($p['or_number'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($p['payment_method'] ?? '-'); ?></td>
                <td class="text-right">₱<?php echo number_format($p['payment_amount'], 2); ?></td>
            </tr>
            <?php 
                endwhile;
            }
            if (!$payments || $payments->num_rows == 0): 
            ?>
            <tr>
                <td colspan="4" class="text-center" style="color: #94a3b8;">No payments recorded</td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td colspan="3">TOTAL PAYMENTS:</td>
                <td class="text-right">₱<?php echo number_format($total_paid, 2); ?></td>
            </tr>
        </tbody>
    </table>
    
    <div class="balance-box <?php echo $balance == 0 ? 'paid' : ''; ?>">
        <div class="label"><?php echo $balance == 0 ? 'FULLY PAID' : 'BALANCE DUE'; ?></div>
        <div class="amount">₱<?php echo number_format($balance, 2); ?></div>
    </div>
    
    <div class="signature">
        <div class="signature-box">
            <div class="signature-line">Student Signature</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Finance Officer</div>
        </div>
    </div>
    
    <div class="footer">
        <p>This is an official document of the Enrollment System.</p>
        <p>Generated on: <?php echo date('F d, Y H:i:s'); ?></p>
    </div>
    
    <div class="actions">
        <button class="btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Print
        </button>
        <button class="btn-close" onclick="window.close()">
            <i class="fas fa-times"></i> Close
        </button>
    </div>
</body>
</html>
