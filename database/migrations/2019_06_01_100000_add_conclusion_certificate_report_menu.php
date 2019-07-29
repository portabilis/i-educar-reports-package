<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddConclusionCertificateReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999400)->firstOrFail()->getKey(),
            'title' => 'Declaração de conclusão de curso',
            'description' => null,
            'link' => '/module/Reports/ConclusionCertificate',
            'order' => 0,
            'old' => 999812,
            'process' => 999812,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999812)->delete();
    }
}
