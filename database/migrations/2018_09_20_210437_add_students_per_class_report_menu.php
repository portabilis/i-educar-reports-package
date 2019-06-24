<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddStudentsPerClassReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999923)->firstOrFail()->getKey(),
            'title' => 'Relatório de alunos por turma',
            'description' => null,
            'link' => '/module/Reports/StudentsPerClass',
            'order' => 0,
            'old' => 999101,
            'process' => 999101,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999101)->delete();
    }
}
