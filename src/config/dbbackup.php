<?php

return [
    'storage' => env('DB_BACKUP_STORAGE', 'local'),

    'local' => [
        'path' => storage_path('app/db_backups'),
    ],

    's3' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'bucket' => env('AWS_BUCKET'),
    ],

    'file_name_format' => 'db_backup_{timestamp}.sql',

    'database' => [
        'default' => env('DB_CONNECTION', 'mysql'),
        'connections' => [
            'mysql' => [
                'dump_command' => "mysqldump --host={host} --port={port} --user={username} --password='{password}' {database} > {file}",
            ],
            'pgsql' => [
                'dump_command' => "PGPASSWORD='{password}' pg_dump --host={host} --port={port} --username={username} --dbname={database} --format=plain --no-owner > {file}",
            ],
            'sqlite' => [
                'dump_command' => "sqlite3 {database} .dump > {file}",
            ],
            'sqlsrv' => [
                'dump_command' => "sqlcmd -S {host},{port} -U {username} -P {password} -Q \"BACKUP DATABASE [{database}] TO DISK = '{file}'\"",
            ],
        ],
    ],
];
