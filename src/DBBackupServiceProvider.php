<?php
namespace SavanRathod\DBBackup;


use Illuminate\Support\ServiceProvider;
use SavanRathod\DBBackup\Commands\BackupDatabase;

class DBBackupServiceProvider extends ServiceProvider{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/dbbackup.php', 'dbbackup');
    }
    public function boot()
    {
        \Log::info("come here");
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/dbbackup.php' => config_path('dbbackup.php'),
            ], 'config');

            $this->commands([
                BackupDatabase::class,
            ]);
        }
    }
}
