<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddStudentsWithBenefitsReportMenu extends Migration
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
            'title' => 'Relatório de alunos que recebem benefícios',
            'description' => null,
            'link' => '/module/Reports/StudentsWithBenefits',
            'order' => 0,
            'old' => 999233,
            'process' => 999233,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999233)->delete();
    }
}
