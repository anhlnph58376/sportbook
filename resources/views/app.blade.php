<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportBook — Nền Tảng Đặt Sân Thể Thao & Quản Lý Cơ Sở Toàn Diện</title>
    <meta name="description" content="Hệ thống đặt sân bóng đá, cầu lông, tennis, pickleball và bóng rổ chuyên nghiệp với cơ chế chống trùng lịch theo thời gian thực và thanh toán tự động.">
    <meta name="theme-color" content="#0a0e17">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

    <!-- SportBook Design System CSS -->
    <link rel="stylesheet" href="/css/sportbook.css">
</head>
<body>

    <!-- ========================================================================= -->
    <!-- TOP DEMO ROLE SWITCHER BAR (Tasting Bar for Portfolios & Technical Review) -->
    <!-- ========================================================================= -->
    <header class="role-switcher-bar" role="banner">
        <div class="role-switcher-inner">
            <div class="role-switcher-left">
                <span class="role-switcher-label">
                    <span class="material-icons-outlined" style="font-size: 16px; color: var(--accent-cyan); vertical-align: middle;">tune</span>
                    Trải Nghiệm Theo Vai Trò:
                </span>
                <div class="role-btn-group" role="group" aria-label="Role Switcher">
                    <button type="button" class="role-btn active" data-role="player" onclick="SportBookApp.changeRole('player')">
                        <span class="material-icons-outlined" style="font-size: 15px;">sports_tennis</span>
                        <span>Người Đặt Sân (Player)</span>
                    </button>
                    <button type="button" class="role-btn" data-role="owner" onclick="SportBookApp.changeRole('owner')">
                        <span class="material-icons-outlined" style="font-size: 15px;">storefront</span>
                        <span>Chủ Sân Bãi (Owner)</span>
                    </button>
                    <button type="button" class="role-btn" data-role="admin" onclick="SportBookApp.changeRole('admin')">
                        <span class="material-icons-outlined" style="font-size: 15px;">admin_panel_settings</span>
                        <span>Quản Trị Viên (Admin)</span>
                    </button>
                    <button type="button" class="role-btn" data-role="guest" onclick="SportBookApp.changeRole('guest')">
                        <span class="material-icons-outlined" style="font-size: 15px;">person_outline</span>
                        <span>Khách (Guest)</span>
                    </button>
                </div>
            </div>

            <div class="role-switcher-right">
                <!-- In-app Notification Center -->
                <div class="notif-bell-wrap" style="position: relative;">
                    <button id="btn-notif-bell" class="btn-notif" aria-label="Thông báo" onclick="SportBookApp.toggleNotificationDropdown()">
                        <span class="material-icons-outlined">notifications</span>
                        <span id="notification-badge-count" class="notif-badge" style="display: none;">0</span>
                    </button>
                    
                    <div id="notifications-dropdown" class="notif-dropdown">
                        <div class="notif-header">
                            <strong>Thông Báo Hệ Thống</strong>
                            <button class="btn-mark-all-read" onclick="SportBookApp.markAllNotifRead()">Đọc tất cả</button>
                        </div>
                        <div id="notifications-list" class="notif-body">
                            <div style="padding: 24px; text-align: center; color: var(--text-muted); font-size: 13px;">
                                Đang tải thông báo...
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Profile Label -->
                <div class="user-chip">
                    <span class="material-icons-outlined" style="font-size: 18px; color: var(--primary);">account_circle</span>
                    <span id="user-profile-name" style="font-size: 13px; font-weight: 600; color: #fff;">Nguyễn Văn An</span>
                </div>
            </div>
        </div>
    </header>

    <!-- ========================================================================= -->
    <!-- MAIN NAVIGATION BAR -->
    <!-- ========================================================================= -->
    <nav class="main-navbar">
        <div class="navbar-container">
            <a href="#" class="brand-logo" onclick="SportBookApp.switchTab('discovery'); return false;">
                <div class="brand-symbol">
                    <span class="material-icons-outlined">sports_soccer</span>
                </div>
                <div class="brand-text">
                    Sport<span>Book</span>
                </div>
            </a>

            <div class="nav-links">
                <button class="nav-link-btn active" data-tab="discovery" onclick="SportBookApp.switchTab('discovery')">
                    <span class="material-icons-outlined" style="font-size: 18px;">explore</span>
                    <span>Khám Phá Sân</span>
                </button>
                <button id="nav-btn-bookings" class="nav-link-btn" data-tab="my-bookings" onclick="SportBookApp.switchTab('my-bookings')">
                    <span class="material-icons-outlined" style="font-size: 18px;">confirmation_number</span>
                    <span>Lịch Đặt Của Tôi</span>
                </button>
                <button id="nav-btn-owner" class="nav-link-btn" data-tab="owner-console" style="display: none;" onclick="SportBookApp.switchTab('owner-console')">
                    <span class="material-icons-outlined" style="font-size: 18px;">dashboard</span>
                    <span>Bảng Quản Lý Chủ Sân</span>
                </button>
                <button id="nav-btn-admin" class="nav-link-btn" data-tab="admin-console" style="display: none;" onclick="SportBookApp.switchTab('admin-console')">
                    <span class="material-icons-outlined" style="font-size: 18px;">security</span>
                    <span>Hệ Thống Quản Trị</span>
                </button>
            </div>

            <div class="nav-cta">
                <button class="btn-primary-cta" onclick="SportBookApp.switchTab('discovery')">
                    <span class="material-icons-outlined" style="font-size: 18px;">flash_on</span>
                    <span>Đặt Sân Nhanh</span>
                </button>
            </div>
        </div>
    </nav>

    <!-- ========================================================================= -->
    <!-- MAIN CONTENT CONTAINER -->
    <!-- ========================================================================= -->
    <main class="content-wrapper">

        <!-- ===================================================================== -->
        <!-- VIEW 1: VENUE DISCOVERY & EXPLORATION (PLAYER FACING) -->
        <!-- ===================================================================== -->
        <section id="view-discovery" class="view-section active">
            <!-- Hero Banner -->
            <div class="hero-banner">
                <div class="hero-content">
                    <div class="hero-badge">
                        <span class="material-icons-outlined" style="font-size: 14px; color: var(--primary);">verified</span>
                        <span>Khóa Lịch Chống Trùng — Pessimistic Locking Row-Level</span>
                    </div>
                    <h1 class="hero-title">
                        Đặt Sân Thể Thao Trực Tuyến <br>
                        <span class="gradient-text">Nhanh Chóng & Chuẩn Xác 100%</span>
                    </h1>
                    <p class="hero-subtitle">
                        Khám phá và đặt trước sân bóng đá, cầu lông, tennis, pickleball tại TP. Hồ Chí Minh. Xác nhận tức thì, thanh toán cọc linh hoạt, kiểm soát lịch trống trực quan.
                    </p>

                    <!-- Search Filter Box -->
                    <div class="search-filter-box">
                        <div class="search-field-group">
                            <span class="material-icons-outlined field-icon">search</span>
                            <input type="text" id="search-keyword" placeholder="Tên cụm sân, đường hoặc địa điểm..." onkeyup="if(event.key === 'Enter') SportBookApp.loadVenues()" />
                        </div>
                        <div class="search-divider"></div>
                        <div class="search-field-group">
                            <span class="material-icons-outlined field-icon">category</span>
                            <select id="search-sport-select" onchange="SportBookApp.loadVenues()">
                                <option value="">Tất cả môn thể thao</option>
                            </select>
                        </div>
                        <div class="search-divider"></div>
                        <div class="search-field-group">
                            <span class="material-icons-outlined field-icon">place</span>
                            <input type="text" id="search-district" placeholder="Quận / Huyện (VD: Quận 7)" onkeyup="if(event.key === 'Enter') SportBookApp.loadVenues()" />
                        </div>
                        <button class="btn-search-trigger" onclick="SportBookApp.loadVenues()">
                            <span class="material-icons-outlined">search</span>
                            <span>Tìm Kiếm</span>
                        </button>
                    </div>

                    <!-- Platform Live Stats -->
                    <div class="hero-stats-row">
                        <div class="stat-pill">
                            <strong style="color: var(--primary);">45+</strong> Sân Tiêu Chuẩn
                        </div>
                        <div class="stat-pill">
                            <strong style="color: var(--accent-cyan);">100%</strong> Chống Trùng Giờ
                        </div>
                        <div class="stat-pill">
                            <strong style="color: var(--accent-amber);">30-min</strong> Bước Giá Linh Hoạt
                        </div>
                        <div class="stat-pill">
                            <strong style="color: var(--accent-indigo);">Hoàn Cọc 24h</strong> Bảo Vệ Người Chơi
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sports Category Filter Row -->
            <div class="sports-filter-section">
                <div class="section-title-wrap">
                    <h2 class="section-heading">Bộ Môn Thể Thao</h2>
                    <span class="section-subheading">Chọn môn để lọc nhanh danh sách sân đang hoạt động</span>
                </div>
                <div id="sports-category-row" class="sports-category-scroll">
                    <!-- Dynamic sport pills will be rendered here -->
                </div>
            </div>

            <!-- Venues Grid -->
            <div class="venues-section">
                <div class="section-title-wrap" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px;">
                    <div>
                        <h2 class="section-heading">Cụm Sân Nổi Bật</h2>
                        <span class="section-subheading">Địa điểm chất lượng cao được đánh giá tốt nhất</span>
                    </div>
                    <button class="btn-text-action" onclick="SportBookApp.loadVenues()">
                        <span class="material-icons-outlined" style="font-size: 16px;">refresh</span>
                        <span>Làm mới danh sách</span>
                    </button>
                </div>

                <div id="venues-grid" class="venues-grid-layout">
                    <!-- Dynamic Venue Cards -->
                </div>
            </div>
        </section>

        <!-- ===================================================================== -->
        <!-- VIEW 2: VENUE DETAIL & INTERACTIVE COURT SLOT PICKER -->
        <!-- ===================================================================== -->
        <section id="view-venue-detail" class="view-section">
            <!-- Dynamic Venue Detail View injected by SportBookApp.renderVenueDetailView() -->
        </section>

        <!-- ===================================================================== -->
        <!-- VIEW 3: PLAYER PORTAL — MY BOOKINGS & REVIEWS -->
        <!-- ===================================================================== -->
        <section id="view-my-bookings" class="view-section">
            <div class="portal-header">
                <div>
                    <h1 class="portal-title">Lịch Sử Đặt Sân Của Tôi</h1>
                    <p class="portal-desc">Xem trạng thái đơn đặt sân, thanh toán cọc giữ chỗ, quản lý hủy hoặc đánh giá sân sau khi thi đấu.</p>
                </div>
                <button class="btn-primary-cta" onclick="SportBookApp.switchTab('discovery')">
                    <span class="material-icons-outlined" style="font-size: 16px;">add_circle_outline</span>
                    <span>Đặt Thêm Sân Mới</span>
                </button>
            </div>

            <div id="my-bookings-list" class="bookings-list-container">
                <!-- Dynamic Player Bookings injected here -->
            </div>
        </section>

        <!-- ===================================================================== -->
        <!-- VIEW 4: OWNER CONSOLE — VENUE & BOOKING MANAGEMENT -->
        <!-- ===================================================================== -->
        <section id="view-owner-console" class="view-section">
            <div class="portal-header">
                <div>
                    <div class="hero-badge" style="margin-bottom: 8px;">
                        <span class="material-icons-outlined" style="font-size: 14px; color: var(--accent-cyan);">storefront</span>
                        <span>Cổng Chủ Sân — SportBook Owner Dashboard</span>
                    </div>
                    <h1 class="portal-title">Quản Lý Cơ Sở Thể Thao</h1>
                    <p class="portal-desc">Theo dõi danh sách cụm sân, quản lý lịch đặt sân của khách, thực hiện check-in và xử lý hoàn cọc khi có sự cố kỹ thuật.</p>
                </div>
            </div>

            <div id="owner-venues-container" style="margin-bottom: 32px;">
                <!-- Owner Venues List -->
            </div>

            <div id="owner-bookings-table-container" style="display: none;">
                <!-- Owner Bookings Table with Check-in action -->
            </div>
        </section>

        <!-- ===================================================================== -->
        <!-- VIEW 5: SUPER ADMIN CONSOLE — METRICS & AUDIT -->
        <!-- ===================================================================== -->
        <section id="view-admin-console" class="view-section">
            <div class="portal-header">
                <div>
                    <div class="hero-badge" style="margin-bottom: 8px;">
                        <span class="material-icons-outlined" style="font-size: 14px; color: var(--accent-rose);">admin_panel_settings</span>
                        <span>Trung Tâm Quản Trị Hệ Thống — SportBook Super Admin</span>
                    </div>
                    <h1 class="portal-title">Bảng Điều Khiển & Phê Duyệt</h1>
                    <p class="portal-desc">Tổng quan các chỉ số doanh thu tiền cọc, số lượt đặt sân, phê duyệt hồ sơ cụm sân mới và quản trị tài khoản người dùng.</p>
                </div>
            </div>

            <!-- KPI Metric Cards Grid -->
            <div id="admin-kpi-grid" class="kpi-cards-grid">
                <!-- Dynamically populated KPI metrics -->
            </div>

            <!-- Pending Venues Approval Section -->
            <div class="admin-table-card" style="margin-top: 32px;">
                <div class="admin-table-header">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-icons-outlined" style="color: var(--accent-amber);">pending_actions</span>
                        <h3 style="font-size: 18px;">Hồ Sơ Cụm Sân Chờ Phê Duyệt</h3>
                    </div>
                    <span style="font-size: 13px; color: var(--text-muted);">Xét duyệt tiêu chuẩn trước khi niêm yết công khai</span>
                </div>
                <div class="data-table-wrap">
                    <table class="data-table">
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
                            <!-- Injected dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- User Accounts Management Section -->
            <div class="admin-table-card" style="margin-top: 32px;">
                <div class="admin-table-header">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-icons-outlined" style="color: var(--accent-indigo);">manage_accounts</span>
                        <h3 style="font-size: 18px;">Quản Lý Người Dùng & Phân Quyền</h3>
                    </div>
                    <span style="font-size: 13px; color: var(--text-muted);">Khóa hoặc mở khóa tài khoản vi phạm chính sách</span>
                </div>
                <div class="data-table-wrap">
                    <table class="data-table">
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
                            <!-- Injected dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main>

    <!-- ========================================================================= -->
    <!-- MODALS -->
    <!-- ========================================================================= -->

    <!-- Modal 1: Payment Simulation Modal -->
    <div id="payment-modal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-icons-outlined" style="color: var(--primary);">payments</span>
                    <h3 class="modal-title">Thanh Toán Tiền Cọc Giữ Chỗ</h3>
                </div>
                <button class="btn-modal-close" onclick="SportBookApp.closeModal('payment-modal')">
                    <span class="material-icons-outlined">close</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="payment-mockup-card">
                    <div class="payment-amount-label">Số tiền cọc cần thanh toán (30%)</div>
                    <div id="modal-deposit-amount" class="payment-amount-val">0 ₫</div>
                    <div class="payment-badge-secure">
                        <span class="material-icons-outlined" style="font-size: 14px;">lock</span>
                        <span>Mô phỏng Cổng Thanh Toán Trực Tuyến An Toàn</span>
                    </div>
                </div>

                <div class="payment-details-summary">
                    <div class="payment-detail-row">
                        <span>Mã đơn đặt sân:</span>
                        <strong id="modal-booking-code" style="color: var(--accent-cyan);">-</strong>
                    </div>
                    <div class="payment-detail-row">
                        <span>Cơ sở & Sân đấu:</span>
                        <span id="modal-venue-court">-</span>
                    </div>
                    <div class="payment-detail-row">
                        <span>Khung giờ thi đấu:</span>
                        <span id="modal-time-slot">-</span>
                    </div>
                </div>

                <div class="payment-instruction-box">
                    <span class="material-icons-outlined" style="color: var(--accent-amber); font-size: 20px;">info</span>
                    <p style="font-size: 12px; color: var(--text-muted); margin: 0;">
                        Trong môi trường thử nghiệm, nhấn nút bên dưới để gửi Webhook giả lập thành công (HTTP 200) từ Payment Gateway. Đơn đặt sẽ chuyển từ <code>awaiting_payment</code> sang <code>confirmed</code> ngay tức thì.
                    </p>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn-modal-secondary" onclick="SportBookApp.closeModal('payment-modal')">Để sau</button>
                <button id="btn-pay-mock-success" class="btn-modal-primary" onclick="SportBookApp.simulatePaymentSuccess()">
                    <span class="material-icons-outlined" style="font-size: 18px;">check_circle</span>
                    <span>Xác Nhận Thanh Toán Mô Phỏng (Success Webhook)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal 2: Cancel Booking Modal -->
    <div id="cancel-modal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-icons-outlined" style="color: var(--accent-rose);">cancel</span>
                    <h3 class="modal-title">Xác Nhận Hủy Đặt Sân</h3>
                </div>
                <button class="btn-modal-close" onclick="SportBookApp.closeModal('cancel-modal')">
                    <span class="material-icons-outlined">close</span>
                </button>
            </div>

            <div class="modal-body">
                <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 14px;">
                    Bạn đang yêu cầu hủy đơn <strong id="cancel-modal-code" style="color: #fff;">-</strong>. Tiền cọc sẽ được tính hoàn trả tự động theo chính sách:
                </p>
                <ul style="font-size: 13px; color: var(--text-subtle); margin-left: 20px; margin-bottom: 16px;">
                    <li>Trước giờ đấu &ge; 24h: <strong>Hoàn 100%</strong> tiền cọc.</li>
                    <li>Trước giờ đấu từ 12h - 24h: <strong>Hoàn 50%</strong> tiền cọc.</li>
                    <li>Trước giờ đấu &lt; 12h: <strong>Không hoàn cọc (0%)</strong>.</li>
                </ul>

                <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Lý do hủy sân:</label>
                <textarea id="cancel-reason-input" class="modal-textarea" rows="3" placeholder="Nhập lý do hủy sân (bắt buộc)..."></textarea>
            </div>

            <div class="modal-footer">
                <button class="btn-modal-secondary" onclick="SportBookApp.closeModal('cancel-modal')">Quay lại</button>
                <button class="btn-modal-danger" onclick="SportBookApp.confirmCancelBooking()">
                    <span class="material-icons-outlined" style="font-size: 18px;">delete_outline</span>
                    <span>Xác Nhận Hủy & Hoàn Tiền</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal 3: Review Venue Modal -->
    <div id="review-modal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="material-icons-outlined" style="color: var(--accent-amber);">rate_review</span>
                    <h3 class="modal-title">Đánh Giá Trải Nghiệm Sân</h3>
                </div>
                <button class="btn-modal-close" onclick="SportBookApp.closeModal('review-modal')">
                    <span class="material-icons-outlined">close</span>
                </button>
            </div>

            <div class="modal-body">
                <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 14px;">
                    Chia sẻ đánh giá thực tế của bạn tại <strong id="review-venue-name" style="color: #fff;">-</strong> để giúp cộng đồng thể thao có thêm thông tin.
                </p>

                <div style="margin-bottom: 16px;">
                    <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Số sao đánh giá:</label>
                    <select id="review-rating-select" class="modal-select">
                        <option value="5">⭐⭐⭐⭐⭐ (5 sao - Rất tuyệt vời)</option>
                        <option value="4">⭐⭐⭐⭐ (4 sao - Tốt)</option>
                        <option value="3">⭐⭐⭐ (3 sao - Bình thường)</option>
                        <option value="2">⭐⭐ (2 sao - Chưa hài lòng)</option>
                        <option value="1">⭐ (1 sao - Rất thất vọng)</option>
                    </select>
                </div>

                <div>
                    <label style="font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px;">Nhận xét chi tiết:</label>
                    <textarea id="review-comment-input" class="modal-textarea" rows="3" placeholder="Chất lượng mặt sân, ánh sáng, phòng thay đồ, thái độ phục vụ..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn-modal-secondary" onclick="SportBookApp.closeModal('review-modal')">Hủy</button>
                <button class="btn-modal-primary" onclick="SportBookApp.submitReviewForm()">
                    <span class="material-icons-outlined" style="font-size: 18px;">send</span>
                    <span>Gửi Đánh Giá</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notifications Container -->
    <div id="toast-container" class="toast-container" aria-live="polite"></div>

    <!-- Application JavaScript Layers -->
    <script src="/js/sportbook-api.js"></script>
    <script src="/js/sportbook-app.js"></script>
</body>
</html>
