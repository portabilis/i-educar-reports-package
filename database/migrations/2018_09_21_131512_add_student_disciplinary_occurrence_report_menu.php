<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddStudentDisciplinaryOccurrenceReportMenu extends Migration
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
            'title' => 'Relatório de ocorrências disciplinares por aluno',
            'description' => null,
            'link' => '/module/Reports/StudentDisciplinaryOccurrence',
            'order' => 0,
            'old' => 999217,
            'process' => 999217,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999217)->delete();
    }
}
