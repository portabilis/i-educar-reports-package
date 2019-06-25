<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddServantsReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999914)->firstOrFail()->getKey(),
            'title' => 'Relatório cadastral de servidores',
            'description' => null,
            'link' => '/module/Reports/Servants',
            'order' => 0,
            'old' => 999820,
            'process' => 999820,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999820)->delete();
    }
}
