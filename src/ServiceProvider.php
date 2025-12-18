<?php

namespace Mikomagni\SimpleLikes;

use Statamic\Providers\AddonServiceProvider;
use Mikomagni\SimpleLikes\Fieldtypes\SimpleLikesFieldtype;
use Mikomagni\SimpleLikes\Tags\SimpleLike;
use Mikomagni\SimpleLikes\Console\Commands\WarmLikesCache;
use Mikomagni\SimpleLikes\Console\Commands\InstallSimpleLikes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Schema\Blueprint;
use Mikomagni\SimpleLikes\Http\CssAsset;

class ServiceProvider extends AddonServiceProvider
{
    protected $fieldtypes = [
        SimpleLikesFieldtype::class,
    ];

    protected $tags = [
        SimpleLike::class,
    ];

    protected $widgets = [
        \Mikomagni\SimpleLikes\Widgets\OverviewWidget::class,
        \Mikomagni\SimpleLikes\Widgets\RecentActivityWidget::class,
        \Mikomagni\SimpleLikes\Widgets\PopularEntriesWidget::class,
        \Mikomagni\SimpleLikes\Widgets\TopUsersWidget::class,
    ];

    protected $modifiers = [
        \Mikomagni\SimpleLikes\Modifiers\NumberFormat::class,
    ];

    protected $commands = [
        WarmLikesCache::class,
        InstallSimpleLikes::class,
    ];

    protected $routes = [
        'actions' => __DIR__.'/../routes/actions.php',
        'web' => __DIR__.'/../routes/web.php',
    ];

    protected $stylesheets = [];

    protected $vite = [
        'input' => [
            'resources/js/simple-likes-fieldtype.js'
        ],
        'publicDirectory' => 'resources/dist',
        'buildDirectory' => 'build',
    ];

    protected function bootAddonStyles()
    {
        if ($this->app->runningInConsole()) {
            return [];
        }

        return [
            'vendor/simple-likes/css/widgets.css'
        ];
    }

    public function bootAddon()
    {
        $this->publishes([
            __DIR__.'/../config/simple-likes.php' => config_path('simple-likes.php'),
        ], 'simple-likes-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/simple-likes'),
        ], 'simple-likes-views');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'simple-likes-migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/dist' => public_path('vendor/simple-likes'),
            ], 'simple-likes-assets');

            $this->publishes([
                __DIR__.'/../resources/css' => public_path('vendor/simple-likes/css'),
            ], 'simple-likes-css');

            $this->publishes([
                __DIR__.'/../resources/js/simple-likes.js' => public_path('vendor/simple-likes/js/simple-likes.js'),
                __DIR__.'/../resources/js/simple-likes-vanilla.js' => public_path('vendor/simple-likes/js/simple-likes-vanilla.js'),
            ], 'simple-likes-js');

            $this->publishes([
                __DIR__.'/../resources/css/simple-likes.css' => public_path('vendor/simple-likes/css/simple-likes.css'),
            ], 'simple-likes-frontend-css');
        }

        $this->bootSimpleLikes();
    }

    private function bootSimpleLikes()
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        try {
            $connection = config('simple-likes.connection');
            $schema = $connection ? Schema::connection($connection) : Schema::getFacadeRoot();

            if (config('simple-likes.auto_migrate', true) && !$schema->hasTable('simple_likes')) {
                $this->createSimpleLikesTable($connection);
            }
        } catch (\Exception $e) {
            if (function_exists('logger')) {
                logger()->debug('Simple Likes: Skipping auto-migration - ' . $e->getMessage());
            }
        }
    }

    private function createSimpleLikesTable(?string $connection = null)
    {
        $schema = $connection ? Schema::connection($connection) : Schema::getFacadeRoot();

        $schema->create('simple_likes', function (Blueprint $table) {
            $table->id();
            $table->string('entry_id')->index()
                ->comment('Statamic entry UUID');
            $table->string('user_id')->index()
                ->comment('User ID (authenticated) or "guest_" + IP hash (guest)');
            $table->string('user_type', 20)->default('authenticated')
                ->comment('Either "authenticated" or "guest"');
            $table->string('ip_hash', 64)->index()
                ->comment('SHA256 hash of IP address for abuse detection');
            $table->timestamps();

            $table->unique(['entry_id', 'user_id'], 'unique_entry_user_like');
            $table->index(['entry_id', 'created_at'], 'entry_created_index');
            $table->index('created_at', 'created_at_index');
            $table->index('user_type', 'user_type_index');
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/simple-likes.php', 'simple-likes');
    }

    public function boot()
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'simple-likes');

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'simple-likes');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/simple-likes'),
        ], 'simple-likes-lang');
        if (!$this->app->runningInConsole()) {
            try {
                $this->generateDynamicCss();
            } catch (\Exception $e) {
                if (function_exists('logger')) {
                    logger()->warning('Failed to generate Simple Likes CSS: ' . $e->getMessage());
                }
            }
        }

        $this->stylesheets = $this->bootAddonStyles();
    }

    protected function generateDynamicCss()
    {
        $cssPath = public_path('vendor/simple-likes/css/widgets.css');
        $cssDir = dirname($cssPath);
        $serviceProviderPath = __FILE__;
        $cssAssetPath = __DIR__ . '/Http/CssAsset.php';

        if (File::exists($cssPath)) {
            $cssTime = File::lastModified($cssPath);
            $serviceProviderTime = File::exists($serviceProviderPath) ? File::lastModified($serviceProviderPath) : 0;
            $cssAssetTime = File::exists($cssAssetPath) ? File::lastModified($cssAssetPath) : 0;
            
            $latestSourceTime = max($serviceProviderTime, $cssAssetTime);

            if ($cssTime >= $latestSourceTime) {
                return;
            }
        }

        if (!File::exists($cssDir)) {
            File::makeDirectory($cssDir, 0755, true);
        }

        $css = CssAsset::generateDynamicCss();

        File::put($cssPath, $css);
    }
}