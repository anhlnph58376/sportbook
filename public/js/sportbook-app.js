/**
 * SportBook Main Application Controller
 */
const SportBookApp = (function() {
    let state = {
        currentUser: null,
        activeRole: 'player', // 'player' | 'owner' | 'admin' | 'guest'
        activeTab: 'discovery', // 'discovery' | 'venue-detail' | 'my-bookings' | 'owner-console' | 'admin-console'
        sports: [],
        amenities: [],
        venues: [],
        selectedSportSlug: '',
        selectedVenue: null,
        selectedCourt: null,
        selectedDate: new Date().toISOString().split('T')[0],
        selectedSlot: null,
        currentBooking: null,
        ownerVenues: [],
        adminMetrics: null,
        notifications: [],
    };

    // Formatters
    const formatVND = (num) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(num);
    const formatDate = (d) => new Date(d).toLocaleDateString('vi-VN');

    // Toast Notifications
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <span class="material-icons-outlined">${type === 'error' ? 'error_outline' : 'check_circle'}</span>
            <span>${message}</span>
        `;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-10px)';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    // Tab Navigation
    function switchTab(tabId) {
        state.activeTab = tabId;
        document.querySelectorAll('.view-section').forEach(sec => sec.classList.remove('active'));
        const activeSec = document.getElementById(`view-${tabId}`);
        if (activeSec) activeSec.classList.add('active');

        document.querySelectorAll('.nav-link-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.tab === tabId);
        });

        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Trigger loaders
        if (tabId === 'discovery') loadVenues();
        if (tabId === 'my-bookings') loadMyBookings();
        if (tabId === 'owner-console') loadOwnerConsole();
        if (tabId === 'admin-console') loadAdminConsole();
    }

    // Initialize Role / Demo Account
    async function initAuth() {
        try {
            // Default auto-login as player if no token
            if (!SportBookApi.getToken()) {
                await SportBookApi.switchRole('player');
            }
            state.currentUser = await SportBookApi.me();
            updateRoleUI();
            loadNotifications();
        } catch (e) {
            console.warn('Auth init failed:', e);
        }
    }

    async function changeRole(roleKey) {
        try {
            state.activeRole = roleKey;
            await SportBookApi.switchRole(roleKey);
            state.currentUser = await SportBookApi.me();
            updateRoleUI();
            showToast(`Đã chuyển sang vai trò: ${SportBookApi.DEMO_ACCOUNTS[roleKey]?.label || 'Khách'}`);

            if (roleKey === 'owner') {
                switchTab('owner-console');
            } else if (roleKey === 'admin') {
                switchTab('admin-console');
            } else {
                switchTab('discovery');
            }
            loadNotifications();
        } catch (e) {
            showToast('Không thể đổi vai trò: ' + e.message, 'error');
        }
    }

    function updateRoleUI() {
        document.querySelectorAll('.role-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.role === state.activeRole);
        });

        const userLabel = document.getElementById('user-profile-name');
        if (userLabel) {
            userLabel.textContent = state.currentUser ? state.currentUser.name : 'Khách';
        }

        // Show/hide owner and admin nav tabs based on role
        const ownerTabBtn = document.getElementById('nav-btn-owner');
        const adminTabBtn = document.getElementById('nav-btn-admin');
        const bookingsTabBtn = document.getElementById('nav-btn-bookings');

        if (ownerTabBtn) {
            ownerTabBtn.style.display = (state.activeRole === 'owner' || state.activeRole === 'admin') ? 'flex' : 'none';
        }
        if (adminTabBtn) {
            adminTabBtn.style.display = state.activeRole === 'admin' ? 'flex' : 'none';
        }
        if (bookingsTabBtn) {
            bookingsTabBtn.style.display = state.currentUser ? 'flex' : 'none';
        }
    }

    // Load Metadata (Sports & Amenities)
    async function loadMetadata() {
        try {
            const [sportsRes, amenitiesRes] = await Promise.all([
                SportBookApi.getSports(),
                SportBookApi.getAmenities()
            ]);

            state.sports = sportsRes.data || [];
            state.amenities = amenitiesRes.data || [];

            renderSportsRow();
            populateSearchSelects();
        } catch (e) {
            console.error('Error loading metadata:', e);
        }
    }

    function renderSportsRow() {
        const row = document.getElementById('sports-category-row');
        if (!row) return;

        let html = `
            <div class="sport-pill ${state.selectedSportSlug === '' ? 'active' : ''}" data-slug="">
                <span class="material-icons-outlined">apps</span>
                <span>Tất cả môn</span>
            </div>
        `;

        state.sports.forEach(s => {
            html += `
                <div class="sport-pill ${state.selectedSportSlug === s.slug ? 'active' : ''}" data-slug="${s.slug}">
                    <span class="material-icons-outlined">sports</span>
                    <span>${s.name}</span>
                </div>
            `;
        });

        row.innerHTML = html;

        row.querySelectorAll('.sport-pill').forEach(pill => {
            pill.addEventListener('click', () => {
                state.selectedSportSlug = pill.dataset.slug;
                row.querySelectorAll('.sport-pill').forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
                loadVenues();
            });
        });
    }

    function populateSearchSelects() {
        const sportSelect = document.getElementById('search-sport-select');
        if (sportSelect) {
            sportSelect.innerHTML = '<option value="">Chọn môn thể thao</option>' +
                state.sports.map(s => `<option value="${s.slug}">${s.name}</option>`).join('');
        }
    }

    // Venue Discovery
    async function loadVenues() {
        const grid = document.getElementById('venues-grid');
        if (!grid) return;

        grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-muted);">Đang tải danh sách sân bãi...</div>';

        const params = {};
        if (state.selectedSportSlug) params.sport_slug = state.selectedSportSlug;

        const keywordInput = document.getElementById('search-keyword');
        if (keywordInput && keywordInput.value.trim()) params.q = keywordInput.value.trim();

        const districtInput = document.getElementById('search-district');
        if (districtInput && districtInput.value) params.district = districtInput.value;

        try {
            const res = await SportBookApi.getVenues(params);
            state.venues = res.data || [];
            renderVenuesGrid();
        } catch (e) {
            grid.innerHTML = `<div style="grid-column: 1/-1; text-align: center; color: var(--accent-rose);">Lỗi khi tải sân: ${e.message}</div>`;
        }
    }

    function renderVenuesGrid() {
        const grid = document.getElementById('venues-grid');
        if (!grid) return;

        if (state.venues.length === 0) {
            grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 60px; color: var(--text-muted);"><p style="font-size: 18px; margin-bottom: 8px;">Không tìm thấy địa điểm phù hợp</p><p style="font-size: 14px;">Thử chọn lại môn thể thao hoặc khu vực khác.</p></div>';
            return;
        }

        grid.innerHTML = state.venues.map(v => {
            const sportsPills = (v.sports || []).map(s => `<span class="venue-tag">${s.name}</span>`).join('');
            const photoUrl = v.primary_image || `https://images.unsplash.com/photo-1574629810360-7efbbe195018?w=600&auto=format&fit=crop&q=60`;

            return `
                <div class="venue-card" data-id="${v.id}" data-slug="${v.slug}">
                    <div class="venue-thumbnail-wrap">
                        <img src="${photoUrl}" alt="${v.name}" loading="lazy" />
                        <div class="venue-tag-list">${sportsPills}</div>
                        <button class="btn-favorite-heart" title="Yêu thích" onclick="event.stopPropagation(); SportBookApp.toggleFavorite(${v.id}, this)">
                            <span class="material-icons-outlined" style="font-size: 18px;">favorite</span>
                        </button>
                    </div>
                    <div class="venue-body">
                        <div class="venue-rating-row">
                            <span class="star-badge">
                                <span class="material-icons-outlined" style="font-size: 14px;">star</span>
                                ${v.average_rating > 0 ? v.average_rating : 'Mới'}
                            </span>
                            <span>(${v.total_reviews} đánh giá)</span>
                        </div>
                        <h3 class="venue-name">${v.name}</h3>
                        <div class="venue-location">
                            <span class="material-icons-outlined" style="font-size: 16px;">place</span>
                            <span>${v.district}, ${v.province}</span>
                        </div>
                        <div class="venue-meta-footer">
                            <div class="venue-time-info">
                                <span class="material-icons-outlined" style="font-size: 14px; vertical-align: text-bottom;">schedule</span>
                                ${v.opening_time} - ${v.closing_time}
                            </div>
                            <button class="btn-book-action" onclick="SportBookApp.openVenueDetail('${v.slug}')">
                                Đặt ngay
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    async function toggleFavorite(venueId, btnEl) {
        if (!state.currentUser) {
            showToast('Vui lòng đăng nhập để lưu sân yêu thích.', 'error');
            return;
        }
        try {
            const res = await SportBookApi.toggleFavorite(venueId);
            const isFav = res.data?.is_favorite;
            btnEl.classList.toggle('favorited', isFav);
            showToast(res.message || (isFav ? 'Đã thêm vào yêu thích' : 'Đã xóa khỏi yêu thích'));
        } catch (e) {
            showToast('Lỗi: ' + e.message, 'error');
        }
    }

    // Venue Detail & Interactive Court Slot Picker
    async function openVenueDetail(slugOrId) {
        try {
            switchTab('venue-detail');
            const panel = document.getElementById('view-venue-detail');
            panel.innerHTML = '<div style="text-align: center; padding: 80px; color: var(--text-muted);">Đang tải chi tiết cụm sân...</div>';

            const res = await SportBookApi.getVenue(slugOrId);
            state.selectedVenue = res.data;

            if (state.selectedVenue.courts && state.selectedVenue.courts.length > 0) {
                state.selectedCourt = state.selectedVenue.courts[0];
            } else {
                state.selectedCourt = null;
            }

            renderVenueDetailView();
            loadCourtAvailability();
        } catch (e) {
            showToast('Không thể tải chi tiết sân: ' + e.message, 'error');
            switchTab('discovery');
        }
    }

    function renderVenueDetailView() {
        const panel = document.getElementById('view-venue-detail');
        if (!panel || !state.selectedVenue) return;

        const v = state.selectedVenue;
        const amenitiesHtml = (v.amenities || []).map(a => `<span class="amenity-chip">${a.name}</span>`).join('');

        const courtTabsHtml = (v.courts || []).map((c, idx) => `
            <button class="court-tab-btn ${state.selectedCourt?.id === c.id ? 'active' : ''}" onclick="SportBookApp.selectCourt(${c.id})">
                ${c.name} (${c.sport?.name || 'Sân'})
            </button>
        `).join('');

        panel.innerHTML = `
            <div class="venue-detail-header">
                <div class="back-btn-row">
                    <button class="btn-back" onclick="SportBookApp.switchTab('discovery')">
                        <span class="material-icons-outlined">arrow_back</span>
                        <span>Quay lại danh sách sân</span>
                    </button>
                </div>
                <div class="detail-title-row">
                    <div>
                        <h1 class="detail-venue-name">${v.name}</h1>
                        <div class="venue-location" style="font-size: 15px;">
                            <span class="material-icons-outlined">place</span>
                            <span>${v.address}, ${v.district}, ${v.province}</span>
                            <span style="margin: 0 8px;">•</span>
                            <span class="material-icons-outlined">phone</span>
                            <span>${v.phone || 'Chưa cập nhật'}</span>
                        </div>
                    </div>
                    <div class="star-badge" style="font-size: 16px; padding: 6px 14px;">
                        <span class="material-icons-outlined">star</span>
                        ${v.average_rating > 0 ? v.average_rating : '5.0'} (${v.total_reviews} đánh giá)
                    </div>
                </div>
                <p style="color: var(--text-muted); font-size: 15px;">${v.description || 'Cơ sở thể thao hiện đại, đầy đủ tiện nghi tiêu chuẩn thi đấu.'}</p>
                <div class="amenities-pill-wrap">
                    <span style="font-size: 13px; color: var(--text-subtle); align-self: center;">Tiện ích:</span>
                    ${amenitiesHtml}
                </div>
            </div>

            <div class="slot-picker-container">
                <!-- Slot Selection Area -->
                <div class="slot-selection-panel">
                    <h3 style="margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <span class="material-icons-outlined" style="color: var(--primary);">event_available</span>
                        <span>Chọn Sân & Khung Giờ Thi Đấu</span>
                    </h3>

                    <!-- Court selector tabs -->
                    <div class="court-tabs-row">${courtTabsHtml}</div>

                    <!-- Date Picker and Legends -->
                    <div class="date-picker-row">
                        <div class="date-input-wrap">
                            <label style="font-size: 12px; color: var(--text-muted); display: block; margin-bottom: 4px;">Ngày đặt sân:</label>
                            <input type="date" id="slot-booking-date" value="${state.selectedDate}" min="${new Date().toISOString().split('T')[0]}" onchange="SportBookApp.changeDate(this.value)" />
                        </div>
                        <div class="slots-legend">
                            <div class="legend-item"><span class="legend-dot avail"></span><span>Trống</span></div>
                            <div class="legend-item"><span class="legend-dot booked"></span><span>Đã đặt / Đã qua</span></div>
                            <div class="legend-item"><span class="legend-dot selected"></span><span>Đang chọn</span></div>
                        </div>
                    </div>

                    <!-- Slot Grid -->
                    <div id="court-slots-grid" class="slots-grid">
                        <div style="grid-column: 1/-1; text-align: center; padding: 30px; color: var(--text-muted);">Đang tải lịch trống...</div>
                    </div>
                </div>

                <!-- Booking Summary & Checkout Action -->
                <div class="booking-summary-panel">
                    <h4 class="summary-title">Thông Tin Đặt Sân</h4>
                    <div class="summary-row">
                        <span>Cơ sở:</span>
                        <strong style="color: #fff;">${v.name}</strong>
                    </div>
                    <div class="summary-row">
                        <span>Sân đấu:</span>
                        <span id="summary-court-name">${state.selectedCourt?.name || '-'}</span>
                    </div>
                    <div class="summary-row">
                        <span>Ngày thi đấu:</span>
                        <span id="summary-date">${formatDate(state.selectedDate)}</span>
                    </div>
                    <div class="summary-row">
                        <span>Khung giờ:</span>
                        <strong id="summary-time" style="color: var(--accent-cyan);">-</strong>
                    </div>
                    <div class="summary-row">
                        <span>Thời lượng:</span>
                        <span id="summary-duration">0 giờ</span>
                    </div>
                    <div class="summary-row total">
                        <span>Tổng tiền sân:</span>
                        <span id="summary-total-price">0 ₫</span>
                    </div>
                    <div class="summary-row deposit">
                        <span>Tiền cọc giữ chỗ (30%):</span>
                        <span id="summary-deposit-price">0 ₫</span>
                    </div>

                    <button id="btn-submit-booking" class="btn-proceed-book" disabled onclick="SportBookApp.proceedBooking()">
                        Tiến Hành Đặt & Thanh Toán Cọc
                    </button>
                    <p style="font-size: 11px; color: var(--text-subtle); text-align: center; margin-top: 10px;">
                        * Được hoàn 100% cọc nếu hủy trước 24h. Khung giờ được khóa chống trùng lặp bằng Pessimistic Lock.
                    </p>
                </div>
            </div>
        `;
    }

    function selectCourt(courtId) {
        state.selectedCourt = (state.selectedVenue.courts || []).find(c => c.id === courtId);
        state.selectedSlot = null;
        updateSummary();
        renderVenueDetailView();
        loadCourtAvailability();
    }

    function changeDate(newDate) {
        state.selectedDate = newDate;
        state.selectedSlot = null;
        updateSummary();
        loadCourtAvailability();
    }

    async function loadCourtAvailability() {
        const grid = document.getElementById('court-slots-grid');
        if (!grid || !state.selectedCourt) return;

        grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 30px; color: var(--text-muted);">Đang kiểm tra tình trạng sân...</div>';

        try {
            const res = await SportBookApi.getCourtAvailability(state.selectedCourt.id, state.selectedDate);
            const data = res.data;

            if (data.is_closed) {
                grid.innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--accent-amber);"><span class="material-icons-outlined" style="font-size: 32px; display: block; margin-bottom: 8px;">night_shelter</span>${data.reason || 'Sân đóng cửa vào ngày này.'}</div>`;
                return;
            }

            if (!data.slots || data.slots.length === 0) {
                grid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 30px; color: var(--text-muted);">Không có khung giờ nào trong ngày này.</div>';
                return;
            }

            grid.innerHTML = data.slots.map((slot, idx) => {
                const isSelected = state.selectedSlot && state.selectedSlot.start_time === slot.start_time;
                const isDisabled = !slot.is_available;
                let statusClass = isDisabled ? 'disabled' : '';
                if (isSelected) statusClass += ' selected';

                return `
                    <div class="slot-btn ${statusClass}" onclick="${isDisabled ? '' : `SportBookApp.selectSlot(${idx})`}">
                        <div class="slot-time">${slot.start_time} - ${slot.end_time}</div>
                        <div class="slot-price">
                            ${isDisabled ? (slot.is_booked ? 'Đã đặt' : 'Đã qua') : formatVND(slot.price)}
                        </div>
                    </div>
                `;
            }).join('');

            // Save slots for reference
            state.availableSlots = data.slots;
        } catch (e) {
            grid.innerHTML = `<div style="grid-column: 1/-1; text-align: center; color: var(--accent-rose);">Lỗi: ${e.message}</div>`;
        }
    }

    function selectSlot(index) {
        if (!state.availableSlots || !state.availableSlots[index]) return;
        state.selectedSlot = state.availableSlots[index];

        document.querySelectorAll('.slot-btn').forEach((btn, i) => {
            btn.classList.toggle('selected', i === index);
        });

        updateSummary();
    }

    function updateSummary() {
        const courtNameEl = document.getElementById('summary-court-name');
        const dateEl = document.getElementById('summary-date');
        const timeEl = document.getElementById('summary-time');
        const durationEl = document.getElementById('summary-duration');
        const totalPriceEl = document.getElementById('summary-total-price');
        const depositPriceEl = document.getElementById('summary-deposit-price');
        const btnBook = document.getElementById('btn-submit-booking');

        if (courtNameEl) courtNameEl.textContent = state.selectedCourt?.name || '-';
        if (dateEl) dateEl.textContent = formatDate(state.selectedDate);

        if (state.selectedSlot) {
            if (timeEl) timeEl.textContent = `${state.selectedSlot.start_time} - ${state.selectedSlot.end_time}`;
            if (durationEl) durationEl.textContent = '1.0 giờ';
            if (totalPriceEl) totalPriceEl.textContent = formatVND(state.selectedSlot.price);
            if (depositPriceEl) depositPriceEl.textContent = formatVND(state.selectedSlot.deposit);
            if (btnBook) btnBook.disabled = false;
        } else {
            if (timeEl) timeEl.textContent = 'Chưa chọn';
            if (durationEl) durationEl.textContent = '0 giờ';
            if (totalPriceEl) totalPriceEl.textContent = '0 ₫';
            if (depositPriceEl) depositPriceEl.textContent = '0 ₫';
            if (btnBook) btnBook.disabled = true;
        }
    }

    // Proceed with Booking & Open Payment Simulation
    async function proceedBooking() {
        if (!state.currentUser) {
            showToast('Vui lòng đăng nhập trước khi đặt sân.', 'error');
            return;
        }
        if (!state.selectedCourt || !state.selectedSlot) {
            showToast('Vui lòng chọn khung giờ trước.', 'error');
            return;
        }

        const btn = document.getElementById('btn-submit-booking');
        if (btn) btn.disabled = true;

        try {
            showToast('Đang gửi yêu cầu và kích hoạt khóa chống trùng lịch...');
            const bookingData = {
                court_id: state.selectedCourt.id,
                booking_date: state.selectedDate,
                start_time: state.selectedSlot.start_time,
                end_time: state.selectedSlot.end_time,
                notes: 'Đặt trực tuyến qua SportBook Web',
            };

            const res = await SportBookApi.createBooking(bookingData);
            state.currentBooking = res.data;

            showToast(res.message || 'Đặt sân thành công!', 'success');

            // Open Payment Modal
            openPaymentModal(state.currentBooking);
        } catch (e) {
            if (e.status === 409) {
                showToast('Xung đột lịch! Khung giờ này vừa có người đặt. Vui lòng chọn giờ khác.', 'error');
            } else {
                showToast('Lỗi đặt sân: ' + e.message, 'error');
            }
            loadCourtAvailability();
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    function openPaymentModal(booking) {
        const modal = document.getElementById('payment-modal');
        if (!modal) return;

        document.getElementById('modal-booking-code').textContent = booking.booking_code;
        document.getElementById('modal-venue-court').textContent = `${booking.court?.venue?.name} — ${booking.court?.name}`;
        document.getElementById('modal-time-slot').textContent = `${formatDate(booking.booking_date)} (${booking.start_time} - ${booking.end_time})`;
        document.getElementById('modal-deposit-amount').textContent = formatVND(booking.deposit_amount);

        modal.classList.add('active');
    }

    async function simulatePaymentSuccess() {
        if (!state.currentBooking || !state.currentBooking.payment) {
            showToast('Không tìm thấy thông tin thanh toán.', 'error');
            return;
        }

        const paymentId = state.currentBooking.payment.id;
        const btn = document.getElementById('btn-pay-mock-success');
        if (btn) btn.disabled = true;

        try {
            showToast('Đang xử lý giao dịch qua cổng thanh toán...');
            const res = await SportBookApi.mockPaymentCheckout(paymentId, 'success');
            showToast(res.message || 'Thanh toán tiền cọc thành công!', 'success');

            closeModal('payment-modal');
            loadNotifications();
            switchTab('my-bookings');
        } catch (e) {
            showToast('Thanh toán thất bại: ' + e.message, 'error');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    // Player Portal — My Bookings
    async function loadMyBookings() {
        const listEl = document.getElementById('my-bookings-list');
        if (!listEl) return;

        listEl.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--text-muted);">Đang tải lịch sử đặt sân...</div>';

        try {
            const res = await SportBookApi.getMyBookings();
            const bookings = res.data || [];

            if (bookings.length === 0) {
                listEl.innerHTML = '<div style="text-align: center; padding: 60px; color: var(--text-muted);"><p style="font-size: 16px;">Bạn chưa có đơn đặt sân nào.</p></div>';
                return;
            }

            listEl.innerHTML = bookings.map(b => {
                const canCancel = b.status === 'awaiting_payment' || b.status === 'confirmed';
                const canReview = b.status === 'completed' && !b.review;

                return `
                    <div class="data-table-wrap" style="margin-bottom: 16px; padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                                <strong style="font-size: 16px; color: #fff;">${b.booking_code}</strong>
                                <span class="status-pill ${b.status}">${b.status_label || b.status}</span>
                            </div>
                            <div style="font-size: 14px; color: var(--text-muted); margin-bottom: 4px;">
                                <span class="material-icons-outlined" style="font-size: 14px; vertical-align: text-bottom;">stadium</span>
                                <strong>${b.court?.venue?.name}</strong> (${b.court?.name})
                            </div>
                            <div style="font-size: 13px; color: var(--text-subtle);">
                                Ngày: <strong>${formatDate(b.booking_date)}</strong> (${b.start_time} - ${b.end_time}) • Tiền cọc: <strong style="color: var(--primary);">${formatVND(b.deposit_amount)}</strong>
                            </div>
                            ${b.refund_amount ? `<div style="font-size: 12px; color: var(--accent-rose); margin-top: 4px;">Đã hoàn tiền cọc: ${formatVND(b.refund_amount)}</div>` : ''}
                        </div>
                        <div style="display: flex; gap: 8px;">
                            ${b.status === 'awaiting_payment' ? `
                                <button class="btn-action-sm primary" onclick="SportBookApp.openPaymentModal(${JSON.stringify(b).replace(/"/g, '&quot;')})">
                                    Thanh toán cọc ngay
                                </button>
                            ` : ''}
                            ${canCancel ? `
                                <button class="btn-action-sm danger" onclick="SportBookApp.openCancelModal(${b.id}, '${b.booking_code}')">
                                    Hủy đặt sân
                                </button>
                            ` : ''}
                            ${canReview ? `
                                <button class="btn-action-sm primary" onclick="SportBookApp.openReviewModal(${b.id}, '${b.court?.venue?.name}')">
                                    Đánh giá sân
                                </button>
                            ` : ''}
                        </div>
                    </div>
                `;
            }).join('');
        } catch (e) {
            listEl.innerHTML = `<div style="text-align: center; color: var(--accent-rose);">Lỗi: ${e.message}</div>`;
        }
    }

    function openCancelModal(bookingId, code) {
        state.cancelTargetId = bookingId;
        document.getElementById('cancel-modal-code').textContent = code;
        document.getElementById('cancel-reason-input').value = '';
        document.getElementById('cancel-modal').classList.add('active');
    }

    async function confirmCancelBooking() {
        const reason = document.getElementById('cancel-reason-input').value.trim();
        if (!reason) {
            showToast('Vui lòng nhập lý do hủy sân.', 'error');
            return;
        }

        try {
            const res = await SportBookApi.cancelBooking(state.cancelTargetId, reason);
            showToast(res.message || 'Hủy đơn thành công!');
            closeModal('cancel-modal');
            loadMyBookings();
            loadNotifications();
        } catch (e) {
            showToast('Không thể hủy: ' + e.message, 'error');
        }
    }

    function openReviewModal(bookingId, venueName) {
        state.reviewBookingId = bookingId;
        document.getElementById('review-venue-name').textContent = venueName;
        document.getElementById('review-rating-select').value = '5';
        document.getElementById('review-comment-input').value = '';
        document.getElementById('review-modal').classList.add('active');
    }

    async function submitReviewForm() {
        const rating = parseInt(document.getElementById('review-rating-select').value);
        const comment = document.getElementById('review-comment-input').value.trim();

        if (!comment) {
            showToast('Vui lòng nhập nội dung đánh giá.', 'error');
            return;
        }

        try {
            const res = await SportBookApi.submitReview({
                booking_id: state.reviewBookingId,
                rating,
                comment
            });
            showToast(res.message || 'Gửi đánh giá thành công!');
            closeModal('review-modal');
            loadMyBookings();
        } catch (e) {
            showToast('Lỗi: ' + e.message, 'error');
        }
    }

    // Notifications Center
    async function loadNotifications() {
        if (!state.currentUser) return;
        try {
            const res = await SportBookApi.getNotifications();
            state.notifications = res.data?.notifications || [];
            const unread = res.data?.unread_count || 0;

            const badge = document.getElementById('notification-badge-count');
            if (badge) {
                badge.textContent = unread;
                badge.style.display = unread > 0 ? 'block' : 'none';
            }

            renderNotificationsDropdown();
        } catch (e) {
            console.warn('Notification load failed:', e);
        }
    }

    function renderNotificationsDropdown() {
        const list = document.getElementById('notifications-list');
        if (!list) return;

        if (state.notifications.length === 0) {
            list.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--text-muted); font-size: 13px;">Không có thông báo mới</div>';
            return;
        }

        list.innerHTML = state.notifications.map(n => {
            const isUnread = !n.read_at;
            const message = n.data?.message || 'Thông báo mới từ SportBook';

            return `
                <div class="notif-item ${isUnread ? 'unread' : ''}" onclick="SportBookApp.markNotifRead('${n.id}')">
                    <div style="font-weight: ${isUnread ? '600' : '400'}; color: #fff; margin-bottom: 2px;">${message}</div>
                    <div style="font-size: 11px; color: var(--text-subtle);">${formatDate(n.created_at)}</div>
                </div>
            `;
        }).join('');
    }

    async function markNotifRead(id) {
        try {
            await SportBookApi.markNotificationRead(id);
            loadNotifications();
        } catch (e) {}
    }

    async function markAllNotifRead() {
        try {
            await SportBookApi.markAllNotificationsRead();
            loadNotifications();
        } catch (e) {}
    }

    function toggleNotificationDropdown() {
        const dd = document.getElementById('notifications-dropdown');
        if (dd) dd.classList.toggle('active');
    }

    // Owner Console
    async function loadOwnerConsole() {
        const panel = document.getElementById('owner-venues-container');
        if (!panel) return;

        panel.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--text-muted);">Đang tải dữ liệu cơ sở thể thao...</div>';

        try {
            const res = await SportBookApi.getMyVenues();
            state.ownerVenues = res.data || [];

            if (state.ownerVenues.length === 0) {
                panel.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--text-muted);">Bạn chưa đăng ký cơ sở thể thao nào. Nhấn "Thêm Cơ Sở Mới" để bắt đầu.</div>';
                return;
            }

            panel.innerHTML = state.ownerVenues.map(v => `
                <div class="venue-card" style="padding: 20px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div>
                            <h3 style="font-size: 20px; margin-bottom: 4px;">${v.name}</h3>
                            <div style="font-size: 13px; color: var(--text-muted);">${v.address}, ${v.district}</div>
                        </div>
                        <span class="status-pill ${v.status}">${v.status}</span>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <button class="btn-action-sm primary" onclick="SportBookApp.loadOwnerBookings(${v.id}, '${v.name}')">
                            Xem Lịch Đặt & Check-In Khách
                        </button>
                    </div>
                </div>
            `).join('');
        } catch (e) {
            panel.innerHTML = `<div style="color: var(--accent-rose);">Lỗi: ${e.message}</div>`;
        }
    }

    async function loadOwnerBookings(venueId, venueName) {
        const container = document.getElementById('owner-bookings-table-container');
        if (!container) return;

        container.style.display = 'block';
        container.innerHTML = `<div style="padding: 20px; text-align: center; color: var(--text-muted);">Đang tải danh sách đặt sân của ${venueName}...</div>`;
        window.scrollTo({ top: container.offsetTop - 80, behavior: 'smooth' });

        try {
            const res = await SportBookApi.getVenueBookings(venueId);
            const bookings = res.data || [];

            if (bookings.length === 0) {
                container.innerHTML = `<div style="padding: 30px; text-align: center; color: var(--text-muted);">Chưa có đơn đặt sân nào cho cụm sân ${venueName}.</div>`;
                return;
            }

            container.innerHTML = `
                <h4 style="margin-bottom: 14px; font-size: 18px;">Lịch Đặt Sân Của: ${venueName}</h4>
                <div class="data-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Sân thi đấu</th>
                                <th>Thời gian</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${bookings.map(b => `
                                <tr>
                                    <td><strong>${b.booking_code}</strong></td>
                                    <td>${b.user?.name || 'Khách'}<br><small style="color: var(--text-subtle);">${b.user?.phone || ''}</small></td>
                                    <td>${b.court?.name}</td>
                                    <td>${formatDate(b.booking_date)}<br><small>${b.start_time} - ${b.end_time}</small></td>
                                    <td><span class="status-pill ${b.status}">${b.status}</span></td>
                                    <td>
                                        ${b.status === 'confirmed' ? `
                                            <button class="btn-action-sm primary" onclick="SportBookApp.checkInGuest(${b.id})">Check-in</button>
                                            <button class="btn-action-sm danger" onclick="SportBookApp.rejectOwnerBooking(${b.id})">Từ chối</button>
                                        ` : '-'}
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        } catch (e) {
            container.innerHTML = `<div style="color: var(--accent-rose);">Lỗi tải lịch: ${e.message}</div>`;
        }
    }

    async function checkInGuest(bookingId) {
        try {
            const res = await SportBookApi.checkInBooking(bookingId);
            showToast('Check-in thành công cho khách hàng!', 'success');
            if (state.ownerVenues.length > 0) {
                loadOwnerBookings(state.ownerVenues[0].id, state.ownerVenues[0].name);
            }
        } catch (e) {
            showToast('Lỗi: ' + e.message, 'error');
        }
    }

    async function rejectOwnerBooking(bookingId) {
        const reason = prompt('Nhập lý do từ chối đơn đặt sân (sẽ hoàn lại cọc cho khách):', 'Sự cố kỹ thuật sân');
        if (!reason) return;

        try {
            const res = await SportBookApi.rejectBooking(bookingId, reason);
            showToast('Đã từ chối đơn đặt sân thành công!');
            if (state.ownerVenues.length > 0) {
                loadOwnerBookings(state.ownerVenues[0].id, state.ownerVenues[0].name);
            }
        } catch (e) {
            showToast('Lỗi: ' + e.message, 'error');
        }
    }

    // Admin Console
    async function loadAdminConsole() {
        try {
            const [metricsRes, pendingRes] = await Promise.all([
                SportBookApi.getAdminMetrics(),
                SportBookApi.getPendingVenues()
            ]);

            const m = metricsRes.data || {};
            const kpiGrid = document.getElementById('admin-kpi-grid');
            if (kpiGrid) {
                kpiGrid.innerHTML = `
                    <div class="kpi-card revenue">
                        <div class="kpi-label">Doanh thu cọc (VND)</div>
                        <div class="kpi-value">${formatVND(m.total_revenue_vnd || 0)}</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Tổng lượt đặt sân</div>
                        <div class="kpi-value">${m.total_bookings || 0}</div>
                    </div>
                    <div class="kpi-card pending">
                        <div class="kpi-label">Cụm sân chờ duyệt</div>
                        <div class="kpi-value">${m.pending_venues || 0}</div>
                    </div>
                    <div class="kpi-card users">
                        <div class="kpi-label">Tổng số người dùng</div>
                        <div class="kpi-value">${m.total_users || 0}</div>
                    </div>
                `;
            }

            renderAdminPendingVenues(pendingRes.data || []);
            loadAdminUsers();
        } catch (e) {
            console.error('Admin load failed:', e);
        }
    }

    function renderAdminPendingVenues(venues) {
        const table = document.getElementById('admin-pending-venues-table');
        if (!table) return;

        if (venues.length === 0) {
            table.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">Không có cơ sở nào đang chờ phê duyệt.</td></tr>';
            return;
        }

        table.innerHTML = venues.map(v => `
            <tr>
                <td><strong>${v.name}</strong></td>
                <td>${v.owner?.name || 'Chủ sân'}</td>
                <td>${v.district}, ${v.province}</td>
                <td><span class="status-pill pending_review">Chờ duyệt</span></td>
                <td>
                    <button class="btn-action-sm primary" onclick="SportBookApp.reviewAdminVenue(${v.id}, 'approve')">Phê duyệt</button>
                    <button class="btn-action-sm danger" onclick="SportBookApp.reviewAdminVenue(${v.id}, 'reject')">Từ chối</button>
                </td>
            </tr>
        `).join('');
    }

    async function reviewAdminVenue(venueId, action) {
        let reason = '';
        if (action === 'reject') {
            reason = prompt('Nhập lý do từ chối hồ sơ sân:', 'Hồ sơ chưa đạt tiêu chuẩn');
            if (!reason) return;
        }

        try {
            const res = await SportBookApi.approveVenue(venueId, action, reason);
            showToast(res.message || 'Đã xử lý hồ sơ thành công!');
            loadAdminConsole();
        } catch (e) {
            showToast('Lỗi: ' + e.message, 'error');
        }
    }

    async function loadAdminUsers() {
        const tbody = document.getElementById('admin-users-table');
        if (!tbody) return;

        try {
            const res = await SportBookApi.getUsers();
            const users = res.data || [];

            tbody.innerHTML = users.map(u => `
                <tr>
                    <td><strong>${u.name}</strong></td>
                    <td>${u.email}</td>
                    <td>${(u.roles || []).join(', ')}</td>
                    <td><span class="status-pill ${u.status}">${u.status === 'active' ? 'Hoạt động' : 'Bị khóa'}</span></td>
                    <td>
                        <button class="btn-action-sm ${u.status === 'active' ? 'danger' : 'primary'}" onclick="SportBookApp.toggleUserLock(${u.id})">
                            ${u.status === 'active' ? 'Khóa' : 'Mở khóa'}
                        </button>
                    </td>
                </tr>
            `).join('');
        } catch (e) {
            console.error('Users load failed:', e);
        }
    }

    async function toggleUserLock(userId) {
        try {
            const res = await SportBookApi.toggleUserLock(userId);
            showToast(res.message || 'Cập nhật tài khoản thành công!');
            loadAdminUsers();
        } catch (e) {
            showToast('Lỗi: ' + e.message, 'error');
        }
    }

    // Modal Utility
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.remove('active');
    }

    // Init
    async function init() {
        await initAuth();
        await loadMetadata();
        await loadVenues();

        // Close modal when clicking on overlay background
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) overlay.classList.remove('active');
            });
        });

        // Close notification dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const dd = document.getElementById('notifications-dropdown');
            const btn = document.getElementById('btn-notif-bell');
            if (dd && btn && !dd.contains(e.target) && !btn.contains(e.target)) {
                dd.classList.remove('active');
            }
        });
    }

    return {
        init,
        switchTab,
        changeRole,
        loadVenues,
        openVenueDetail,
        selectCourt,
        changeDate,
        selectSlot,
        proceedBooking,
        openPaymentModal,
        simulatePaymentSuccess,
        openCancelModal,
        confirmCancelBooking,
        openReviewModal,
        submitReviewForm,
        toggleFavorite,
        toggleNotificationDropdown,
        markNotifRead,
        markAllNotifRead,
        loadOwnerBookings,
        checkInGuest,
        rejectOwnerBooking,
        reviewAdminVenue,
        toggleUserLock,
        closeModal,
    };
})();

// Launch on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    SportBookApp.init();
});
