<?php

namespace iEducar\Community\Reports\Commands;

use Illuminate\Console\Command;

class CommunityReportsLinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'community:reports:link';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a symbol link to reports package';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $source = __DIR__ . '/../../ieducar';
        $target = base_path('ieducar/modules/Reports');

        if (is_link($target)) {
            unlink($target);
        }

        symlink($source, $target);

        $this->info("Symbol link created in: {$target}");
    }
}
