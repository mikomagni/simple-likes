<!-- statamic:hide -->

<p align="center">
<a href="https://simplelikes.com">
<picture>
    <source srcset="./art/logo-dark.svg" media="(prefers-color-scheme: dark)">
    <img align="center" width="250" height="70" src="./art/logo-light.svg">
</picture>
</a>
</p>
<br>

<!-- /statamic:hide -->

Simple Likes is a lightweight add-on that lets visitors like, save, or favourite your content. Use it for wishlists, bookmarks, shortlists, whatever fits your project. Fully customisable icons and styling. Includes analytics tags and dashboard widgets to track engagement.

[**Visit the documentation**](https://simplelikes.com) to learn more about getting started with Simple Likes.

## Features

* Like buttons for any entry in any collection, with full control over which collections are enabled
* Guest & authenticated user support with privacy-friendly IP hashing
* Per-entry control to enable/disable guest likes or close likes entirely
* Wishlist feature for guests and authenticated users with Antlers tags and API endpoint
* Boost field to set a starting count for social proof
* Antlers tags for popular content, recent activity, weekly trends, top users, and wishlists
* Four dashboard widgets for at-a-glance engagement stats
* Multi-layer spam protection with configurable rate limiting
* Batched API requests to minimise database queries on pages with many like buttons
* Full static caching support with client-side hydration
* Alpine.js and Vanilla JS versions included
* Flexible database support (SQLite, MySQL, MariaDB, PostgreSQL)
* Fully translatable with publishable language files
* Guest likes cleanup command to prevent database bloat

## More than just likes

While it's called "Simple Likes," the addon is flexible enough for many use cases: likes, favourites, wishlists, bookmarks, shortlists, and more. Customise the icon, label, and styling to match your needs.

## Support

* Found a bug? [Submit a bug report](https://github.com/mikomagni/simple-likes/issues/new)
* Have a feature request? [Open a feature request](https://github.com/mikomagni/simple-likes/discussions/new?category=feature-requests)
* Have another question? [Ask for help](https://github.com/mikomagni/simple-likes/discussions/new?category=help) or [email us](mailto:support@simplelikes.com)

## Commercial addon

This addon is **paid software**. You may use it for free during development, but you must purchase a license from the [Statamic Marketplace](https://statamic.com/addons/graffio/simple-likes) before deploying to production. See [LICENSE.md](LICENSE.md) for full terms.

### Version Compatibility

| Simple Likes | Statamic | PHP | Laravel |
|-------------|----------|-----|---------|
| v2.x | 6.x | ^8.2 | ^11.0 \|\| ^12.0 |
| v1.x | 5.x | ^8.2 | ^11.0 |

Composer will automatically install the correct version based on your Statamic version.

If you purchased Simple Likes before Statamic 6, your license covers both versions.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release history.

## Credits

Icons by [Lucide](https://lucide.dev/) — ISC License
Copyright (c) for portions of Lucide are held by Cole Bemis 2013-2022 as part of Feather (MIT). All other copyright (c) for Lucide are held by Lucide Contributors 2022-2025.
