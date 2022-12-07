<?php

use Illuminate\Database\Migrations\Migration;
use App\Menu;

class AddClassBoardMapReportMenu extends Migration
{
    public function up()
    {
        Menu::query()->updateOrCreate(['old' => 999609], [
            'parent_id' => Menu::query()->where('old', 999925)->firstOrFail()->getKey(),
            'process' => 999609,
            'title' => 'Mapa do conselho de classe',
            'order' => 0,
            'parent_old' => 999925,
            'link' => '/module/Reports/ClassBoardMap'
        ]);
    }

    public function down()
    {
        Menu::query()->where('old', 999609)->delete();
    }
}
