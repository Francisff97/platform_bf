// app/Jobs/Seo/BackfillPages.php
namespace App\Jobs\Seo;

use App\Support\Seo\Ingestor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BackfillPages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout   = 120;   // opzionale
    public $tries     = 1;     // opzionale
    public $backoff   = 5;     // opzionale

    public function handle(): void
    {
        Ingestor::syncPages();
    }
}
