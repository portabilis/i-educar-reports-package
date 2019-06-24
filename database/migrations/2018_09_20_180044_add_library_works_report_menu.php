<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddLibraryWorksReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999905)->firstOrFail()->getKey(),
            'title' => 'Relatório de obras',
            'description' => null,
            'link' => '/module/Reports/LibraryWorks',
            'order' => 3,
            'old' => 999617,
            'process' => 999617,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999617)->delete();
    }
}
