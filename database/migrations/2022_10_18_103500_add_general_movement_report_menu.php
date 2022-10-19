<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddGeneralMovementReportMenu extends Migration
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
            'title' => 'Movimento geral',
            'description' => null,
            'link' => '/module/Reports/GeneralMovement',
            'order' => 0,
            'old' => 999301,
            'process' => 9998868,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 9998868)->delete();
    }
}
