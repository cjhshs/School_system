<?php
/**
 * Shared Portal Layout Template
 * Modern UI with sidebar navigation
 */

if (!isset($portal_title)) $portal_title = 'Dashboard';
if (!isset($portal_icon)) $portal_icon = 'fa-home';
if (!isset($nav_items)) $nav_items = [];
if (!isset($user_name)) $user_name = $_SESSION['username'] ?? 'User';
if (!isset($user_role)) $user_role = $_SESSION['user_role'] ?? 'User';
if (!isset($user_avatar)) $user_avatar = isset($_SESSION['avatar']) ? $_SESSION['avatar'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $portal_title; ?> - CJLG University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/enrollment_system/css/modern.css">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="app-sidebar">
            <div class="sidebar-header">
                <a href="<?php echo $base_url; ?>dashboard.php" class="sidebar-brand">
                    <i class="fas fa-crown"></i>
                    <div>
                        <div><?php echo $portal_title; ?></div>
                        <small class="sidebar-subtitle">Management</small>
                    </div>
                </a>
            </div>

            <div class="sidebar-user">
                <div class="user-avatar">
                    <?php if ($user_avatar): ?>
                        <img src="<?php echo $user_avatar; ?>" alt="">
                    <?php else: ?>
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div class="user-info">
                    <h6><?php echo htmlspecialchars($user_name); ?></h6>
                    <span class="user-role-badge"><?php echo ucfirst(str_replace('_', ' ', $user_role)); ?></span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main Menu</div>
                    <?php foreach ($nav_items as $item): ?>
                        <?php if (isset($item['divider'])): ?>
                            <div class="nav-divider"></div>
                        <?php elseif (isset($item['section'])): ?>
                            <div class="nav-section-title"><?php echo $item['section']; ?></div>
                        <?php else: ?>
                            <div class="nav-item">
                                <a href="<?php echo $item['url']; ?>" 
                                   class="nav-link <?php echo ($current_page === $item['key']) ? 'active' : ''; ?>">
                                    <i class="<?php echo $item['icon']; ?>"></i>
                                    <span><?php echo $item['label']; ?></span>
                                    <?php if (isset($item['badge'])): ?>
                                        <span class="badge bg-danger"><?php echo $item['badge']; ?></span>
                                    <?php endif; ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </nav>

            <div class="sidebar-footer">
                <a href="<?php echo $base_url; ?>logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sign Out</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="app-main">
            <!-- Header -->
            <header class="app-header">
                <div class="header-left">
                    <button class="toggle-sidebar" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="page-titles">
                        <h1 class="page-title"><?php echo $portal_title; ?></h1>
                        <p class="page-subtitle"><?php echo date('l, F j, Y'); ?></p>
                    </div>
                </div>

                <div class="header-right">
                    <button class="header-action" title="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="badge-dot"></span>
                    </button>
                    <button class="header-action" title="Messages">
                        <i class="fas fa-envelope"></i>
                    </button>

                    <div class="user-dropdown">
                        <button class="user-dropdown-btn">
                            <div class="avatar">
                                <?php if ($user_avatar): ?>
                                    <img src="<?php echo $user_avatar; ?>" alt="">
                                <?php else: ?>
                                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                                <div class="user-role"><?php echo ucfirst(str_replace('_', ' ', $user_role)); ?></div>
                            </div>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="user-dropdown-menu">
                            <a href="#"><i class="fas fa-user"></i> My Profile</a>
                            <a href="#"><i class="fas fa-cog"></i> Settings</a>
                            <div class="divider"></div>
                            <a href="<?php echo $base_url; ?>logout.php"><i class="fas fa-sign-out-alt text-danger"></i> Sign Out</a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="app-content">
