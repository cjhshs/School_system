<?php
require_once '../config.php';

// Set page-specific CSS
$extra_css = '<link rel="stylesheet" href="../css/register.css">';

// Set page-specific JS
$extra_js = '<script src="../js/register.js"></script>';

// Fetch dropdown data
$religions = $conn->query("SELECT DISTINCT religion FROM religions WHERE religion IS NOT NULL ORDER BY religion");
$ethnicities = $conn->query("SELECT DISTINCT ethnicname FROM ethnicity ORDER BY ethnicname");
$dialects = $conn->query("SELECT DISTINCT dialects FROM dialects ORDER BY dialects");
$regions = $conn->query("SELECT DISTINCT region_name FROM regions ORDER BY region_name");
$courses = $conn->query("SELECT DISTINCT code, name FROM courses WHERE code != 'code' ORDER BY code");

// Generate school year options
$current_year = date('Y');
$school_years = [];
for ($i = 0; $i < 5; $i++) {
    $year = $current_year + $i;
    $school_years[] = ($year - 1) . '-' . $year;
}

// Create uploads directory if not exists
$upload_dir = '../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

include '../header.php';
?>

<style>
.drop-zone {
    width: 180px;
    height: 180px;
    border: 3px dashed #ced4da;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
    background: #f8f9fa;
    position: relative;
    overflow: hidden;
}

.drop-zone:hover {
    border-color: #667eea;
    background: #e9ecef;
}

.drop-zone.dragover {
    border-color: #667eea;
    background: #e9ecef;
    transform: scale(1.05);
}

.drop-zone img {
    display: none;
}

.drop-zone.has-image {
    border-style: solid;
}

#dropZoneText {
    text-align: center;
    color: #6c757d;
}

#dropZoneText i {
    display: block;
    margin-bottom: 10px;
}

#profile_picture {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    opacity: 0;
    cursor: pointer;
}
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4><i class="fas fa-user-plus me-2"></i>Student Enrollment Form</h4>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle me-2"></i>Enrollment Successful!</h5>
                        <p class="mb-1"><strong>Student Number:</strong> <?php echo htmlspecialchars($_GET['student_no']); ?></p>
                        <?php if(isset($_GET['default_pass'])): ?>
                        <p class="mb-1"><strong>Default Password:</strong> <code><?php echo htmlspecialchars($_GET['default_pass']); ?></code></p>
                        <div class="alert alert-warning mt-2 mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>Please save your password! You can change it in your student portal.
                        </div>
                        <a href="login.php" class="btn btn-primary mt-2"><i class="fas fa-sign-in-alt me-2"></i>Go to Student Login</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <form action="process-enrollment.php" method="POST" id="enrollmentForm" enctype="multipart/form-data">
                    
                    <!-- Profile Picture Section -->
                    <div class="row mb-4">
                        <div class="col-12 text-center">
                            <div class="profile-picture-wrapper d-inline-block">
                                <div class="drop-zone" id="dropZone">
                                    <div id="dropZoneText">
                                        <i class="fas fa-camera fa-2x"></i>
                                        <small>Upload Photo</small>
                                        <small class="d-block">Click or drag</small>
                                    </div>
                                    <img id="previewImage" src="" alt="Profile">
                                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">Upload a photo (max 5MB)</small>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="firstname" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="firstname" name="firstname" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="middlename" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" id="middlename" name="middlename">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="lastname" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="lastname" name="lastname" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="suffix" class="form-label">Suffix</label>
                                    <input type="text" class="form-control" id="suffix" name="suffix" placeholder="Jr., Sr., III">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="birthdate" class="form-label">Birth Date *</label>
                                    <input type="date" class="form-control" id="birthdate" name="birthdate" onchange="calculateAge()" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="age" class="form-label">Age</label>
                                    <input type="number" class="form-control" id="age" name="age" readonly>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="gender" class="form-label">Gender *</label>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="civil_status" class="form-label">Civil Status *</label>
                                    <select class="form-select" id="civil_status" name="civil_status" required>
                                        <option value="">Select Status</option>
                                        <option value="Single">Single</option>
                                        <option value="Married">Married</option>
                                        <option value="Divorced">Divorced</option>
                                        <option value="Widowed">Widowed</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-home me-2"></i>Address Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="address" class="form-label">Street Address / House Number *</label>
                                    <input type="text" class="form-control" id="address" name="address" placeholder="e.g., 123 Main Street" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="region" class="form-label">Region *</label>
                                    <select class="form-select" id="region" name="region" required>
                                        <option value="">Select Region</option>
                                        <?php while ($reg = $regions->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars($reg['region_name']); ?>"><?php echo htmlspecialchars($reg['region_name']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="province" class="form-label">Province *</label>
                                    <select class="form-select" id="province" name="province" required>
                                        <option value="">Select Region First</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="city" class="form-label">City/Municipality *</label>
                                    <select class="form-select" id="city" name="city" required>
                                        <option value="">Select Province First</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="barangay" class="form-label">Barangay *</label>
                                    <select class="form-select" id="barangay" name="barangay" required>
                                        <option value="">Select City First</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="zipcode" class="form-label">ZIP Code *</label>
                                    <input type="text" class="form-control" id="zipcode" name="zipcode" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="nationality" class="form-label">Nationality *</label>
                                    <input type="text" class="form-control" id="nationality" name="nationality" value="Filipino" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="complete_address" class="form-label">Complete Address (Auto-generated)</label>
                                    <textarea class="form-control" id="complete_address" name="complete_address" rows="2" readonly placeholder="Auto-generated from above fields"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-phone me-2"></i>Contact Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="contact_no" class="form-label">Contact Number *</label>
                                    <input type="text" class="form-control" id="contact_no" name="contact_no" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="religion" class="form-label">Religion *</label>
                                    <select class="form-select" id="religion" name="religion" required>
                                        <option value="">Select Religion</option>
                                        <?php while ($rel = $religions->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars($rel['religion']); ?>"><?php echo htmlspecialchars($rel['religion']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Background Information -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-users me-2"></i>Background Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="ethnicity" class="form-label">Ethnicity</label>
                                    <select class="form-select" id="ethnicity" name="ethnicity">
                                        <option value="">Select Ethnicity</option>
                                        <?php while ($eth = $ethnicities->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars(trim($eth['ethnicname'])); ?>"><?php echo htmlspecialchars(trim($eth['ethnicname'])); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="dialect" class="form-label">Dialect</label>
                                    <select class="form-select" id="dialect" name="dialect">
                                        <option value="">Select Dialect</option>
                                        <?php while ($dia = $dialects->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars($dia['dialects']); ?>"><?php echo htmlspecialchars($dia['dialects']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="year_level" class="form-label">Year Level *</label>
                                    <select class="form-select" id="year_level" name="year_level" required>
                                        <option value="">Select Year</option>
                                        <option value="1">1st Year</option>
                                        <option value="2">2nd Year</option>
                                        <option value="3">3rd Year</option>
                                        <option value="4">4th Year</option>
                                        <option value="5">5th Year</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Guardian Information -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-user-shield me-2"></i>Guardian Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="guardian_name" class="form-label">Guardian Name</label>
                                    <input type="text" class="form-control" id="guardian_name" name="guardian_name" placeholder="Full Name">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="guardian_relationship" class="form-label">Relationship</label>
                                    <select class="form-select" id="guardian_relationship" name="guardian_relationship">
                                        <option value="">Select Relationship</option>
                                        <option value="Parent">Parent</option>
                                        <option value="Father">Father</option>
                                        <option value="Mother">Mother</option>
                                        <option value="Sibling">Sibling</option>
                                        <option value="Grandparent">Grandparent</option>
                                        <option value="Aunt/Uncle">Aunt/Uncle</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="guardian_phone" class="form-label">Guardian Phone</label>
                                    <input type="text" class="form-control" id="guardian_phone" name="guardian_phone" placeholder="Contact Number">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="guardian_email" class="form-label">Guardian Email</label>
                                    <input type="email" class="form-control" id="guardian_email" name="guardian_email" placeholder="Email Address">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="guardian_address" class="form-label">Guardian Address</label>
                                    <input type="text" class="form-control" id="guardian_address" name="guardian_address" placeholder="Complete Address">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Previous School Information -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-school me-2"></i>Previous School Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="previous_school" class="form-label">Previous School Name</label>
                                    <input type="text" class="form-control" id="previous_school" name="previous_school" placeholder="School Name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="previous_school_address" class="form-label">School Address</label>
                                    <input type="text" class="form-control" id="previous_school_address" name="previous_school_address" placeholder="School Address">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Course Information Section -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Course Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="school_year" class="form-label">School Year *</label>
                                    <select class="form-select" id="school_year" name="school_year" required>
                                        <option value="">Select School Year</option>
                                        <?php foreach ($school_years as $sy): ?>
                                            <option value="<?php echo $sy; ?>" <?php echo ($sy == $current_year . '-' . ($current_year + 1)) ? 'selected' : ''; ?>>
                                                <?php echo $sy; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="course" class="form-label">Course *</label>
                                    <select class="form-select" id="course" name="course" required>
                                        <option value="">Select Course</option>
                                        <?php 
                                        $courses->data_seek(0);
                                        while ($course = $courses->fetch_assoc()): 
                                        ?>
                                            <option value="<?php echo htmlspecialchars($course['code']); ?>"><?php echo htmlspecialchars($course['code']); ?> - <?php echo htmlspecialchars($course['name']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="major" class="form-label">Major (if applicable)</label>
                                    <select class="form-select" id="major" name="major">
                                        <option value="">Select Major</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-paper-plane me-2"></i>Submit Enrollment</button>
                            <button type="reset" class="btn btn-secondary btn-lg ms-2"><i class="fas fa-undo me-2"></i>Reset Form</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
