<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddScoreAbsenceReleaseReportMenu extends Migration
{
    public function up()
    {
        Menu::query()->updateOrCreate(['old' => 999231], [
            'parent_id' => Menu::query()->where('old', 999922)->firstOrFail()->getKey(),
            'process' => 999231,
            'title' => 'Relatório de notas e faltas lançadas',
            'order' => 0,
            'parent_old' => 999922,
            'link' => '/module/Reports/ScoreAbsenceRelease'
        ]);
    }

    public function down()
    {
        Menu::query()->where('old', 999231)->delete();
    }
}
