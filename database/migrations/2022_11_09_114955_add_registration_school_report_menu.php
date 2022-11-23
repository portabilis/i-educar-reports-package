<?php

use Illuminate\Database\Migrations\Migration;
use App\Menu;

class AddRegistrationSchoolReportMenu extends Migration
{
    public function up()
    {
        Menu::query()->updateOrCreate(['old' => 999105], [
            'parent_id' => Menu::query()->where('old', 999923)->firstOrFail()->getKey(),
            'process' => 999105,
            'title' => 'Relatório de matrículas de alunos por escola',
            'order' => 0,
            'parent_old' => 999923,
            'link' => '/module/Reports/RegistrationSchool'
        ]);
    }

    public function down()
    {
        Menu::query()->where('old', 999105)->delete();
    }
}
