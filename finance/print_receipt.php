<?php
require_once '../config.php';

if (!isset($_GET['id'])) {
    header('Location: dashboard.php?page=students');
    exit;
}

$student_id = $_GET['id'];
$student = $conn->query("SELECT * FROM students WHERE id = $student_id")->fetch_assoc();

if (!$student) {
    echo '<div class="alert alert-danger">Student not found.</div>';
    exit;
}

// Get fees
$fees = $conn->query("
    SELECT sf.*, ft.name as fee_name 
    FROM student_fees sf 
    LEFT JOIN fee_types ft ON sf.fee_type_id = ft.id 
    WHERE sf.student_id = $student_id
");

// Get payments
$payments = $conn->query("
    SELECT * FROM payments 
    WHERE student_id = $student_id 
    ORDER BY payment_date DESC
");

// Calculate totals
$fees_data = [];
$total_amount = 0;
if ($fees) {
    while ($f = $fees->fetch_assoc()) {
        $fees_data[] = $f;
        $total_amount += $f['amount'];
    }
}

$total_paid = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE student_id = $student_id")->fetch_assoc()['total'];
$balance = $total_amount - $total_paid;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Statement of Account - <?php echo htmlspecialchars($student['student_number']); ?></title>
    <style>
        body { font-family: 'Courier New', monospace; font-size: 12px; max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px dashed #000; padding-bottom: 15px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header h2 { margin: 5px 0; font-size: 14px; font-weight: normal; }
        .info { margin-bottom: 20px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 5px; }
        th { background-color: #f0f0f0; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; background-color: #e0e0e0; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
        .signature { margin-top: 50px; display: flex; justify-content: space-between; }
        .signature-box { text-align: center; width: 200px; }
        .signature-line { border-top: 1px solid #000; margin-top: 30px; padding-top: 5px; }
        @media print {
            .no-print { display: none; }
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
            <span><strong>Student Number:</strong> <?php echo htmlspecialchars($student['student_number']); ?></span>
            <span><strong>Date:</strong> <?php echo date('F d, Y'); ?></span>
        </div>
        <div class="info-row">
            <span><strong>Name:</strong> <?php echo htmlspecialchars($student['lastname'] . ', ' . $student['firstname']); ?></span>
            <span><strong>Course:</strong> <?php echo htmlspecialchars($student['course_code'] ?? 'N/A'); ?></span>
        </div>
        <div class="info-row">
            <span><strong>Year Level:</strong> <?php echo htmlspecialchars($student['year_level']); ?></span>
            <span><strong>Semester:</strong> <?php echo date('Y'); ?></span>
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
                <td colspan="2" class="text-center">No fees assessed</td>
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
                <th>Reference</th>
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
                <td><?php echo htmlspecialchars($p['reference_number'] ?? '-'); ?></td>
                <td class="text-right">₱<?php echo number_format($p['amount'], 2); ?></td>
            </tr>
            <?php 
                endwhile;
            }
            if (!$payments || $payments->num_rows == 0): 
            ?>
            <tr>
                <td colspan="4" class="text-center">No payments recorded</td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td colspan="3">TOTAL PAYMENTS:</td>
                <td class="text-right">₱<?php echo number_format($total_paid, 2); ?></td>
            </tr>
        </tbody>
    </table>
    
    <div style="background-color: #f0f0f0; padding: 15px; margin-top: 20px; border: 2px solid #000;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; text-align: right;"><strong>BALANCE DUE:</strong></td>
                <td style="border: none; text-align: right; font-size: 20px;">
                    <strong>₱<?php echo number_format($balance, 2); ?></strong>
                </td>
            </tr>
        </table>
    </div>
    
    <?php if ($balance == 0): ?>
    <div style="text-align: center; margin-top: 15px; color: green; font-weight: bold; font-size: 16px;">
        *** FULLY PAID ***
    </div>
    <?php endif; ?>
    
    <div class="signature">
        <div class="signature-box">
            <div class="signature-line">Student Signature</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Finance Officer</div>
        </div>
    </div>
    
    <div class="footer">
        <p>This is an official document of the CJLG University.</p>
        <p>Generated on: <?php echo date('F d, Y H:i:s'); ?></p>
    </div>
    
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>
</body>
</html>
