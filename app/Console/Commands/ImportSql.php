<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportSql extends Command
{
    protected $signature = 'db:import-sql {file=database/sql/edutec.sql} {--fresh : vide la base avant l\'import}';

    protected $description = 'Importe un fichier SQL dans la base configurée dans .env';

    public function handle(): int
    {
        $path = base_path($this->argument('file'));

        if (!is_file($path)) {
            $this->error("Fichier introuvable : $path");
            return self::FAILURE;
        }

        $db = DB::connection()->getDatabaseName();

        if ($this->option('fresh')) {
            if (!$this->confirm("Toutes les tables de la base « $db » vont être supprimées. Continuer ?")) {
                return self::FAILURE;
            }
            $this->call('db:wipe', ['--force' => true]);
        }

        DB::unprepared(file_get_contents($path));

        $this->info("Import terminé dans la base « $db ».");
        return self::SUCCESS;
    }
}
