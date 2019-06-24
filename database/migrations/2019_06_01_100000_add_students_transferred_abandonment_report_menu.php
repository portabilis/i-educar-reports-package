<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddStudentsTransferredAbandonmentReportMenu extends Migration
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
            'title' => 'Relatório de alunos transferidos/abandono',
            'description' => null,
            'link' => '/module/Reports/StudentsTransferredAbandonment',
            'order' => 0,
            'old' => 999607,
            'process' => 999607,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999607)->delete();
    }
}
