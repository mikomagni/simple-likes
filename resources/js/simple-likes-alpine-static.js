/**
 * Simple Likes - Alpine.js Component (Static/Non-Cached Version)
 *
 * For sites WITHOUT static caching that can use server-rendered Antlers tags.
 * Add this to your site.js or include it separately.
 * Requires Alpine.js to be loaded first.
 *
 * CSS: Include simple-likes.css for animations and default styling.
 * @import 'vendor/simple-likes/css/simple-likes.css';
 *
 * Features:
 * - Uses server-rendered liked state from Antlers tags (no API hydration)
 * - Automatic CSRF token refresh on 419 errors
 * - Optimistic UI updates with rollback on error
 * - Rate limit handling
 * - Smooth animations (requires CSS file)
 * - Simpler and lighter than the cached version
 *
 * Usage in templates:
 * <div x-data="simpleLikes({
 *     entryId: '{{ id }}',
 *     liked: {{ user_has_liked ? 'true' : 'false' }},
 *     count: {{ likes_count }}
 * })">
 *     <button @click="toggleLike" x-text="liked ? '❤️' : '🤍'"></button>
 *     <span x-text="count"></span>
 * </div>
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('simpleLikes', (config = {}) => ({
        // State - all values come from server-rendered Antlers tags
        entryId: config.entryId || '',
        liked: config.liked || false,
        count: config.count || 0,
        loading: false,
        canInteract: config.canInteract !== false,
        isLocked: config.isLocked || false,
        isAuthenticated: config.isAuthenticated || false,
        allowGuestLikes: config.allowGuestLikes || false,
        errorMessage: '',
        errorType: '',

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
