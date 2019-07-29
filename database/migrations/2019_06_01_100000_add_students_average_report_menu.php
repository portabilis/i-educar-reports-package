<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddStudentsAverageReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999303)->firstOrFail()->getKey(),
            'title' => 'Relatório de alunos com o melhor desempenho',
            'description' => null,
            'link' => '/module/Reports/StudentsAverage',
            'order' => 0,
            'old' => 999834,
            'process' => 999834,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999834)->delete();
    }
}
