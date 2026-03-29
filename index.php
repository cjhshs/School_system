<?php
require_once 'config.php';
include 'header.php';

$total_students = $conn->query("SELECT COUNT(*) as cnt FROM students WHERE enrollment_status IN ('Enrolled', 'Pending')")->fetch_assoc()['cnt'];
$total_courses = $conn->query("SELECT COUNT(DISTINCT code) as cnt FROM courses WHERE code != 'CODE'")->fetch_assoc()['cnt'];
$total_enrolled = $conn->query("SELECT COUNT(*) as cnt FROM enrollments WHERE status = 'Confirmed'")->fetch_assoc()['cnt'];

// Get courses for display
$courses_result = $conn->query("SELECT DISTINCT code, name, major FROM courses WHERE code != 'CODE' ORDER BY code, name LIMIT 12");
$course_icons = ['fa-laptop-code', 'fa-chart-line', 'fa-cogs', 'fa-palette', 'fa-gavel', 'fa-heartbeat', 'fa-calculator', 'fa-book', 'fa-globe', 'fa-music', 'fa-camera', 'fa-flask'];
?>

<!-- HERO SECTION -->
<section class="hero-section">
    <div class="hero-bg-pattern"></div>
    <div class="hero-grid-overlay"></div>
    <div class="container">
        <div class="hero-content">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-text">
                        <h1>Your Future Starts <span class="highlight">Here</span></h1>
                        <p>Transform your academic journey with our modern enrollment system. Streamlined processes, real-time updates, and seamless experience designed for the next generation of students.</p>
                        <div class="hero-buttons">
                            <a href="student/register.php" class="btn-hero-primary">
                                <i class="fas fa-rocket"></i> Get Started
                            </a>
                            <a href="#portals" class="btn-hero-outline">
                                <i class="fas fa-th-large"></i> Explore Portals
                            </a>
                        </div>
                        <div class="hero-badges">
                            <span class="hero-badge"><i class="fas fa-check-circle"></i> Easy Enrollment</span>
                            <span class="hero-badge"><i class="fas fa-check-circle"></i> 24/7 Access</span>
                            <span class="hero-badge"><i class="fas fa-check-circle"></i> Secure & Fast</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image-section">
                        <div class="hero-image-container">
                            <div class="hero-image-backdrop"></div>
                            <img src="images/school.png" alt="Student" class="hero-image" onerror="this.style.display='none'">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- STATS SECTION -->
<section class="stats-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="stats-card fade-in-up">
                    <div class="stats-icon"><i class="fas fa-user-graduate"></i></div>
                    <h3><?php echo number_format($total_students); ?>+</h3>
                    <p>Active Students</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card fade-in-up" style="animation-delay: 0.1s;">
                    <div class="stats-icon"><i class="fas fa-clipboard-check"></i></div>
                    <h3><?php echo number_format($total_enrolled); ?>+</h3>
                    <p>Enrolled This Semester</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card fade-in-up" style="animation-delay: 0.2s;">
                    <div class="stats-icon"><i class="fas fa-book-open"></i></div>
                    <h3><?php echo number_format($total_courses); ?>+</h3>
                    <p>Courses Available</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PORTALS SECTION -->
<section class="portals-section" id="portals">
    <div class="container">
        <div class="section-header fade-in-up">
            <span class="section-label">PORTAL ACCESS</span>
            <h2 class="section-title">Choose Your Portal</h2>
            <p class="section-subtitle">Access different portals based on your role. Each portal is designed to meet your specific needs.</p>
        </div>
        <div class="row justify-content-center g-4">
            <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                <div class="portal-card student">
                    <div class="portal-icon"><i class="fas fa-user-graduate"></i></div>
                    <h4>Student</h4>
                    <a href="student/login.php" class="btn-portal">Login</a>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                <div class="portal-card registrar">
                    <div class="portal-icon"><i class="fas fa-user-shield"></i></div>
                    <h4>Registrar</h4>
                    <a href="registrar/login.php" class="btn-portal">Login</a>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                <div class="portal-card finance">
                    <div class="portal-icon"><i class="fas fa-coins"></i></div>
                    <h4>Finance</h4>
                    <a href="finance/login.php" class="btn-portal">Login</a>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                <div class="portal-card cashier">
                    <div class="portal-icon"><i class="fas fa-cash-register"></i></div>
                    <h4>Cashier</h4>
                    <a href="cashier/login.php" class="btn-portal">Login</a>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                <div class="portal-card teacher">
                    <div class="portal-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                    <h4>Teacher</h4>
                    <a href="teacher/login.php" class="btn-portal">Login</a>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                <div class="portal-card dean">
                    <div class="portal-icon"><i class="fas fa-user-tie"></i></div>
                    <h4>Dean</h4>
                    <a href="dean/login.php" class="btn-portal">Login</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES SECTION -->
<section class="features-section">
    <div class="container">
        <div class="section-header fade-in-up">
            <span class="section-label">WHY CHOOSE US</span>
            <h2 class="section-title">Powerful Features</h2>
            <p class="section-subtitle">Everything you need for a seamless academic experience</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card fade-in-up">
                    <div class="feature-icon"><i class="fas fa-bolt"></i></div>
                    <h5>Lightning Fast</h5>
                    <p>Quick enrollment process with real-time validation and instant confirmation</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card fade-in-up" style="animation-delay: 0.1s;">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <h5>Secure Data</h5>
                    <p>Your academic records are protected with enterprise-grade security</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card fade-in-up" style="animation-delay: 0.2s;">
                    <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                    <h5>Real-time Updates</h5>
                    <p>Track your grades and enrollment status as they happen</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- COURSES SECTION -->
<section class="courses-section" id="courses">
    <div class="container">
        <div class="section-header fade-in-up">
            <span class="section-label">ACADEMICS</span>
            <h2 class="section-title">Available Courses</h2>
            <p class="section-subtitle">Explore our diverse range of programs designed to shape future leaders</p>
        </div>
        <div class="courses-grid fade-in-up">
            <?php if ($courses_result && $courses_result->num_rows > 0): ?>
                <?php $i = 0; while ($row = $courses_result->fetch_assoc()): ?>
                    <div class="course-card">
                        <div class="course-icon"><i class="fas <?php echo $course_icons[$i % count($course_icons)]; ?>"></i></div>
                        <div class="course-info">
                            <h5><?php echo htmlspecialchars($row['code']); ?></h5>
                            <p><?php echo htmlspecialchars($row['name']); ?><?php echo !empty($row['major']) ? ' - ' . htmlspecialchars($row['major']) : ''; ?></p>
                        </div>
                        <?php if (!empty($row['major'])): ?>
                            <span class="course-badge">Major</span>
                        <?php endif; ?>
                    </div>
                <?php $i++; endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center"><p class="text-muted">No courses available at the moment.</p></div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-5 fade-in-up">
            <a href="student/register.php" class="btn-hero-primary">
                <i class="fas fa-graduation-cap"></i> View All Courses & Enroll
            </a>
        </div>
    </div>
</section>

<!-- ANNOUNCEMENTS SECTION -->
<section class="announcements-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="announcement-card fade-in-up">
                    <div class="announcement-header">
                        <h4><i class="fas fa-bullhorn me-2"></i>Latest Announcements</h4>
                    </div>
                    <div class="announcement-body">
                        <div class="announcement-item">
                            <h6><i class="fas fa-calendar-check text-success"></i> Enrollment Now Open!</h6>
                            <p>Spring Semester 2026 enrollment is now ongoing. Secure your slot today!</p>
                            <span class="announcement-date"><i class="far fa-clock me-1"></i> Just now</span>
                        </div>
                        <div class="announcement-item">
                            <h6><i class="fas fa-calendar-check text-warning"></i> Grade Submission Deadline</h6>
                            <p>Teachers are reminded to submit grades by the end of the month.</p>
                            <span class="announcement-date"><i class="far fa-clock me-1"></i> 2 days ago</span>
                        </div>
                        <div class="announcement-item">
                            <h6><i class="fas fa-info-circle text-primary"></i> Finance Reminder</h6>
                            <p>Students with outstanding balances must settle before final exams.</p>
                            <span class="announcement-date"><i class="far fa-clock me-1"></i> 5 days ago</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="announcement-card fade-in-up" style="animation-delay: 0.1s;">
                    <div class="announcement-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h4><i class="fas fa-star me-2"></i>Quick Stats</h4>
                    </div>
                    <div class="announcement-body" style="padding: 20px;">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="quick-stat-card" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));">
                                    <i class="fas fa-user-graduate"></i>
                                    <h3><?php echo number_format($total_students); ?></h3>
                                    <p>Students</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="quick-stat-card" style="background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(32, 201, 151, 0.1));">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                    <h3>50+</h3>
                                    <p>Teachers</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="quick-stat-card" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(253, 126, 20, 0.1));">
                                    <i class="fas fa-book-open"></i>
                                    <h3><?php echo number_format($total_courses); ?></h3>
                                    <p>Courses</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="quick-stat-card" style="background: linear-gradient(135deg, rgba(23, 162, 184, 0.1), rgba(102, 16, 242, 0.1));">
                                    <i class="fas fa-building"></i>
                                    <h3>10+</h3>
                                    <p>Departments</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA SECTION -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content fade-in-up">
            <h2>Ready to Begin Your Journey?</h2>
            <p>Join thousands of students who have started their academic transformation with us.</p>
            <a href="student/register.php" class="btn-hero-primary">
                <i class="fas fa-rocket"></i> Enroll Now
            </a>
        </div>
    </div>
</section>

<script src="js/home.js"></script>

<?php include 'footer.php'; ?>
