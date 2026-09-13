<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="SportBook — Nền tảng đặt sân bóng đá, cầu lông, tennis, pickleball & bóng rổ trực tuyến chuyên nghiệp tại TP. Hồ Chí Minh. Chống trùng lịch Pessimistic Locking, thanh toán cọc tức thì, hoàn tiền tự động theo chính sách." />
    <meta name="theme-color" content="#060b14" />
    <meta property="og:title" content="SportBook — Đặt Sân Thể Thao Trực Tuyến" />
    <meta property="og:description" content="Đặt sân thể thao an toàn, chống trùng lịch 100%, thanh toán tiền cọc linh hoạt." />
    <meta property="og:type" content="website" />

    <title>SportBook — Nền Tảng Đặt Sân Thể Thao Chuyên Nghiệp</title>

    <!-- Google Fonts: Outfit + Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet" />

    <!-- Google Material Icons Outlined -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />

    <!-- SportBook Design System -->
    <link rel="stylesheet" href="/css/sportbook.css" />
</head>
<body>

<!-- ============================================================
     AMBIENT BACKGROUND ORBS
     ============================================================ -->
<div class="ambient-wrap" aria-hidden="true">
    <div class="ambient-orb ambient-orb-1"></div>
    <div class="ambient-orb ambient-orb-2"></div>
    <div class="ambient-orb ambient-orb-3"></div>
</div>

<!-- ============================================================
     ROLE SWITCHER TASTING BAR (Demo / Portfolio Helper)
     ============================================================ -->
<header class="role-switcher-bar" role="banner">
    <div class="role-switcher-inner">
        <div class="role-switcher-left">
            <span class="role-switcher-label">
                <span class="material-icons-outlined" style="font-size:15px;">tune</span>
                Demo vai trò:
            </span>
            <div class="role-btn-group" role="group" aria-label="Demo role switcher">
                <button type="button" class="role-btn active" data-role="player"
                        onclick="SportBookApp.changeRole('player')"
                        title="player1@sportbook.vn / Password@123">
                    <span class="material-icons-outlined" style="font-size:14px;">sports_tennis</span>
                    <span>Player</span>
                </button>
                <button type="button" class="role-btn" data-role="owner"
                        onclick="SportBookApp.changeRole('owner')"
                        title="owner1@sportbook.vn / Password@123">
                    <span class="material-icons-outlined" style="font-size:14px;">storefront</span>
                    <span>Owner</span>
                </button>
                <button type="button" class="role-btn" data-role="admin"
                        onclick="SportBookApp.changeRole('admin')"
                        title="admin@sportbook.vn / Password@123">
                    <span class="material-icons-outlined" style="font-size:14px;">admin_panel_settings</span>
                    <span>Admin</span>
                </button>
                <button type="button" class="role-btn" data-role="guest"
                        onclick="SportBookApp.changeRole('guest')">
                    <span class="material-icons-outlined" style="font-size:14px;">person_outline</span>
                    <span>Guest</span>
                </button>
            </div>
        </div>

        <div class="role-switcher-right">
            <!-- Notification Bell -->
            <div style="position:relative;">
                <button id="btn-notif-bell"
                        class="btn-notif"
                        aria-label="Thông báo hệ thống"
                        onclick="SportBookApp.toggleNotificationDropdown()">
                    <span class="material-icons-outlined">notifications</span>
                    <span id="notification-badge-count" class="notif-badge" style="display:none;">0</span>
                </button>

                <div id="notifications-dropdown" class="notif-dropdown" role="dialog" aria-label="Trung tâm thông báo">
                    <div class="notif-header">
                        <strong>Thông Báo</strong>
                        <button class="btn-mark-all-read" onclick="SportBookApp.markAllNotifRead()">
                            Đánh dấu đã đọc tất cả
                        </button>
                    </div>
                    <div id="notifications-list" class="notif-body">
                        <div style="padding:28px 16px; text-align:center; color:var(--text-tertiary); font-size:13px;">
                            <span class="material-icons-outlined" style="font-size:32px; display:block; margin-bottom:8px; opacity:.5;">notifications_none</span>
                            Đang tải thông báo...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logged-in User Chip -->
            <div class="user-chip">
                <span class="material-icons-outlined" style="font-size:20px; color:var(--primary);">account_circle</span>
                <span id="user-profile-name" style="font-size:12.5px; font-weight:700;">Nguyễn Tuấn Anh</span>
            </div>
        </div>
    </div>
</header>

<!-- ============================================================
     MAIN NAVIGATION BAR
     ============================================================ -->
<nav class="main-navbar" role="navigation" aria-label="Điều hướng chính">
    <div class="navbar-container">
        <!-- Brand Logo -->
        <a href="#" class="brand-logo" onclick="SportBookApp.switchTab('discovery'); return false;" aria-label="SportBook Trang chủ">
            <div class="brand-symbol">
                <span class="material-icons-outlined">sports_soccer</span>
            </div>
            <div class="brand-text">Sport<span>Book</span></div>
        </a>

        <!-- Nav Links -->
        <nav class="nav-links" role="menubar">
            <button class="nav-link-btn active"
                    data-tab="discovery"
                    role="menuitem"
                    onclick="SportBookApp.switchTab('discovery')">
                <span class="material-icons-outlined" style="font-size:18px;">explore</span>
                <span>Khám Phá Sân</span>
            </button>

            <button id="nav-btn-bookings"
                    class="nav-link-btn"
                    data-tab="my-bookings"
                    role="menuitem"
                    onclick="SportBookApp.switchTab('my-bookings')">
                <span class="material-icons-outlined" style="font-size:18px;">confirmation_number</span>
                <span>Lịch Đặt Của Tôi</span>
            </button>

            <button id="nav-btn-owner"
                    class="nav-link-btn"
                    data-tab="owner-console"
                    role="menuitem"
                    style="display:none;"
                    onclick="SportBookApp.switchTab('owner-console')">
                <span class="material-icons-outlined" style="font-size:18px;">dashboard</span>
                <span>Quản Lý Sân</span>
            </button>

            <button id="nav-btn-admin"
                    class="nav-link-btn"
                    data-tab="admin-console"
                    role="menuitem"
                    style="display:none;"
                    onclick="SportBookApp.switchTab('admin-console')">
                <span class="material-icons-outlined" style="font-size:18px;">security</span>
                <span>Quản Trị Hệ Thống</span>
            </button>
        </nav>

        <!-- CTA Button -->
        <div class="nav-cta">
            <button class="btn-primary-cta" onclick="SportBookApp.switchTab('discovery')">
                <span class="material-icons-outlined" style="font-size:17px;">flash_on</span>
                <span>Đặt Sân Nhanh</span>
            </button>
        </div>
    </div>
</nav>

<!-- ============================================================
     MAIN CONTENT
     ============================================================ -->
<main class="content-wrapper" role="main">

    <!-- ====================================================
         VIEW 1 — VENUE DISCOVERY (PLAYER FACING)
         ==================================================== -->
    <section id="view-discovery" class="view-section active" aria-label="Khám phá sân thể thao">

        <!-- Hero Banner -->
        <div class="hero-banner">
            <div class="hero-content">
                <div class="hero-badge">
                    <span class="material-icons-outlined" style="font-size:14px;">lock</span>
                    Chống trùng lịch tự động — Pessimistic Locking (SELECT … FOR UPDATE)
                </div>

                <h1 class="hero-title">
                    Đặt Sân Thể Thao<br>
                    <span class="gradient-text">Nhanh Chóng &amp; Chuẩn Xác</span>
                </h1>

                <p class="hero-subtitle">
                    Khám phá và đặt trước sân bóng đá, cầu lông, tennis, pickleball &amp; bóng rổ tại TP. Hồ Chí Minh.
                    Xác nhận tức thì, khóa chỗ thông minh, thanh toán cọc linh hoạt.
                </p>

                <!-- Search Box -->
                <div class="search-filter-box" role="search">
                    <div class="search-field-group">
                        <span class="material-icons-outlined field-icon">search</span>
                        <input type="text"
                               id="search-keyword"
                               placeholder="Tên sân, đường hoặc địa điểm..."
                               aria-label="Tìm kiếm sân"
                               onkeyup="if(event.key==='Enter') SportBookApp.loadVenues()" />
                    </div>

                    <div class="search-divider"></div>

                    <div class="search-field-group">
                        <span class="material-icons-outlined field-icon">category</span>
                        <select id="search-sport-select"
                                aria-label="Chọn môn thể thao"
                                onchange="SportBookApp.loadVenues()">
                            <option value="">Tất cả bộ môn</option>
                        </select>
                    </div>

                    <div class="search-divider"></div>

                    <div class="search-field-group">
                        <span class="material-icons-outlined field-icon">place</span>
                        <input type="text"
                               id="search-district"
                               placeholder="Quận / Huyện..."
                               aria-label="Lọc theo quận huyện"
                               onkeyup="if(event.key==='Enter') SportBookApp.loadVenues()" />
                    </div>

                    <button class="btn-search-trigger" onclick="SportBookApp.loadVenues()" aria-label="Tìm kiếm">
                        <span class="material-icons-outlined">search</span>
                        <span>Tìm Kiếm</span>
                    </button>
                </div>

                <!-- Platform Highlights -->
                <div class="hero-stats-row">
                    <div class="stat-pill">
                        <strong style="color:var(--primary);">45+</strong>
                        Sân Tiêu Chuẩn
                    </div>
                    <div class="stat-pill">
                        <strong style="color:var(--cyan-400);">100%</strong>
                        Chống Trùng Giờ
                    </div>
                    <div class="stat-pill">
                        <strong style="color:var(--amber-500);">30-min</strong>
                        Bước Giá Linh Hoạt
                    </div>
                    <div class="stat-pill">
                        <strong style="color:#a78bfa);">Hoàn Cọc 24h</strong>
                        Chính Sách Bảo Vệ
                    </div>
                </div>
            </div>
        </div>

        <!-- Sport Category Filter -->
        <div class="sports-filter-section">
            <div class="section-title-wrap">
                <h2 class="section-heading">Bộ Môn Thể Thao</h2>
                <span class="section-subheading">Chọn môn để lọc danh sách sân đang hoạt động</span>
            </div>
            <div id="sports-category-row" class="sports-category-scroll" role="list" aria-label="Lọc theo bộ môn">
                <!-- Dynamically rendered sport pills -->
            </div>
        </div>

        <!-- Venues Grid -->
        <div class="venues-section">
            <div class="section-title-wrap" style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:20px;">
                <div>
                    <h2 class="section-heading">Cụm Sân Nổi Bật</h2>
                    <span class="section-subheading">Địa điểm chất lượng cao được đánh giá tốt nhất</span>
                </div>
                <button class="btn-text-action" onclick="SportBookApp.loadVenues()" aria-label="Làm mới danh sách">
                    <span class="material-icons-outlined" style="font-size:16px;">refresh</span>
                    Làm mới
                </button>
            </div>

            <div id="venues-grid" class="venues-grid-layout" role="list" aria-label="Danh sách sân thể thao">
                <!-- Dynamically rendered venue cards -->
            </div>
        </div>
    </section>

    <!-- ====================================================
         VIEW 2 — VENUE DETAIL & INTERACTIVE SLOT PICKER
         ==================================================== -->
    <section id="view-venue-detail" class="view-section" aria-label="Chi tiết cụm sân và chọn khung giờ">
        <!-- Fully dynamic — injected by SportBookApp.renderVenueDetailView() -->
    </section>

    <!-- ====================================================
         VIEW 3 — PLAYER PORTAL: MY BOOKINGS
         ==================================================== -->
    <section id="view-my-bookings" class="view-section" aria-label="Lịch sử đặt sân của tôi">
        <div class="portal-header">
            <div>
                <div class="hero-badge" style="margin-bottom:12px;">
                    <span class="material-icons-outlined" style="font-size:13px;">confirmation_number</span>
                    Cổng Người Chơi — Player Portal
                </div>
                <h1 class="portal-title">Lịch Sử Đặt Sân</h1>
                <p class="portal-desc">
                    Theo dõi trạng thái đơn đặt sân, thanh toán cọc giữ chỗ, hủy sân hoàn tiền
                    tự động theo chính sách và gửi đánh giá sau khi thi đấu.
                </p>
            </div>
            <button class="btn-primary-cta" onclick="SportBookApp.switchTab('discovery')">
                <span class="material-icons-outlined" style="font-size:16px;">add_circle_outline</span>
                Đặt Thêm Sân Mới
            </button>
        </div>

        <div id="my-bookings-list" class="bookings-list-container">
            <!-- Dynamic booking cards injected here -->
        </div>
    </section>

    <!-- ====================================================
         VIEW 4 — OWNER CONSOLE: VENUE & BOOKING MANAGEMENT
         ==================================================== -->
    <section id="view-owner-console" class="view-section" aria-label="Bảng quản lý chủ sân">
        <div class="portal-header">
            <div>
                <div class="hero-badge" style="margin-bottom:12px;">
                    <span class="material-icons-outlined" style="font-size:13px;">storefront</span>
                    Owner Dashboard — Cổng Chủ Sân Bãi
                </div>
                <h1 class="portal-title">Quản Lý Cơ Sở Thể Thao</h1>
                <p class="portal-desc">
                    Theo dõi danh sách cụm sân, quản lý lịch đặt sân của khách, thực hiện
                    check-in và xử lý hoàn cọc khi có sự cố kỹ thuật.
                </p>
            </div>
        </div>

        <div id="owner-venues-container" style="margin-bottom:28px;">
            <!-- Owner Venues List -->
        </div>

        <div id="owner-bookings-table-container" style="display:none;">
            <!-- Owner Bookings Table -->
        </div>
    </section>

    <!-- ====================================================
         VIEW 5 — SUPER ADMIN CONSOLE
         ==================================================== -->
    <section id="view-admin-console" class="view-section" aria-label="Hệ thống quản trị">
        <div class="portal-header">
            <div>
                <div class="hero-badge" style="margin-bottom:12px; background:rgba(244,63,94,0.08); border-color:rgba(244,63,94,0.2); color:var(--rose-500);">
                    <span class="material-icons-outlined" style="font-size:13px; color:var(--rose-500);">shield</span>
                    Super Admin — Trung Tâm Quản Trị Hệ Thống
                </div>
                <h1 class="portal-title">Bảng Điều Khiển &amp; Phê Duyệt</h1>
                <p class="portal-desc">
                    Tổng quan doanh thu tiền cọc, số lượt đặt sân, phê duyệt hồ sơ cụm sân
                    mới và quản trị tài khoản người dùng toàn hệ thống.
                </p>
            </div>
        </div>

        <!-- KPI Metric Cards -->
        <div id="admin-kpi-grid" class="kpi-cards-grid" aria-label="Chỉ số thống kê">
            <!-- Dynamically populated KPI cards -->
        </div>

        <!-- Pending Venues Approval -->
        <div class="admin-table-card" style="margin-top:8px;">
            <div class="admin-table-header">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="material-icons-outlined" style="color:var(--amber-500); font-size:22px;">pending_actions</span>
                    <div>
                        <h3 style="font-size:17px; margin-bottom:2px;">Hồ Sơ Cụm Sân Chờ Phê Duyệt</h3>
                        <p style="font-size:12px; color:var(--text-tertiary);">Xét duyệt tiêu chuẩn trước khi niêm yết công khai</p>
                    </div>
                </div>
            </div>
            <div class="data-table-wrap">
                <table class="data-table" aria-label="Bảng sân chờ duyệt">
                    <thead>
                        <tr>
                            <th>Tên cụm sân</th>
                            <th>Chủ sở hữu</th>
                            <th>Khu vực</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="admin-pending-venues-table">
                        <!-- Dynamically injected -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Users Management -->
        <div class="admin-table-card" style="margin-top:20px;">
            <div class="admin-table-header">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="material-icons-outlined" style="color:#a78bfa; font-size:22px;">manage_accounts</span>
                    <div>
                        <h3 style="font-size:17px; margin-bottom:2px;">Quản Lý Người Dùng &amp; Phân Quyền</h3>
                        <p style="font-size:12px; color:var(--text-tertiary);">Khóa hoặc mở khóa tài khoản vi phạm chính sách</p>
                    </div>
                </div>
            </div>
            <div class="data-table-wrap">
                <table class="data-table" aria-label="Bảng quản lý người dùng">
                    <thead>
                        <tr>
                            <th>Họ và tên</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="admin-users-table">
                        <!-- Dynamically injected -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>

</main>

<!-- ============================================================
     MODAL 1 — PAYMENT SIMULATION
     ============================================================ -->
<div id="payment-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="payment-modal-title">
    <div class="modal-container">
        <div class="modal-header">
            <h2 id="payment-modal-title" class="modal-title">
                <span class="material-icons-outlined" style="color:var(--primary); vertical-align:middle; margin-right:6px;">payments</span>
                Thanh Toán Tiền Cọc
            </h2>
            <button class="btn-modal-close"
                    onclick="SportBookApp.closeModal('payment-modal')"
                    aria-label="Đóng modal thanh toán">
                <span class="material-icons-outlined">close</span>
            </button>
        </div>

        <div class="modal-body">
            <div class="payment-mockup-card">
                <div class="payment-amount-label">Số tiền cọc giữ chỗ (30% tổng sân)</div>
                <div id="modal-deposit-amount" class="payment-amount-val">0 ₫</div>
                <div class="payment-badge-secure">
                    <span class="material-icons-outlined" style="font-size:13px;">lock</span>
                    <span>Mô phỏng Cổng Thanh Toán Bảo Mật TLS 1.3</span>
                </div>
            </div>

            <div class="payment-details-summary">
                <div class="payment-detail-row">
                    <span>Mã đơn đặt sân</span>
                    <strong id="modal-booking-code" style="color:var(--cyan-400); font-family:var(--font-mono);">—</strong>
                </div>
                <div class="payment-detail-row">
                    <span>Cơ sở &amp; Sân đấu</span>
                    <span id="modal-venue-court" style="text-align:right; max-width:200px;">—</span>
                </div>
                <div class="payment-detail-row">
                    <span>Khung giờ thi đấu</span>
                    <span id="modal-time-slot">—</span>
                </div>
            </div>

            <div class="payment-instruction-box">
                <span class="material-icons-outlined" style="color:var(--amber-500); font-size:18px; flex-shrink:0; margin-top:1px;">info_outline</span>
                <p style="font-size:12.5px; color:var(--text-secondary); margin:0; line-height:1.6;">
                    Môi trường thử nghiệm: Nhấn <strong>Xác Nhận</strong> để gửi Webhook mô phỏng thành công từ Payment Gateway. Đơn sẽ chuyển từ
                    <code style="font-size:11px; background:rgba(255,255,255,0.06); padding:1px 6px; border-radius:4px;">awaiting_payment</code> →
                    <code style="font-size:11px; background:rgba(255,255,255,0.06); padding:1px 6px; border-radius:4px;">confirmed</code> ngay tức thì.
                </p>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn-modal-secondary" onclick="SportBookApp.closeModal('payment-modal')">
                Để Sau
            </button>
            <button id="btn-pay-mock-success"
                    class="btn-modal-primary"
                    onclick="SportBookApp.simulatePaymentSuccess()">
                <span class="material-icons-outlined" style="font-size:17px;">check_circle</span>
                Xác Nhận Thanh Toán (Mock Webhook)
            </button>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL 2 — CANCEL BOOKING
     ============================================================ -->
<div id="cancel-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="cancel-modal-title">
    <div class="modal-container">
        <div class="modal-header">
            <h2 id="cancel-modal-title" class="modal-title">
                <span class="material-icons-outlined" style="color:var(--rose-500); vertical-align:middle; margin-right:6px;">cancel</span>
                Xác Nhận Hủy Đặt Sân
            </h2>
            <button class="btn-modal-close"
                    onclick="SportBookApp.closeModal('cancel-modal')"
                    aria-label="Đóng modal hủy sân">
                <span class="material-icons-outlined">close</span>
            </button>
        </div>

        <div class="modal-body">
            <p style="font-size:14px; color:var(--text-secondary); margin-bottom:16px; line-height:1.7;">
                Bạn đang yêu cầu hủy đơn
                <strong id="cancel-modal-code" style="color:var(--text-primary); font-family:var(--font-mono);">—</strong>.
                Tiền cọc sẽ được hoàn trả tự động theo chính sách:
            </p>

            <div style="background:var(--bg-surface-3); border-radius:var(--r-sm); padding:14px 18px; margin-bottom:18px; border:1px solid var(--border-soft);">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px; padding-bottom:8px; border-bottom:1px solid var(--border-soft);">
                    <span class="material-icons-outlined" style="font-size:16px; color:var(--primary);">check_circle</span>
                    <span style="font-size:13px;">Hủy trước giờ đấu <strong>≥ 24 giờ</strong> → Hoàn <strong style="color:var(--primary);">100%</strong> cọc</span>
                </div>
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px; padding-bottom:8px; border-bottom:1px solid var(--border-soft);">
                    <span class="material-icons-outlined" style="font-size:16px; color:var(--amber-500);">schedule</span>
                    <span style="font-size:13px;">Hủy trước <strong>12 – 24 giờ</strong> → Hoàn <strong style="color:var(--amber-500);">50%</strong> cọc</span>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="material-icons-outlined" style="font-size:16px; color:var(--rose-500);">cancel</span>
                    <span style="font-size:13px;">Hủy trong <strong>vòng 12 giờ</strong> → Hoàn <strong style="color:var(--rose-500);">0%</strong> cọc</span>
                </div>
            </div>

            <label for="cancel-reason-input" style="display:block; font-size:13px; font-weight:700; margin-bottom:8px;">
                Lý do hủy sân <span style="color:var(--rose-500);">*</span>
            </label>
            <textarea id="cancel-reason-input"
                      class="modal-textarea"
                      rows="3"
                      placeholder="Nhập lý do cụ thể (bắt buộc)..."
                      aria-required="true"></textarea>
        </div>

        <div class="modal-footer">
            <button class="btn-modal-secondary" onclick="SportBookApp.closeModal('cancel-modal')">
                Quay Lại
            </button>
            <button class="btn-modal-danger" onclick="SportBookApp.confirmCancelBooking()">
                <span class="material-icons-outlined" style="font-size:17px;">delete_outline</span>
                Xác Nhận Hủy &amp; Hoàn Tiền
            </button>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL 3 — REVIEW / RATING
     ============================================================ -->
<div id="review-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="review-modal-title">
    <div class="modal-container">
        <div class="modal-header">
            <h2 id="review-modal-title" class="modal-title">
                <span class="material-icons-outlined" style="color:var(--amber-500); vertical-align:middle; margin-right:6px;">star_rate</span>
                Đánh Giá Trải Nghiệm
            </h2>
            <button class="btn-modal-close"
                    onclick="SportBookApp.closeModal('review-modal')"
                    aria-label="Đóng modal đánh giá">
                <span class="material-icons-outlined">close</span>
            </button>
        </div>

        <div class="modal-body">
            <p style="font-size:14px; color:var(--text-secondary); margin-bottom:18px; line-height:1.6;">
                Chia sẻ trải nghiệm thực tế của bạn tại
                <strong id="review-venue-name" style="color:var(--text-primary);">—</strong>
                để giúp cộng đồng thể thao có thêm thông tin chính xác.
            </p>

            <div style="margin-bottom:16px;">
                <label for="review-rating-select" style="display:block; font-size:13px; font-weight:700; margin-bottom:8px;">
                    Đánh giá của bạn
                </label>
                <select id="review-rating-select" class="modal-select" aria-label="Chọn số sao đánh giá">
                    <option value="5">⭐⭐⭐⭐⭐ — 5 sao (Xuất sắc)</option>
                    <option value="4">⭐⭐⭐⭐ — 4 sao (Rất tốt)</option>
                    <option value="3">⭐⭐⭐ — 3 sao (Bình thường)</option>
                    <option value="2">⭐⭐ — 2 sao (Chưa hài lòng)</option>
                    <option value="1">⭐ — 1 sao (Rất thất vọng)</option>
                </select>
            </div>

            <div>
                <label for="review-comment-input" style="display:block; font-size:13px; font-weight:700; margin-bottom:8px;">
                    Nhận xét chi tiết <span style="color:var(--rose-500);">*</span>
                </label>
                <textarea id="review-comment-input"
                          class="modal-textarea"
                          rows="4"
                          placeholder="Chất lượng mặt sân, ánh sáng, phòng thay đồ, thái độ nhân viên, không gian bãi đỗ xe..."
                          aria-required="true"></textarea>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn-modal-secondary" onclick="SportBookApp.closeModal('review-modal')">
                Hủy
            </button>
            <button class="btn-modal-primary" onclick="SportBookApp.submitReviewForm()">
                <span class="material-icons-outlined" style="font-size:17px;">send</span>
                Gửi Đánh Giá
            </button>
        </div>
    </div>
</div>

<!-- ============================================================
     TOAST NOTIFICATION CONTAINER
     ============================================================ -->
<div id="toast-container" class="toast-container" aria-live="polite" aria-atomic="false"></div>

<!-- ============================================================
     JAVASCRIPT LAYERS
     ============================================================ -->
<script src="/js/sportbook-api.js"></script>
<script src="/js/sportbook-app.js"></script>
</body>
</html>
