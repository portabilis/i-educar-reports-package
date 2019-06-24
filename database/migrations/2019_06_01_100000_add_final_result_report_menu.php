<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddFinalResultReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999925)->firstOrFail()->getKey(),
            'title' => 'Resultado final',
            'description' => null,
            'link' => '/module/Reports/FinalResult',
            'order' => 0,
            'old' => 999608,
            'process' => 999608,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999608)->delete();
    }
}
