<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddEducationalProgressAndProceduresReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999303)->firstOrFail()->getKey(),
            'title' => 'Rendimento e movimento escolar',
            'description' => null,
            'link' => '/module/Reports/EducationalProgressAndProcedures',
            'order' => 0,
            'old' => 999830,
            'process' => 999830,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999830)->delete();
    }
}
