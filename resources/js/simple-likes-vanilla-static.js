/**
 * Simple Likes - Vanilla JavaScript (Static/Non-Cached Version)
 *
 * For sites WITHOUT static caching that can use server-rendered Antlers tags.
 * For sites that don't use Alpine.js.
 * Add this to your site.js or include it separately.
 *
 * CSS: Include simple-likes.css for animations and default styling.
 * @import 'vendor/simple-likes/css/simple-likes.css';
 *
 * Features:
 * - Uses server-rendered liked state from data attributes (no API hydration)
 * - Automatic CSRF token refresh on 419 errors
 * - Optimistic UI updates with rollback on error
 * - Rate limit handling
 * - Smooth animations (requires CSS file)
 * - Simpler and lighter than the cached version
 *
 * Usage in templates:
 * <div class="simple-likes"
 *      data-simple-likes
 *      data-entry-id="{{ id }}"
 *      data-liked="{{ user_has_liked ? 'true' : 'false' }}"
 *      data-count="{{ likes_count }}"
 *      data-can-interact="true"
 *      data-is-locked="false"
 *      data-is-authenticated="{{ logged_in ? 'true' : 'false' }}"
 *      data-allow-guest-likes="true">
 *     <button class="simple-likes-btn">...</button>
 *     <span class="simple-likes-count"></span>
 *     <span class="simple-likes-error"></span>
 * </div>
 */

(function() {
    'use strict';

    /**
     * Refresh CSRF token by fetching current page
     */
    function refreshCsrfToken() {
        return fetch(window.location.href, {
            method: 'GET',
            credentials: 'same-origin'
        })
        .then(function(response) {
            return response.text();
        })
        .then(function(html) {
            var match = html.match(/<meta[^>]+name=["']csrf-token["'][^>]+content=["']([^"']+)["']/i);
            if (match && match[1]) {
                var metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    metaTag.setAttribute('content', match[1]);
                }
                return match[1];
            }
            return null;
        })
        .catch(function(e) {
            console.error('Failed to refresh CSRF token:', e);
            return null;
        });
    }

    /**
     * Initialize all Simple Likes components on the page
     */
    function initSimpleLikes() {
        document.querySelectorAll('[data-simple-likes]').forEach(initComponent);
    }

    /**
     * Initialize a single Simple Likes component
     */
    function initComponent(container) {
        // Skip if already initialized
        if (container.dataset.initialized === 'true') return;
        container.dataset.initialized = 'true';

        var button = container.querySelector('.simple-likes-btn');
        var countEl = container.querySelector('.simple-likes-count');
        var heartEl = container.querySelector('.simple-likes-heart');
        var errorEl = container.querySelector('.simple-likes-error');

        // Parse initial state from data attributes (server-rendered values)
        var state = {
            entryId: container.dataset.entryId || '',
            liked: container.dataset.liked === 'true',
            count: parseInt(container.dataset.count, 10) || 0,
            loading: false,
            canInteract: container.dataset.canInteract !== 'false',
            isLocked: container.dataset.isLocked === 'true',
            isAuthenticated: container.dataset.isAuthenticated === 'true',
            allowGuestLikes: container.dataset.allowGuestLikes === 'true',
            errorMessage: '',
            errorType: ''
        };

        /**
         * Update display elements
         */
        function updateDisplay() {
            if (countEl) {
                countEl.textContent = state.count;
            }
            if (heartEl) {
                heartEl.setAttribute('fill', state.liked ? 'currentColor' : 'none');
            }
            if (button) {
                button.disabled = state.loading;
                button.style.opacity = state.loading ? '0.5' : '1';
            }
        }

        /**
         * Show error message with auto-hide
         */
        function showError(msg, type) {
            state.errorMessage = msg;
            state.errorType = type || 'error';

            if (errorEl) {
                errorEl.textContent = msg;
                errorEl.className = 'simple-likes-error ' + (type === 'warning' ? 'simple-likes-error--warning' : 'simple-likes-error--error');
                errorEl.style.display = 'block';
            }

            setTimeout(function() {
                state.errorMessage = '';
                state.errorType = '';
                if (errorEl) {
                    errorEl.style.display = 'none';
                }
            }, 5000);
        }

        /**
         * Toggle like state for the entry
         */
        function toggleLike(isRetry) {
            if (state.loading || !state.canInteract || state.isLocked) return;

            var csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                showError('Please refresh the page and try again', 'error');
                return;
            }

            state.loading = true;
            var wasLiked = state.liked;

            // Optimistic update
            state.liked = !state.liked;
            state.count += state.liked ? 1 : -1;
            updateDisplay();

            fetch('/!/simple-likes/' + state.entryId + '/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                }
            })
            .then(function(response) {
                // Handle 419 CSRF token expired - refresh and retry once
                if (response.status === 419 && !isRetry) {
                    // Revert optimistic update before retry
                    state.liked = wasLiked;
                    state.count += wasLiked ? 1 : -1;
                    state.loading = false;
                    updateDisplay();

                    return refreshCsrfToken().then(function(newToken) {
                        if (newToken) {
                            return toggleLike(true); // Retry with fresh token
                        } else {
                            throw new Error('Session expired. Please refresh the page.');
                        }
                    });
                }

                if (!response.ok) {
                    return response.json()
                        .catch(function() { return {}; })
                        .then(function(data) {
                            throw new Error(data.error || 'HTTP ' + response.status);
                        });
                }
                return response.json();
            })
            .then(function(data) {
                if (!data) return; // Skip if we retried

                state.liked = data.user_has_liked;
                state.count = data.likes_count;
                state.errorMessage = '';
                updateDisplay();

                if (errorEl) errorEl.style.display = 'none';
            })
            .catch(function(error) {
                // Revert optimistic update
                state.liked = wasLiked;
                state.count += wasLiked ? 1 : -1;
                updateDisplay();

                var msg = String(error.message || error);
                if (msg.indexOf('429') !== -1 || msg.indexOf('quickly') !== -1 || msg.indexOf('slow') !== -1) {
                    showError('Too many likes. Please slow down.', 'warning');
                } else if (msg.indexOf('Session expired') !== -1) {
                    showError(msg, 'error');
                } else {
                    showError('Please try again.', 'error');
                }
            })
            .finally(function() {
                state.loading = false;
                updateDisplay();
            });
        }

        // Bind click event
        if (button && state.canInteract && !state.isLocked) {
            button.addEventListener('click', function() {
                toggleLike(false);
            });
        }

        // Initial display update
        updateDisplay();

        // Expose state and methods on the container for external access
        container.simpleLikes = {
            getState: function() { return state; },
            toggleLike: function() { toggleLike(false); },
            updateDisplay: updateDisplay
        };
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSimpleLikes);
    } else {
        initSimpleLikes();
    }

    // Expose for manual initialization (useful for dynamically loaded content)
    window.SimpleLikesStatic = {
        init: initSimpleLikes,
        initComponent: initComponent
    };
})();
