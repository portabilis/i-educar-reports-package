<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddTransportationUsersReportMenu extends Migration
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
            'title' => 'Relatório de usuários do transporte',
            'description' => null,
            'link' => '/module/Reports/TransportationUsers',
            'order' => 0,
            'old' => 999825,
            'process' => 999825,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999825)->delete();
    }
}
