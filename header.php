<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CJLG University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="/enrollment_system/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/enrollment_system/css/home.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --navbar-height: 80px;
            --navbar-height-scrolled: 60px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        .navbar-main {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--navbar-height);
            background: var(--primary-gradient);
            z-index: 1000;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
        }

        .navbar-main.scrolled {
            height: var(--navbar-height-scrolled);
            background: rgba(102, 126, 234, 0.98);
            backdrop-filter: blur(20px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
        }

        .navbar-main.scrolled .nav-logo {
            font-size: 1.2rem;
        }

        .navbar-main.scrolled .nav-logo i {
            font-size: 1.8rem;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
            font-size: 1.4rem;
            font-weight: 700;
            transition: all 0.4s ease;
        }

        .nav-logo i {
            font-size: 2.2rem;
            transition: all 0.4s ease;
        }

        .nav-logo:hover {
            color: rgba(255, 255, 255, 0.9);
            transform: scale(1.02);
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 8px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link i {
            font-size: 0.9rem;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .nav-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 10px 24px !important;
            backdrop-filter: blur(10px);
        }

        .nav-btn:hover {
            background: white !important;
            color: var(--primary-color) !important;
            border-color: white;
        }

        .mobile-toggle {
            display: none;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            padding: 12px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .mobile-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .mobile-toggle span {
            display: block;
            width: 24px;
            height: 2px;
            background: white;
            margin: 5px 0;
            transition: all 0.3s ease;
        }

        .mobile-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }

        .mobile-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .mobile-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(5px, -5px);
        }

        @media (max-width: 992px) {
            .mobile-toggle {
                display: block;
            }

            .nav-menu {
                position: fixed;
                top: var(--navbar-height);
                left: 0;
                right: 0;
                background: var(--primary-gradient);
                flex-direction: column;
                padding: 20px;
                gap: 10px;
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                transition: all 0.4s ease;
            }

            .nav-menu.active {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
            }

            .navbar-main.scrolled .nav-menu {
                top: var(--navbar-height-scrolled);
            }

            .nav-link {
                width: 100%;
                justify-content: center;
                padding: 15px 20px;
            }
        }

        .body-content {
            padding-top: var(--navbar-height);
            transition: padding-top 0.4s ease;
        }

        .body-content.navbar-scrolled {
            padding-top: var(--navbar-height-scrolled);
        }
    </style>
</head>
<body>
    <nav class="navbar-main" id="navbar">
        <div class="nav-container">
            <a href="/enrollment_system/index.php" class="nav-logo">
                <i class="fas fa-graduation-cap"></i>
                <span>CJLG University</span>
            </a>

            <button class="mobile-toggle" id="mobileToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <ul class="nav-menu" id="navMenu">
                <li class="nav-item">
                    <a href="/enrollment_system/index.php" class="nav-link active">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/enrollment_system/student/register.php" class="nav-link nav-btn">
                        <i class="fas fa-user-plus"></i> Enroll Now
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/enrollment_system/student/login.php" class="nav-link">
                        <i class="fas fa-user-graduate"></i> Student
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/enrollment_system/registrar/login.php" class="nav-link">
                        <i class="fas fa-user-shield"></i> Registrar
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/enrollment_system/finance/login.php" class="nav-link">
                        <i class="fas fa-coins"></i> Finance
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/enrollment_system/teacher/login.php" class="nav-link">
                        <i class="fas fa-chalkboard-teacher"></i> Teacher
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/enrollment_system/dean/login.php" class="nav-link">
                        <i class="fas fa-user-tie"></i> Dean
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/enrollment_system/cashier/login.php" class="nav-link">
                        <i class="fas fa-cash-register"></i> Cashier
                    </a>
                </li>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'super_admin'): ?>
                <li class="nav-item">
                    <a href="/enrollment_system/superadmin/dashboard.php" class="nav-link">
                        <i class="fas fa-cog"></i> Super Admin
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="body-content" id="bodyContent">
        <script>
            $(window).on('scroll', function() {
                const $navbar = $('#navbar');
                const $body = $('#bodyContent');
                
                if ($(this).scrollTop() > 50) {
                    $navbar.addClass('scrolled');
                    $body.addClass('navbar-scrolled');
                } else {
                    $navbar.removeClass('scrolled');
                    $body.removeClass('navbar-scrolled');
                }
            });

            $('#mobileToggle').on('click', function() {
                $(this).toggleClass('active');
                $('#navMenu').toggleClass('active');
            });

            $('.nav-link').on('click', function() {
                $('#mobileToggle').removeClass('active');
                $('#navMenu').removeClass('active');
            });
        </script>
