<?php

use Illuminate\Database\Migrations\Migration;
use App\Menu;

class AddUserAccessReportMenu extends Migration
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

        Menu::query()->updateOrCreate(['old' => 999223], [
            'parent_id' => Menu::query()->where('old', 999827)->firstOrFail()->getKey(),
            'process' => 999223,
            'title' => 'Relatório de usuários e acessos',
            'order' => 1,
            'parent_old' => 999827,
            'link' => '/module/Reports/UserAccess'
        ]);
    }

    public function down()
    {
        Menu::query()->where('old', 999223)->delete();
    }
}
