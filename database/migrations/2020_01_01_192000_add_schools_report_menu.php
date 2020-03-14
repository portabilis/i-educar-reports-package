<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddSchoolsReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999300)->firstOrFail()->getKey(),
            'title' => 'Relatório geral de escolas',
            'description' => null,
            'link' => '/module/Reports/Schools',
            'order' => 0,
            'old' => 999605,
            'process' => 999605,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999605)->delete();
    }
}
