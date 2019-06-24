<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddFrequencyCertificateReportMenu extends Migration
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
            'title' => 'Atestado de frequência',
            'description' => null,
            'link' => '/module/Reports/FrequencyCertificate',
            'order' => 0,
            'old' => 999102,
            'process' => 999102,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999102)->delete();
    }
}
