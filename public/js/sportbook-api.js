/**
 * SportBook API Client & Authentication Layer
 */
const SportBookApi = (function() {
    const API_BASE = '/api/v1';

    const DEMO_ACCOUNTS = {
        player: { email: 'player1@sportbook.vn', password: 'Password@123', role: 'player', label: 'Nguyễn Tuấn Anh (Người chơi)' },
        owner: { email: 'owner1@sportbook.vn', password: 'Password@123', role: 'venue_owner', label: 'Trần Văn Hoàng (Chủ sân Hoàng Gia)' },
        admin: { email: 'admin@sportbook.vn', password: 'Password@123', role: 'admin', label: 'Quản trị viên (Admin)' },
    };

    function getToken() {
        return localStorage.getItem('sportbook_token');
    }

    function setToken(token) {
        if (token) {
            localStorage.setItem('sportbook_token', token);
        } else {
            localStorage.removeItem('sportbook_token');
        }
    }

    function getCurrentUser() {
        try {
            return JSON.parse(localStorage.getItem('sportbook_user'));
        } catch (e) {
            return null;
        }
    }

    function setCurrentUser(user) {
        if (user) {
            localStorage.setItem('sportbook_user', JSON.stringify(user));
        } else {
            localStorage.removeItem('sportbook_user');
        }
    }

    async function request(endpoint, options = {}) {
        const url = `${API_BASE}${endpoint}`;
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...(options.headers || {})
        };

        const token = getToken();
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        try {
            const res = await fetch(url, {
                ...options,
                headers
            });

            const data = await res.json().catch(() => ({ success: false, message: 'Phản hồi không hợp lệ từ máy chủ.' }));

            if (!res.ok) {
                const error = new Error(data.message || `Lỗi yêu cầu: ${res.status}`);
                error.status = res.status;
                error.data = data;
                throw error;
            }

            return data;
        } catch (err) {
            console.error(`[API Error ${endpoint}]:`, err);
            throw err;
        }
    }

    return {
        DEMO_ACCOUNTS,
        getToken,
        getCurrentUser,

        // Auth
        async login(email, password) {
            const res = await request('/auth/login', {
                method: 'POST',
                body: JSON.stringify({ email, password })
            });
            if (res.data && res.data.access_token) {
                setToken(res.data.access_token);
                setCurrentUser(res.data.user);
            }
            return res.data;
        },

        async logout() {
            try {
                await request('/auth/logout', { method: 'POST' });
            } catch (e) {}
            setToken(null);
            setCurrentUser(null);
        },

        async me() {
            try {
                const res = await request('/auth/me');
                if (res.data) {
                    setCurrentUser(res.data);
                }
                return res.data;
            } catch (e) {
                setToken(null);
                setCurrentUser(null);
                return null;
            }
        },

        async switchRole(roleKey) {
            if (roleKey === 'guest') {
                this.logout();
                return null;
            }
            const creds = DEMO_ACCOUNTS[roleKey];
            if (!creds) return null;
            return await this.login(creds.email, creds.password);
        },

        // Metadata
        getSports: () => request('/sports'),
        getAmenities: () => request('/amenities'),
        getConfigurations: () => request('/configurations'),

        // Venues
        getVenues: (params = {}) => {
            const query = new URLSearchParams(params).toString();
            return request(`/venues?${query}`);
        },
        getVenue: (idOrSlug) => request(`/venues/${idOrSlug}`),
        getVenueCourts: (venueId) => request(`/venues/${venueId}/courts`),
        getCourtAvailability: (courtId, date) => request(`/courts/${courtId}/availability?date=${date}`),
        getVenueReviews: (venueId) => request(`/venues/${venueId}/reviews`),
        toggleFavorite: (venueId) => request(`/venues/${venueId}/favorite`, { method: 'POST' }),

        // Bookings (Player)
        createBooking: (data) => request('/bookings', {
            method: 'POST',
            body: JSON.stringify(data)
        }),
        getMyBookings: (params = {}) => {
            const query = new URLSearchParams(params).toString();
            return request(`/bookings/my?${query}`);
        },
        getBooking: (id) => request(`/bookings/${id}`),
        cancelBooking: (id, reason) => request(`/bookings/${id}/cancel`, {
            method: 'POST',
            body: JSON.stringify({ reason })
        }),
        checkoutBooking: (id, provider = 'mock') => request(`/bookings/${id}/checkout`, {
            method: 'POST',
            body: JSON.stringify({ provider })
        }),
        mockPaymentCheckout: (paymentId, status = 'success') => request('/payments/mock/checkout', {
            method: 'POST',
            body: JSON.stringify({ payment_id: paymentId, status })
        }),

        // Reviews
        submitReview: (data) => request('/reviews', {
            method: 'POST',
            body: JSON.stringify(data)
        }),

        // Notifications
        getNotifications: () => request('/notifications'),
        markNotificationRead: (id) => request(`/notifications/${id}/read`, { method: 'POST' }),
        markAllNotificationsRead: () => request('/notifications/read-all', { method: 'POST' }),

        // Owner Portal
        getMyVenues: () => request('/owner/venues'),
        createVenue: (data) => request('/owner/venues', {
            method: 'POST',
            body: JSON.stringify(data)
        }),
        getVenueBookings: (venueId) => request(`/owner/venues/${venueId}/bookings`),
        checkInBooking: (bookingId) => request(`/owner/bookings/${bookingId}/check-in`, { method: 'POST' }),
        rejectBooking: (bookingId, reason) => request(`/owner/bookings/${bookingId}/reject`, {
            method: 'POST',
            body: JSON.stringify({ reason })
        }),
        replyReview: (reviewId, comment) => request(`/owner/reviews/${reviewId}/reply`, {
            method: 'POST',
            body: JSON.stringify({ comment })
        }),

        // Admin Portal
        getAdminMetrics: () => request('/admin/metrics'),
        getPendingVenues: () => request('/admin/venues/pending'),
        approveVenue: (venueId, action, reason = '') => request(`/admin/venues/${venueId}/approve`, {
            method: 'POST',
            body: JSON.stringify({ action, rejection_reason: reason })
        }),
        getUsers: (params = {}) => {
            const query = new URLSearchParams(params).toString();
            return request(`/admin/users?${query}`);
        },
        toggleUserLock: (userId) => request(`/admin/users/${userId}/toggle-lock`, { method: 'POST' }),
        getAuditLogs: () => request('/admin/audit-logs'),
    };
})();
