# Changelog

All notable changes to Simple Likes will be documented in this file.

## v2.0.0 - 02/01/2026

First stable release for Statamic 6.

### Added

- Wishlist feature for guests and authenticated users
- New Antlers tags: `{{ simple_like:wishlist }}`, `{{ simple_like:wishlist_count }}`, `{{ simple_like:is_guest }}`
- New API endpoint `/!/simple-likes/wishlist` for client-side wishlist fetching
- Example wishlist partials (Alpine.js and server-side versions)
- Guest likes cleanup command: `php please simple-likes:prune-guests --days=30 --dry-run`
- Sortable table columns in Recent Activity, Popular Entries, and Top Users widgets
- Control Panel translation support for all widgets and fieldtype
- English language file (`resources/lang/en/messages.php`)
- Publishable language files for community translations
- Language publishing option in install command
- Mobile responsive widget layouts
- Gravatar support with automatic fallback to initials

### Changed

- Statamic 6 compatibility
- Migrated all widgets from Blade views to Vue 3 components
- Migrated fieldtype to Vue 3 Composition API
- Simplified Vite build configuration
- Updated minimum requirement to `statamic/cms: ^6.0`, `laravel/framework: ^11.0 || ^12.0`
- Table headers now use Statamic's native Button component with ghost variant for consistent CP styling

---

## v1.0.2 - 02/01/2026

Final release for Statamic 5. Future development continues on v2.x for Statamic 6.

## v1.0.1 - 18/12/2025

### Added

- Documentation updates

## v1.0.0 - 14/12/2025

### Added

- Like buttons for any entry in any collection
- Guest and authenticated user support with privacy-friendly IP hashing
- Per-entry control to enable/disable guest likes or close likes entirely
- Boost field to set a starting count for social proof
- Antlers tags: `simple_like`, `simple_like:count`, `simple_like:popular`, `simple_like:activity`, `simple_like:weekly`, `simple_like:top_users`
- Four dashboard widgets: Overview, Recent Activity, Popular Entries, Top Users
- Multi-layer spam protection with configurable rate limiting
- Batched API requests to minimise database queries
- Full static caching support with client-side hydration
- Alpine.js and Vanilla JS versions included
- Flexible database support (SQLite, MySQL, MariaDB, PostgreSQL)
- Install command: `php please simple-likes:install`
- Cache warming command: `php please simple-likes:warm-cache`
