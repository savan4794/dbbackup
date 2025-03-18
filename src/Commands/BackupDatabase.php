<?php

namespace SavanRathod\DBBackup\Commands;

use Illuminate\Console\Command;
use SavanRathod\DBBackup\DBBackupManager;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Take a database backup and store it in the configured file system.';

    public function handle()
    {
        try {
            $manager = new DBBackupManager();
            $url = $manager->backup();
            $this->info("Backup successful! File URL: $url");
        } catch (\Exception $e) {
            $this->error("Backup failed: " . $e->getMessage());
        }
    }
}
