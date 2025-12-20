<?php

namespace Mikomagni\SimpleLikes\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Statamic\Console\RunsInPlease;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\error;
use function Laravel\Prompts\warning;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\table;

class InstallSimpleLikes extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:simple-likes:install
                            {--force : Force reinstall even if table exists}';

    protected $description = 'Install Simple Likes addon (creates database table and optionally publishes assets)';

    public function handle(): int
    {
        $this->newLine();
        $this->line('<fg=red>  ❤</> <options=bold>Simple Likes</>');
        $this->newLine();

        info('Installing...');

        if (!$this->checkDatabaseConnection()) {
            return self::FAILURE;
        }

        if (!$this->createTable()) {
            return self::FAILURE;
        }

        if (confirm(
            label: 'Would you like to publish the config file?',
            default: false,
            hint: 'Lets you customise templates, caching, and rate limiting.'
        )) {
            spin(
                callback: fn () => $this->callSilent('vendor:publish', [
                    '--tag' => 'simple-likes-config',
                    '--force' => $this->option('force'),
                ]),
                message: 'Publishing config file...'
            );
            info('Config published to config/simple-likes.php');
        }

        if (confirm(
            label: 'Would you like to publish the view templates?',
            default: false,
            hint: 'Lets you customise the like button templates.'
        )) {
            spin(
                callback: fn () => $this->callSilent('vendor:publish', [
                    '--tag' => 'simple-likes-views',
                    '--force' => $this->option('force'),
                ]),
                message: 'Publishing view templates...'
            );
            info('Views published to resources/views/vendor/simple-likes');
        }

        if (confirm(
            label: 'Would you like to publish the JavaScript files?',
            default: false,
            hint: 'Copies JS to public/vendor/simple-likes/js for manual inclusion.'
        )) {
            spin(
                callback: fn () => $this->callSilent('vendor:publish', [
                    '--tag' => 'simple-likes-js',
                    '--force' => $this->option('force'),
                ]),
                message: 'Publishing JavaScript files...'
            );
            info('JS published to public/vendor/simple-likes/js');
        }

        if (confirm(
            label: 'Would you like to publish the CSS files?',
            default: false,
            hint: 'Copies CSS to public/vendor/simple-likes/css for manual inclusion.'
        )) {
            spin(
                callback: fn () => $this->callSilent('vendor:publish', [
                    '--tag' => 'simple-likes-css',
                    '--force' => $this->option('force'),
                ]),
                message: 'Publishing CSS files...'
            );
            info('CSS published to public/vendor/simple-likes/css');
        }

        if (confirm(
            label: 'Would you like to publish the language files?',
            default: false,
            hint: 'Allows customising or translating Control Panel strings.'
        )) {
            spin(
                callback: fn () => $this->callSilent('vendor:publish', [
                    '--tag' => 'simple-likes-lang',
                    '--force' => $this->option('force'),
                ]),
                message: 'Publishing language files...'
            );
            info('Language files published to resources/lang/vendor/simple-likes');
        }

        $this->newLine();
        outro('Simple Likes installed successfully!');

        $this->newLine();
        info('Next Steps:');
        table(
            headers: ['Step', 'Action'],
            rows: [
                ['1', 'Add CSRF meta tag to your layout <head>:'],
                ['', '<meta name="csrf-token" content="{{ csrf_token }}">'],
                ['2', 'Add {{ simple_like }} to your templates'],
                ['3', 'Docs: https://simplelikes.com'],
            ]
        );

        return self::SUCCESS;
    }

    protected function checkDatabaseConnection(): bool
    {
        try {
            \Illuminate\Support\Facades\DB::connection()->getPDO();
            return true;
        } catch (\Exception $e) {
            error('Could not connect to database. Please check your .env configuration.');
            $this->newLine();
            note('Make sure DB_CONNECTION, DB_DATABASE, DB_USERNAME, and DB_PASSWORD are set correctly.');
            return false;
        }
    }

    protected function createTable(): bool
    {
        try {
            $connection = config('simple-likes.connection');
            $schema = $connection ? Schema::connection($connection) : Schema::getFacadeRoot();

            if ($schema->hasTable('simple_likes')) {
                if ($this->option('force')) {
                    warning('Dropping existing simple_likes table...');
                    $schema->dropIfExists('simple_likes');
                } else {
                    info('Database table already exists.');
                    return true;
                }
            }

            spin(
                callback: function () use ($schema) {
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
                },
                message: 'Creating database table...'
            );

            info('Database table created successfully.');
            return true;

        } catch (\Exception $e) {
            error('Failed to create database table: ' . $e->getMessage());
            return false;
        }
    }

}
