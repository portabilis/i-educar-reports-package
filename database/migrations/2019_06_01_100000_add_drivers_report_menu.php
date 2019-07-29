<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddDriversReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 9998847)->firstOrFail()->getKey(),
            'title' => 'Relatório de motoristas do transporte',
            'description' => null,
            'link' => '/module/Reports/Drivers',
            'order' => 0,
            'old' => 21252,
            'process' => 21252,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 21252)->delete();
    }
}
