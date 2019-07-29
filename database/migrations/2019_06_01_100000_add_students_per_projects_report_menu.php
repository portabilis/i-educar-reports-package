<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddStudentsPerProjectsReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999300)->firstOrFail()->getKey(),
            'title' => 'Relatório de alunos participantes de projetos',
            'description' => null,
            'link' => '/module/Reports/StudentsPerProjects',
            'order' => 0,
            'old' => 999234,
            'process' => 999234,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999234)->delete();
    }
}
