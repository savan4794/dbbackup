<?php

namespace SavanRathod\DBBackup;

use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DBBackupManager
{
    protected $config;

    public function __construct()
    {
        $this->config = config('dbbackup');
    }

    public function backup()
    {
        $connection = $this->config['database']['default'];
        $settings = config("database.connections.{$connection}");

        if (!$settings) {
            throw new \Exception("Database connection [{$connection}] is not configured.");
        }

        $fileName = str_replace('{timestamp}', Carbon::now()->format('Y-m-d_H-i-s'), $this->config['file_name_format']);
        $backupPath = storage_path("app/temp_backups/{$fileName}");

        if (!is_dir(dirname($backupPath))) {
            mkdir(dirname($backupPath), 0777, true);
        }

        $command = $this->getDumpCommand($connection, $settings, $backupPath);
        if (!$command) {
            throw new \Exception("No dump command found for database connection: {$connection}");
        }
        $process = Process::fromShellCommandline($command);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            throw new \Exception('Database backup failed: ' . $exception->getMessage());
        }

        return $this->storeBackup($backupPath, $fileName);
    }

    protected function getDumpCommand($connection, $settings, $filePath)
    {
        $commands = [
            'mysql' => "mysqldump --host={$settings['host']} --port={$settings['port']} --user={$settings['username']} --password='{$settings['password']}' {$settings['database']} > {$filePath}",

            'pgsql' => "PGPASSWORD='{$settings['password']}' pg_dump --host={$settings['host']} --port={$settings['port']} --username={$settings['username']} --dbname={$settings['database']} --format=plain --no-owner > {$filePath}",

            'sqlite' => "sqlite3 {$settings['database']} .dump > {$filePath}",

            'sqlsrv' => "sqlcmd -S {$settings['host']},{$settings['port']} -U {$settings['username']} -P {$settings['password']} -Q \"BACKUP DATABASE [{$settings['database']}] TO DISK = '{$filePath}'\"",
        ];

        return $commands[$connection] ?? null;
    }

    protected function storeBackup($filePath, $fileName)
    {
        $disk = Storage::disk($this->config['storage']);
        $disk->put("db_backups/{$fileName}", file_get_contents($filePath));
        unlink($filePath);

        return $disk->url("db_backups/{$fileName}");
    }
}
