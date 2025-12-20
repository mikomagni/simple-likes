# Changelog

All notable changes to Simple Likes will be documented in this file.

## v6.0.0-beta.2 - 20/12/2025

### Added

- Control Panel translation support for all widgets and fieldtype
- English language file (`resources/lang/en/messages.php`)
- Publishable language files for community translations
- Language publishing option in install command (`php please simple-likes:install`)

## v6.0.0-beta.1 - 18/12/2025

### Changed

- Statamic 6 compatibility
- Migrated all widgets from Blade views to Vue 3 components
- Migrated fieldtype to Vue 3 Composition API
- Simplified Vite build configuration
- Updated minimum requirement to `statamic/cms: ^6.0`

### Added

- Mobile responsive widget layouts
- Gravatar support with automatic fallback to initials

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
