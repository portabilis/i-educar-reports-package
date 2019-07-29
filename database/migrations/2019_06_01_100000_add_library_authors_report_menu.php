<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddLibraryAuthorsReportMenu extends Migration
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
            'title' => 'Relatório de autores',
            'link' => '/module/Reports/LibraryAuthors',
            'order' => 1,
            'parent_old' => 999905,
            'old' => 999615,
            'process' => 999615,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999615)->delete();
    }
}
