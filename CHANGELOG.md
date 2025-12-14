# Changelog

All notable changes to Simple Likes will be documented in this file.

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
