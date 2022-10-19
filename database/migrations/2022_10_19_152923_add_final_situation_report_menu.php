<?php

use App\Support\Database\AsView;
use Illuminate\Database\Migrations\Migration;
use App\Menu;

class AddFinalSituationReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999301)->firstOrFail()->getKey(),
            'title' => 'Quadro de Situação Final',
            'description' => null,
            'link' => '/module/Reports/FinalSituation',
            'order' => 0,
            'old' => 999301,
            'process' => 999882,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999882)->delete();
    }
}
