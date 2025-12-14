/**
 * Simple Likes - Alpine.js Component
 *
 * Add this to your site.js or include it separately.
 * Requires Alpine.js to be loaded first.
 *
 * CSS: Include simple-likes.css for animations and default styling.
 * @import 'vendor/simple-likes/css/simple-likes.css';
 *
 * Features:
 * - Static cache compatible (batched status fetch on page load)
 * - Automatic CSRF token refresh on 419 errors
 * - Optimistic UI updates with rollback on error
 * - Rate limit handling
 * - Smooth animations (requires CSS file)
 *
 * Usage in templates:
 * <div x-data="simpleLikes({ entryId: 'abc123' })">
 *     <button @click="toggleLike" x-text="liked ? '❤️' : '🤍'"></button>
 *     <span x-text="count"></span>
 * </div>
 */

// Global batch manager - collects all entry IDs and fetches in one request
window.SimpleLikesBatch = {
    pending: [],
    callbacks: {},
    timeout: null,
    fetching: false,
    cache: {},

    /**
     * Register an entry for batched status fetch
     */
    register(entryId, callback) {
        // If already cached, use it immediately
        if (this.cache[entryId]) {
            callback(this.cache[entryId]);
            return;
        }

        // Add to pending batch
        if (!this.callbacks[entryId]) {
            this.callbacks[entryId] = [];
            this.pending.push(entryId);
        }
        this.callbacks[entryId].push(callback);

        // Debounce - wait 50ms for more registrations before fetching
        if (this.timeout) clearTimeout(this.timeout);
        this.timeout = setTimeout(() => this.fetch(), 50);
    },

    /**
     * Fetch all pending statuses in one request
     */
    async fetch() {
        if (this.pending.length === 0 || this.fetching) return;

        this.fetching = true;
        const ids = [...this.pending];
        this.pending = [];

        try {
            const response = await fetch(`/!/simple-likes/status?ids=${ids.join(',')}`, {
                method: 'GET',
                credentials: 'same-origin'
            });

            if (response.ok) {
                const data = await response.json();

                // Cache and notify all callbacks
                ids.forEach(id => {
                    const status = data[id] || { count: 0, liked: false };
                    this.cache[id] = status;

                    if (this.callbacks[id]) {
                        this.callbacks[id].forEach(cb => cb(status));
                        delete this.callbacks[id];
                    }
                });
            } else {
                // On error, notify callbacks with null
                ids.forEach(id => {
                    if (this.callbacks[id]) {
                        this.callbacks[id].forEach(cb => cb(null));
                        delete this.callbacks[id];
                    }
                });
            }
        } catch (e) {
            console.warn('Simple Likes: Failed to fetch status batch', e);
            // Notify callbacks with null on error
            ids.forEach(id => {
                if (this.callbacks[id]) {
                    this.callbacks[id].forEach(cb => cb(null));
                    delete this.callbacks[id];
                }
            });
        } finally {
            this.fetching = false;
            // If more items were added while fetching, fetch them
            if (this.pending.length > 0) {
                this.fetch();
            }
        }
    },

    /**
     * Invalidate cache for an entry (after toggle)
     */
    invalidate(entryId) {
        delete this.cache[entryId];
    }
};

document.addEventListener('alpine:init', () => {
    Alpine.data('simpleLikes', (config = {}) => ({
        // State
        entryId: config.entryId || '',
        liked: false,
        count: config.count || 0,
        loading: true, // Start as loading until hydrated
        hydrated: false,
        canInteract: config.canInteract !== false,
        isLocked: config.isLocked || false,
        isAuthenticated: config.isAuthenticated || false,
        allowGuestLikes: config.allowGuestLikes || false,
        errorMessage: '',
        errorType: '',

        /**
         * Initialise component - register for batched status fetch
         */
        init() {
            if (this.entryId) {
                window.SimpleLikesBatch.register(this.entryId, (status) => {
                    if (status) {
                        this.liked = status.liked;
                        this.count = status.count;
                    }
                    this.loading = false;
                    this.hydrated = true;
                });
            } else {
                this.loading = false;
                this.hydrated = true;
            }
        },

        /**
         * Refresh CSRF token by fetching current page
         */
        async refreshCsrfToken() {
            try {
                const response = await fetch(window.location.href, {
                    method: 'GET',
                    credentials: 'same-origin'
                });
                const html = await response.text();
                const match = html.match(/<meta[^>]+name=["']csrf-token["'][^>]+content=["']([^"']+)["']/i);
                if (match && match[1]) {
                    const metaTag = document.querySelector('meta[name="csrf-token"]');
                    if (metaTag) {
                        metaTag.setAttribute('content', match[1]);
                    }
                    return match[1];
                }
            } catch (e) {
                console.error('Failed to refresh CSRF token:', e);
            }
            return null;
        },

        /**
         * Toggle like state for the entry
         */
        async toggleLike(isRetry = false) {
            if (this.loading || !this.canInteract || this.isLocked) return;

            let csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                this.showError('Please refresh the page and try again', 'error');
                return;
            }

            this.loading = true;
            const wasLiked = this.liked;

            // Optimistic update
            this.liked = !this.liked;
            this.count += this.liked ? 1 : -1;

            try {
                const response = await fetch(`/!/simple-likes/${this.entryId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                    }
                });

                // Handle 419 CSRF token expired - refresh and retry once
                if (response.status === 419 && !isRetry) {
                    // Revert optimistic update before retry
                    this.liked = wasLiked;
                    this.count += wasLiked ? 1 : -1;
                    this.loading = false;

                    const newToken = await this.refreshCsrfToken();
                    if (newToken) {
                        return this.toggleLike(true); // Retry with fresh token
                    } else {
                        throw new Error('Session expired. Please refresh the page.');
                    }
                }

                if (!response.ok) {
                    const data = await response.json().catch(() => ({}));
                    throw new Error(data.error || `HTTP ${response.status}`);
                }

                const data = await response.json();
                this.liked = data.user_has_liked;
                this.count = data.likes_count;
                this.errorMessage = '';

                // Invalidate batch cache so next page load gets fresh data
                window.SimpleLikesBatch.invalidate(this.entryId);

            } catch (error) {
                // Revert optimistic update
                this.liked = wasLiked;
                this.count += wasLiked ? 1 : -1;

                const msg = String(error.message || error);
                if (msg.includes('429') || msg.includes('quickly') || msg.includes('slow')) {
                    this.showError('Too many likes. Please slow down.', 'warning');
                } else if (msg.includes('Session expired')) {
                    this.showError(msg, 'error');
                } else {
                    this.showError('Please try again.', 'error');
                }
            } finally {
                this.loading = false;
            }
        },

        /**
         * Show error message with auto-hide
         */
        showError(msg, type = 'error') {
            this.errorMessage = msg;
            this.errorType = type;
            setTimeout(() => {
                this.errorMessage = '';
                this.errorType = '';
            }, 5000);
        }
    }));
});
