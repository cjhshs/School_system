</div>
    
    <footer style="background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%); color: white; padding: 40px 0 20px; margin-top: 60px;">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5><i class="fas fa-graduation-cap me-2"></i>CJLG University</h5>
                    <p class="text-white-50 mt-3">Streamlined academic management for modern education. Your gateway to seamless enrollment and administrative excellence.</p>
                </div>
                <div class="col-lg-4">
                    <h6>Quick Links</h6>
                    <ul class="list-unstyled mt-3">
                        <li class="mb-2"><a href="/enrollment_system/index.php" class="text-white-50 text-decoration-none hover-text-white"><i class="fas fa-chevron-right me-2"></i>Home</a></li>
                        <li class="mb-2"><a href="/enrollment_system/student/register.php" class="text-white-50 text-decoration-none hover-text-white"><i class="fas fa-chevron-right me-2"></i>Enroll Now</a></li>
                        <li class="mb-2"><a href="/enrollment_system/student/login.php" class="text-white-50 text-decoration-none hover-text-white"><i class="fas fa-chevron-right me-2"></i>Student Portal</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6>Contact</h6>
                    <ul class="list-unstyled mt-3 text-white-50">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>University Campus</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i>(123) 456-7890</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i>info@university.edu</li>
                    </ul>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.1); margin: 30px 0 20px;">
            <div class="text-center text-white-50">
                <p class="mb-0">&copy; 2026 CJLG University. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <style>
        .hover-text-white:hover {
            color: white !important;
            transition: color 0.3s ease;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if (isset($extra_js)): ?>
    <?php echo $extra_js; ?>
    <?php endif; ?>
</body>
</html>