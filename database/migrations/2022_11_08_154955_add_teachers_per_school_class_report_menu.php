<?php

use Illuminate\Database\Migrations\Migration;
use App\Menu;

class AddTeachersPerSchoolClassReportMenu extends Migration
{
    public function up()
    {
        Menu::query()->updateOrCreate([
            'old' => 999915,
        ], [
            'parent_id' => Menu::query()->where('old', 999913)->firstOrFail()->getKey(),
            'title' => 'Indicadores',
            'order' => 2,
            'parent_old' => 999913,
        ]);

        Menu::query()->updateOrCreate(['old' => 999859], [
            'parent_id' => Menu::query()->where('old', 999915)->firstOrFail()->getKey(),
            'process' => 999859,
            'title' => 'Quantitativo de docentes por turma',
            'order' => 0,
            'parent_old' => 999915,
            'link' => '/module/Reports/TeachersPerSchoolClass'
        ]);
    }

    public function down()
    {
        Menu::query()->where('old', 999859)->delete();
    }
}
