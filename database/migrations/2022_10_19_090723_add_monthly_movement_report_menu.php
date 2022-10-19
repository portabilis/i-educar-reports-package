<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddMonthlyMovementReportMenu extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999301)->firstOrFail()->getKey(),
            'title' => 'Relatório de Movimento Mensal',
            'description' => null,
            'link' => '/module/Reports/MonthlyMovement',
            'order' => 0,
            'old' => 999301,
            'process' => 9998862,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 9998862)->delete();
    }
}
