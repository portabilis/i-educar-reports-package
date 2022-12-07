<?php

use Illuminate\Database\Migrations\Migration;
use App\Menu;

class AddFinalMapByDisciplineReportMenu extends Migration
{
    public function up()
    {
        Menu::query()->updateOrCreate(['old' => 999886], [
            'parent_id' => Menu::query()->where('old', 999925)->firstOrFail()->getKey(),
            'process' => 999886,
            'title' => 'Mapa final por disciplina',
            'order' => 6,
            'parent_old' => 999925,
            'link' => '/module/Reports/FinalMapByDiscipline'
        ]);
    }

    public function down()
    {
        Menu::query()->where('old', 999886)->delete();
    }
}
