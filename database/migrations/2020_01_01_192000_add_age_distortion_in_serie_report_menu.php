<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddAgeDistortionInSerieReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999303)->firstOrFail()->getKey(),
            'title' => 'Gráfico de distorção idade/série',
            'description' => null,
            'link' => '/module/Reports/AgeDistortionInSerie',
            'order' => 0,
            'old' => 999840,
            'process' => 999840,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999840)->delete();
    }
}
