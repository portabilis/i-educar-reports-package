<?php

use Illuminate\Database\Migrations\Migration;
use App\Menu;

class AddUserAccessGraphicReportMenu extends Migration
{
    public function up()
    {
        Menu::query()->updateOrCreate([
            'old' => 999827,
        ], [
            'parent_id' => Menu::query()->where('old', 21126)->firstOrFail()->getKey(),
            'title' => 'Gerenciais',
            'order' => 0,
            'parent_old' => 21126,
        ]);

        Menu::query()->updateOrCreate(['old' => 999244], [
            'parent_id' => Menu::query()->where('old', 999827)->firstOrFail()->getKey(),
            'process' => 999244,
            'title' => 'Gráfico de usuários e acessos',
            'order' => 0,
            'parent_old' => 999827,
            'link' => '/module/Reports/UserAccessGraphic',
        ]);
    }

    public function down()
    {
        Menu::query()->where('process', 999244)->delete();
    }
}
