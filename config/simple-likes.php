<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Template
    |--------------------------------------------------------------------------
    |
    | Set a default template for the {{ simple_like }} tag.
    | When set, all like buttons will use this template unless overridden
    | with the template_from parameter.
    |
    | Examples:
    | - null (default) - Uses the addon's built-in template
    | - 'partials.my-like-button' - Uses resources/views/partials/my-like-button.antlers.html
    | - 'simple-likes::like-button-vanilla' - Uses the addon's vanilla JS template
    |
    */
    'default_template' => env('SIMPLE_LIKES_DEFAULT_TEMPLATE', null),

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | The database connection to use for storing likes.
    | Set to null to use Laravel's default connection (from DB_CONNECTION in .env).
    |
    | Examples:
    | - null (default) - Uses your default Laravel database connection
    | - 'mysql' - Use the mysql connection defined in config/database.php
    | - 'sqlite' - Use SQLite
    | - 'simple_likes' - Use a custom connection you've defined
    |
    | To use a specific database, ensure you have:
    | 1. Defined the connection in config/database.php
    | 2. Set the appropriate credentials in your .env file
    |
    */
    'connection' => env('SIMPLE_LIKES_DB_CONNECTION', null),

    /*
    |--------------------------------------------------------------------------
    | Auto Migration
    |--------------------------------------------------------------------------
    |
    | Automatically run migration when the addon boots.
    | Set to false if you prefer to publish and run migrations manually.
    |
    */
    'auto_migrate' => env('SIMPLE_LIKES_AUTO_MIGRATE', true),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching behavior for different types of data.
    | All values are in seconds. Set to 0 to disable caching for that type.
    |
    | Tips for high-traffic sites:
    | - Keep activity_ttl low (30-60s) for near-real-time updates
    | - Use higher values for global/popular/weekly to reduce DB load
    | - The stats_all endpoint uses activity_ttl since it contains activity data
    |
    */
    'cache' => [
        // Default TTL for general caching (used as fallback)
        'default_ttl' => env('SIMPLE_LIKES_CACHE_TTL', 1800), // 30 minutes

        // Entry-specific stats (per-entry like counts, user status)
        'entry_ttl' => env('SIMPLE_LIKES_CACHE_ENTRY_TTL', 300), // 5 minutes

        // Global statistics (total likes, total entries, today/week/month counts)
        'global_ttl' => env('SIMPLE_LIKES_CACHE_GLOBAL_TTL', 1800), // 30 minutes

        // Recent activity feed (time-sensitive, shows "2 min ago" etc.)
        'activity_ttl' => env('SIMPLE_LIKES_CACHE_ACTIVITY_TTL', 60), // 1 minute

        // Popular entries ranking
        'popular_ttl' => env('SIMPLE_LIKES_CACHE_POPULAR_TTL', 1800), // 30 minutes

        // Weekly/daily chart data
        'weekly_ttl' => env('SIMPLE_LIKES_CACHE_WEEKLY_TTL', 1800), // 30 minutes

        // Top users leaderboard
        'top_users_ttl' => env('SIMPLE_LIKES_CACHE_TOP_USERS_TTL', 1800), // 30 minutes

        // Combined stats-all endpoint (short TTL since it contains activity data)
        'stats_all_ttl' => env('SIMPLE_LIKES_CACHE_STATS_ALL_TTL', 60), // 1 minute

        // CP Widget TTLs (shorter for fresher admin data)
        'widget_overview_ttl' => env('SIMPLE_LIKES_WIDGET_OVERVIEW_TTL', 300), // 5 minutes
        'widget_popular_ttl' => env('SIMPLE_LIKES_WIDGET_POPULAR_TTL', 300), // 5 minutes
        'widget_activity_ttl' => env('SIMPLE_LIKES_WIDGET_ACTIVITY_TTL', 120), // 2 minutes
        'widget_top_users_ttl' => env('SIMPLE_LIKES_WIDGET_TOP_USERS_TTL', 300), // 5 minutes
    ],

    // Legacy support: 'cache_ttl' is deprecated, use 'cache.default_ttl' instead
    'cache_ttl' => env('SIMPLE_LIKES_CACHE_TTL', 1800),


    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting to prevent spam and bot attacks.
    | Adjust these values based on your site's traffic and needs.
    |
    */
    'rate_limiting' => [
        // Laravel throttle middleware limits (requests per minute per IP)
        'like_toggle_limit' => env('SIMPLE_LIKES_TOGGLE_LIMIT', 60),
        'stats_limit' => env('SIMPLE_LIKES_STATS_LIMIT', 120),
        'status_limit' => env('SIMPLE_LIKES_STATUS_LIMIT', 300), // Higher limit - called on every page load

        // Database-level spam protection
        'rapid_fire' => [
            'max_likes' => env('SIMPLE_LIKES_RAPID_FIRE_MAX', 10),
            'time_window' => env('SIMPLE_LIKES_RAPID_FIRE_WINDOW', 10), // seconds
        ],

        'user_limits' => [
            'max_likes_per_minute' => env('SIMPLE_LIKES_USER_MAX_PER_MINUTE', 30),
            'max_toggles_per_entry' => env('SIMPLE_LIKES_MAX_TOGGLES_PER_ENTRY', 10),
            'toggle_time_window' => env('SIMPLE_LIKES_TOGGLE_WINDOW', 60), // seconds
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Widget Configuration
    |--------------------------------------------------------------------------
    |
    | Configure widget display limits. Each widget is now separate and can be
    | added/removed individually from the dashboard.
    | Can be overridden via environment variables.
    |
    */
    'widget' => [
        'recent_activity_limit' => env('SIMPLE_LIKES_RECENT_ACTIVITY_LIMIT', 5),
        'popular_entries_limit' => env('SIMPLE_LIKES_POPULAR_ENTRIES_LIMIT', 5),
        'popular_entries_sort_by' => env('SIMPLE_LIKES_POPULAR_ENTRIES_SORT_BY', 'total'), // 'total' or 'real'
        'top_users_limit' => env('SIMPLE_LIKES_TOP_USERS_LIMIT', 5),
    ],
];
