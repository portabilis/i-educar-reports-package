<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddConferenceEvaluationsFaultsReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999922)->firstOrFail()->getKey(),
            'title' => 'Relatório de conferência de notas e faltas',
            'description' => null,
            'link' => '/module/Reports/ConferenceEvaluationsFaults',
            'order' => 0,
            'old' => 999809,
            'process' => 999809,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999809)->delete();
    }
}
