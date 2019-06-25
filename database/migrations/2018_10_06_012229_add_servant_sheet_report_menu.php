<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddServantSheetReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999916)->firstOrFail()->getKey(),
            'title' => 'Ficha do Servidor',
            'description' => null,
            'link' => '/module/Reports/ServantSheet',
            'order' => 0,
            'old' => 999822,
            'process' => 999822,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999822)->delete();
    }
}
