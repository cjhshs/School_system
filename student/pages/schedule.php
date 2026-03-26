<?php
require_once '../config.php';

$student_id = $_SESSION['student_id'];
$student = $conn->query("SELECT s.*, c.name as course_name FROM students s LEFT JOIN courses c ON s.course_code = c.code WHERE s.id = $student_id")->fetch_assoc();
$current_school_year = date('Y') . '-' . (date('Y') + 1);

$subjects = $conn->query("SELECT * FROM subjects WHERE course_code = '" . $student['course_code'] . "' AND school_year = '$current_school_year' ORDER BY semester, subject_code");

$total_units = 0;
$subjects_array = [];
if ($subjects) {
    while ($row = $subjects->fetch_assoc()) {
        $subjects_array[] = $row;
        $total_units += $row['units'];
    }
}
?>

<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-calendar"></i> My Schedule</h1>
        <p>Academic Year <?php echo $current_school_year; ?></p>
    </div>
    <div class="page-header-right">
        <a href="print_schedule.php" target="_blank" class="btn btn-outline-secondary">
            <i class="fas fa-print me-2"></i>Print
        </a>
        <a href="export_schedule.php" target="_blank" class="btn btn-primary">
            <i class="fas fa-download me-2"></i>Download PDF
        </a>
    </div>
</div>

<!-- Student Info Banner -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-2 col-6 mb-2">
                <div class="stat-label">Student</div>
                <div class="fw-semibold"><?php echo htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?></div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="stat-label">Course</div>
                <div class="fw-semibold"><?php echo htmlspecialchars($student['course_code']); ?></div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="stat-label">Year Level</div>
                <div class="fw-semibold"><?php echo htmlspecialchars($student['year_level']); ?></div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="stat-label">Semester</div>
                <div class="fw-semibold">1st Semester</div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="stat-label">Total Units</div>
                <div class="fw-semibold"><?php echo $total_units; ?></div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="stat-label">Total Subjects</div>
                <div class="fw-semibold"><?php echo count($subjects_array); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Weekly Timetable -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-calendar-week"></i> Weekly Timetable</h5>
            </div>
            <div class="card-body p-0">
                <?php if (count($subjects_array) > 0): ?>
                <div class="table-responsive">
                    <table class="schedule-timetable">
                        <thead>
                            <tr>
                                <th class="time-col">Time</th>
                                <th>Monday</th>
                                <th>Tuesday</th>
                                <th>Wednesday</th>
                                <th>Thursday</th>
                                <th>Friday</th>
                                <th>Saturday</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $time_slots = [
                                '7:00-8:00' => ['7:00', '8:00'],
                                '8:00-9:00' => ['8:00', '9:00'],
                                '9:00-10:00' => ['9:00', '10:00'],
                                '10:00-11:00' => ['10:00', '11:00'],
                                '11:00-12:00' => ['11:00', '12:00'],
                                '12:00-13:00' => ['12:00', '13:00'],
                                '13:00-14:00' => ['13:00', '14:00'],
                                '14:00-15:00' => ['14:00', '15:00'],
                                '15:00-16:00' => ['15:00', '16:00'],
                                '16:00-17:00' => ['16:00', '17:00'],
                                '17:00-18:00' => ['17:00', '18:00'],
                            ];
                            
                            $days = ['M', 'T', 'W', 'Th', 'F', 'S'];
                            $day_names = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            
                            // Create schedule grid
                            $schedule_grid = [];
                            foreach ($time_slots as $slot => $times) {
                                $schedule_grid[$slot] = ['', '', '', '', '', ''];
                            }
                            
                            foreach ($subjects_array as $subj) {
                                if (empty($subj['schedule'])) continue;
                                
                                $sched = strtoupper($subj['schedule']);
                                foreach ($days as $idx => $day) {
                                    if (strpos($sched, $day) !== false) {
                                        // Parse time from schedule
                                        if (preg_match('/(\d{1,2}:\d{2})/', $sched, $matches)) {
                                            $time = intval(substr($matches[1], 0, 2));
                                            foreach ($time_slots as $slot => $times) {
                                                $start = intval(explode(':', $times[0])[0]);
                                                $end = intval(explode(':', $times[1])[0]);
                                                if ($time >= $start && $time < $end) {
                                                    $schedule_grid[$slot][$idx] = [
                                                        'code' => $subj['subject_code'],
                                                        'desc' => $subj['description'] ?? '',
                                                        'room' => $subj['room'] ?? 'TBA'
                                                    ];
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            
                            foreach ($schedule_slots ?? $time_slots as $slot => $times): ?>
                            <tr>
                                <td class="time-slot"><?php echo $slot; ?></td>
                                <?php 
                                $grid_slot = $slot;
                                foreach ($day_names as $idx => $day_name): 
                                    $item = $schedule_grid[$grid_slot][$idx] ?? '';
                                ?>
                                <td class="<?php echo $item ? 'table-primary' : ''; ?>">
                                    <?php if ($item): ?>
                                        <div class="schedule-item">
                                            <div class="subject-code"><?php echo htmlspecialchars($item['code']); ?></div>
                                            <div class="subject-name"><?php echo htmlspecialchars($item['desc']); ?></div>
                                            <div class="room"><i class="fas fa-door-open me-1"></i><?php echo htmlspecialchars($item['room']); ?></div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="card-body">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-calendar-times"></i></div>
                        <h4>No Schedule Available</h4>
                        <p>Please contact the registrar to set up your class schedule.</p>
    </div>
</div>

<script>
document.querySelectorAll('.search-input').forEach(function(input) {
    input.addEventListener('input', function() {
        var tableId = this.getAttribute('data-table');
        var table = document.getElementById(tableId);
        if (!table) return;
        var filter = this.value.toLowerCase();
        var rows = table.querySelectorAll('tbody tr');
        rows.forEach(function(row) {
            var text = row.textContent.toLowerCase();
            row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
        });
    });
});

function clearSearch(btn) {
    var input = btn.parentElement.querySelector('.search-input');
    input.value = '';
    input.dispatchEvent(new Event('input'));
}
</script>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Subject List -->
    <div class="col-lg-4">
        <div class="card">
            <div class="table-header">
                <h5 class="table-title"><i class="fas fa-book"></i> Enrolled Subjects</h5>
            </div>
            <div class="search-wrapper mb-3">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" data-table="subjectsTable" placeholder="Search subjects...">
                <button type="button" class="search-clear" onclick="clearSearch(this)"><i class="fas fa-times"></i></button>
            </div>
            <div class="table-responsive">
                <table class="table" id="subjectsTable">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Units</th>
                            <th>Room</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($subjects_array) > 0): foreach ($subjects_array as $subj): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?php echo htmlspecialchars($subj['subject_code']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($subj['description'] ?? ''); ?></small>
                            </td>
                            <td><span class="badge badge-info"><?php echo $subj['units']; ?></span></td>
                            <td><?php echo $subj['room'] ?: '<span class="text-muted">-</span>'; ?></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">No subjects enrolled</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-secondary">
                            <td class="fw-bold">Total</td>
                            <td class="fw-bold"><?php echo $total_units; ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Class Info -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title"><i class="fas fa-info-circle"></i> Class Information</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">School Year</span>
                    <span class="fw-semibold"><?php echo $current_school_year; ?></span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Semester</span>
                    <span class="fw-semibold">1st Semester</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Course</span>
                    <span class="fw-semibold"><?php echo htmlspecialchars($student['course_code'] ?? 'N/A'); ?></span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Year Level</span>
                    <span class="fw-semibold"><?php echo htmlspecialchars($student['year_level'] ?? 'N/A'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
