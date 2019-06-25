<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddLibraryPublishersReportMenu extends Migration
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
            'title' => 'Relatório de editoras',
            'description' => null,
            'link' => '/module/Reports/LibraryPublishers',
            'order' => 2,
            'old' => 999616,
            'process' => 999616,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999616)->delete();
    }
}
