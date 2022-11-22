<?php

use Illuminate\Database\Migrations\Migration;
use App\Menu;

class AddMinutesFinalResultReportMenu extends Migration
{
    public function up()
    {
        Menu::query()->updateOrCreate(['old' => 9998911], [
            'parent_id' => Menu::query()->where('old', 999925)->firstOrFail()->getKey(),
            'process' => 9998911,
            'title' => 'Ata Resultado final',
            'parent_old' => 999925,
            'link' => '/module/Reports/MinutesFinalResult'
        ]);
    }

    public function down()
    {
        Menu::query()->where('old', 9998911)->delete();
    }
}
