// app/Jobs/Seo/BackfillMedia.php
namespace App\Jobs\Seo;

use App\Support\Seo\Ingestor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BackfillMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout   = 300; // lo scan di /public può essere lungo
    public $tries     = 1;
    public $backoff   = 5;

    public function handle(): void
    {
        Ingestor::syncMedia();
    }
}
