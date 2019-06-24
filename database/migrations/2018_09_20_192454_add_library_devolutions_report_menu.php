<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddLibraryDevolutionsReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999906)->firstOrFail()->getKey(),
            'title' => 'Relatório de devoluções',
            'description' => null,
            'link' => '/module/Reports/LibraryDevolutions',
            'order' => 2,
            'old' => 999619,
            'process' => 999619,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999619)->delete();
    }
}
