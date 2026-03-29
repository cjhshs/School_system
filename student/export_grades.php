<?php
require_once '../config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

$student_id = $_SESSION['student_id'];
$student = $conn->query("SELECT s.*, c.code as course_code, c.name as course_name FROM students s LEFT JOIN courses c ON s.course_id = c.id WHERE s.id = $student_id")->fetch_assoc();

$passing_grade = 75;
$dept_result = $conn->query("SELECT passing_grade FROM departments LIMIT 1");
if ($dept_result && $dept_result->num_rows > 0) {
    $passing_grade = $dept_result->fetch_assoc()['passing_grade'];
}

$grades = $conn->query("
    SELECT sub.subject_code, sub.description, sub.units, sub.semester, sub.year_level,
           g.prelim, g.midterm, g.final_exam, g.final_grade, g.remarks, g.grade_status, g.approved_at
    FROM student_subjects ss
    JOIN subjects sub ON ss.subject_id = sub.id
    LEFT JOIN grades g ON sub.id = g.subject_id AND g.student_id = $student_id
    WHERE ss.student_id = $student_id
    ORDER BY sub.semester, sub.subject_code
");

$grades_array = [];
$total_units = 0;
$passed_count = 0;
$failed_count = 0;
$pending_count = 0;

if ($grades) {
    while ($row = $grades->fetch_assoc()) {
        $grades_array[] = $row;
        if ($row['units']) $total_units += $row['units'];
        if ($row['remarks'] == 'Passed') $passed_count++;
        elseif ($row['remarks'] == 'Failed') $failed_count++;
        if ($row['status'] == 'Submitted') $pending_count++;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Grades - <?php echo htmlspecialchars($student['student_number']); ?></title>
    <style>
        @page { size: A4; margin: 15mm; }
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            font-size: 11px; 
            margin: 0; 
            padding: 20px;
        }
        .document { max-width: 210mm; margin: 0 auto; }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 3px double #333; 
            padding-bottom: 15px;
        }
        .header h1 { margin: 0; font-size: 22px; color: #333; letter-spacing: 2px; }
        .header h2 { margin: 5px 0 0 0; font-size: 14px; font-weight: normal; color: #666; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 8px 10px; border: 1px solid #ddd; }
        .info-table td:first-child { width: 30%; font-weight: bold; background: #f5f5f5; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 8px; }
        th { background-color: #2c3e50 !important; color: white; text-align: left; }
        .text-center { text-align: center; }
        .passed { color: #27ae60; font-weight: bold; }
        .failed { color: #e74c3c; font-weight: bold; }
        .pending { color: #f39c12; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #888; }
        .stats { display: flex; justify-content: center; gap: 30px; margin: 20px 0; }
        .stat-box { text-align: center; padding: 15px 25px; border: 2px solid #333; border-radius: 5px; }
        .stat-box h3 { margin: 0; font-size: 24px; }
        .stat-box p { margin: 5px 0 0 0; font-size: 11px; }
        .watermark {
            position: fixed; top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px; color: rgba(0,0,0,0.03);
            pointer-events: none; z-index: -1;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="document">
        <div class="watermark">OFFICIAL RECORD</div>
        
        <div class="header">
            <h1>OFFICIAL GRADE REPORT</h1>
            <h2>Enrollment Management System</h2>
        </div>
        
        <table class="info-table">
            <tr>
                <td>Student Name:</td>
                <td><?php echo htmlspecialchars($student['lastname'] . ', ' . $student['firstname']); ?></td>
            </tr>
            <tr>
                <td>Student Number:</td>
                <td><?php echo htmlspecialchars($student['student_number']); ?></td>
            </tr>
            <tr>
                <td>Course:</td>
                <td><?php echo htmlspecialchars($student['course_code']); ?></td>
            </tr>
            <tr>
                <td>Year Level:</td>
                <td><?php echo htmlspecialchars($student['year_level']); ?> Year</td>
            </tr>
            <tr>
                <td>Semester:</td>
                <td>Academic Year <?php echo date('Y'); ?></td>
            </tr>
            <tr>
                <td>Date Generated:</td>
                <td><?php echo date('F d, Y'); ?></td>
            </tr>
        </table>
        
        <div class="stats">
            <div class="stat-box">
                <h3><?php echo count($grades_array); ?></h3>
                <p>Total Subjects</p>
            </div>
            <div class="stat-box" style="border-color: #27ae60;">
                <h3 style="color: #27ae60;"><?php echo $passed_count; ?></h3>
                <p>Passed</p>
            </div>
            <div class="stat-box" style="border-color: #e74c3c;">
                <h3 style="color: #e74c3c;"><?php echo $failed_count; ?></h3>
                <p>Failed</p>
            </div>
            <div class="stat-box" style="border-color: #f39c12;">
                <h3 style="color: #f39c12;"><?php echo $pending_count; ?></h3>
                <p>Pending</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $total_units; ?></h3>
                <p>Total Units</p>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%">#</th>
                    <th style="width: 12%">Subject</th>
                    <th>Description</th>
                    <th class="text-center" style="width: 7%">Units</th>
                    <th class="text-center">Prelim</th>
                    <th class="text-center">Midterm</th>
                    <th class="text-center">Final</th>
                    <th class="text-center">Average</th>
                    <th class="text-center">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($grades_array as $grade): ?>
                <?php
                    $remark_class = '';
                    if ($grade['status'] == 'Approved') {
                        $remark_class = $grade['remarks'] == 'Passed' ? 'passed' : 'failed';
                    } elseif ($grade['status'] == 'Submitted') {
                        $remark_class = 'pending';
                    }
                ?>
                <tr>
                    <td class="text-center"><?php echo $i++; ?></td>
                    <td><strong><?php echo htmlspecialchars($grade['subject_code']); ?></strong></td>
                    <td><?php echo htmlspecialchars($grade['description']); ?></td>
                    <td class="text-center"><?php echo $grade['units']; ?></td>
                    <td class="text-center"><?php echo $grade['prelim'] !== null ? number_format($grade['prelim'], 2) : '-'; ?></td>
                    <td class="text-center"><?php echo $grade['midterm'] !== null ? number_format($grade['midterm'], 2) : '-'; ?></td>
                    <td class="text-center"><?php echo $grade['final_exam'] !== null ? number_format($grade['final_exam'], 2) : '-'; ?></td>
                    <td class="text-center"><strong><?php echo $grade['final_grade'] !== null ? number_format($grade['final_grade'], 2) : '-'; ?></strong></td>
                    <td class="text-center <?php echo $remark_class; ?>">
                        <?php
                        if ($grade['status'] == 'Approved') {
                            echo $grade['remarks'];
                        } elseif ($grade['status'] == 'Submitted') {
                            echo 'PENDING';
                        } elseif ($grade['grade_id'] = $grade['final_grade']) {
                            echo 'DRAFT';
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($grades_array)): ?>
                <tr><td colspan="9" class="text-center">No grades available</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 30px; font-size: 10px; color: #666;">
            <p><strong>Legend:</strong> Passing Grade = <?php echo $passing_grade; ?>%</p>
            <p>Status: <span class="passed">Approved</span> = Official grade | <span class="pending">Pending</span> = Awaiting approval | Draft = Not yet submitted</p>
        </div>
        
        <div class="footer">
            <p><strong>This is an official document of the CJLG University.</strong></p>
            <p>Generated on: <?php echo date('F d, Y H:i:s'); ?></p>
            <p class="no-print" style="margin-top: 15px;">
                <button onclick="window.print()" class="btn btn-primary" style="padding: 10px 30px; cursor: pointer;">
                    <i class="fas fa-print"></i> Print Grades
                </button>
            </p>
        </div>
    </div>
</body>
</html>
