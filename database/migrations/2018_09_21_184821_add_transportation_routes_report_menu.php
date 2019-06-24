<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddTransportationRoutesReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 9998847)->firstOrFail()->getKey(),
            'title' => 'Relatório de rotas do transporte',
            'description' => null,
            'link' => '/module/Reports/TransportationRoutes',
            'order' => 0,
            'old' => 21242,
            'process' => 21242,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 21242)->delete();
    }
}
