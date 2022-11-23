<?php

use Illuminate\Database\Migrations\Migration;
use App\Menu;

class AddNotEnrollmentReportMenu extends Migration
{
    public function up()
    {
        Menu::query()->updateOrCreate(['old' => 999108], [
            'parent_id' => Menu::query()->where('old', 999923)->firstOrFail()->getKey(),
            'process' => 999108,
            'title' => 'Relatório de alunos não enturmados por escola',
            'order' => 0,
            'parent_old' => 999923,
            'link' => '/module/Reports/NotEnrollment'
        ]);
    }

    public function down()
    {
        Menu::query()->where('old', 999108)->delete();
    }
}
