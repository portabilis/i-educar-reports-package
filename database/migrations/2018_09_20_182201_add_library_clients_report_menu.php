<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddLibraryClientsReportMenu extends Migration
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
            'title' => 'Relatório de clientes',
            'description' => null,
            'link' => '/module/Reports/LibraryClients',
            'order' => 4,
            'old' => 999845,
            'process' => 999845,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999845)->delete();
    }
}
