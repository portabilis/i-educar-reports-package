<?php

use Illuminate\Database\Migrations\Migration;
use App\Menu;

class AddStudentCardReportMenu extends Migration
{
    public function up()
    {
        Menu::query()->updateOrCreate([
            'old' => 999600,
        ], [
            'parent_id' => Menu::query()->where('old', 21127)->firstOrFail()->getKey(),
            'title' => 'Carteiras',
            'order' => 4,
            'parent_old' => 21127,
        ]);
        Menu::query()->updateOrCreate(['old' => 999602], [
            'parent_id' => Menu::query()->where('old', 999600)->firstOrFail()->getKey(),
            'process' => 999602,
            'title' => 'Carteira de estudante',
            'order' => 0,
            'parent_old' => 999600,
            'link' => '/module/Reports/StudentCard'
        ]);
    }

    public function down()
    {
        Menu::query()->where('old', 999602)->delete();
    }
}
