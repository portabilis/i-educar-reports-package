<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddTeachersAndCoursesTaughtByClassReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999914)->firstOrFail()->getKey(),
            'title' => 'Relatório de docentes e disciplinas lecionadas por turma',
            'description' => null,
            'link' => '/module/Reports/StudentsWithDisabilities',
            'order' => 0,
            'old' => 999860,
            'process' => 999860,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999860)->delete();
    }
}
