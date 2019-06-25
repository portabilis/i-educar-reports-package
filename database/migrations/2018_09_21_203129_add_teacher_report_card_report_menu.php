<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddTeacherReportCardReportMenu extends Migration
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
            'title' => 'Boletim do professor',
            'description' => null,
            'link' => '/module/Reports/TeacherReportCard',
            'order' => 0,
            'old' => 999205,
            'process' => 999205,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999205)->delete();
    }
}
