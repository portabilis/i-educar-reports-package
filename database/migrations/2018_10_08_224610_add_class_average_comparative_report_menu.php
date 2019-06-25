<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddClassAverageComparativeReportMenu extends Migration
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
            'title' => 'Comparativo de média da turma',
            'description' => null,
            'link' => '/module/Reports/ClassAverageComparative',
            'order' => 0,
            'old' => 999872,
            'process' => 999872,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999872)->delete();
    }
}
