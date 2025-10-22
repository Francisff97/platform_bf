<?php

namespace App\Jobs;

use App\Services\CsvImporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Notification;
use App\Notifications\CsvImportFinished;

class CsvImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $entity,
        public string $file,
        public array $mapping,
        public ?int $userId = null
    ) {}

    public function handle(CsvImporter $importer): void
    {
        [$ok, $fail, $updated] = $importer->import($this->entity, $this->file, $this->mapping);
        // Notifica opzionale
        if ($this->userId && class_exists(CsvImportFinished::class)) {
            Notification::route('mail', optional(\App\Models\User::find($this->userId))->email)
                ->notify(new CsvImportFinished($this->entity, $ok, $updated, $fail));
        }
    }
}