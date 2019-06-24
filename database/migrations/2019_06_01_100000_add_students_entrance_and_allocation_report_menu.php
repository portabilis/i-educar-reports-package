<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddStudentsEntranceAndAllocationReportMenu extends Migration
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
            'title' => 'Relatório de alunos por data de entrada e enturmação',
            'description' => null,
            'link' => '/module/Reports/StudentsEntranceAndAllocation',
            'order' => 0,
            'old' => 999871,
            'process' => 999871,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999871)->delete();
    }
}
