<?php

namespace App\Console\Commands;

use App\Http\Controllers\FixedAssetController;
use App\Models\FixedAsset;
use Illuminate\Console\Command;

class DepreciateAssets extends Command
{
    /**
     * @var string
     */
    protected $signature = 'assets:depreciate {--period= : Period YYYY-MM (defaults to current month)} {--institution= : Limit to one institution}';

    /**
     * @var string
     */
    protected $description = 'Post the period\'s depreciation for all due active fixed assets (idempotent).';

    public function handle()
    {
        $period = $this->option('period') ?: now()->format('Y-m');
        $institution = $this->option('institution');

        $this->info("Depreciating assets for period $period");

        $query = FixedAsset::where('status', 'Active');
        if ($institution) {
            $query->where('institution_id', $institution);
        }

        $engine = app(FixedAssetController::class);
        $posted = 0;
        $total = 0;
        $skipped = 0;

        foreach ($query->cursor() as $asset) {
            $result = $engine->depreciateAsset($asset, $period);
            if (!empty($result['posted'])) {
                $posted++;
                $total += $result['amount'];
            } else {
                $skipped++;
            }
        }

        $this->info("Done. Depreciated: $posted | skipped: $skipped | total expense: " . number_format($total, 2));
        return Command::SUCCESS;
    }
}
