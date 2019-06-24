<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddStudentsWithDisabilitiesReportMenu extends Migration
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
            'title' => 'Relatório de alunos com deficiência',
            'description' => null,
            'link' => '/module/Reports/StudentsWithDisabilities',
            'order' => 0,
            'old' => 999227,
            'process' => 999227,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999227)->delete();
    }
}
