<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddReportCardReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999450)->firstOrFail()->getKey(),
            'title' => 'Boletim escolar',
            'description' => null,
            'link' => '/module/Reports/ReportCard',
            'order' => 0,
            'old' => 999202,
            'process' => 999202,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999202)->delete();
    }
}
