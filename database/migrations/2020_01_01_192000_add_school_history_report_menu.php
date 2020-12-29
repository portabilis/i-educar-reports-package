<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddSchoolHistoryReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999460)->firstOrFail()->getKey(),
            'title' => 'Histórico escolar',
            'description' => null,
            'link' => '/module/Reports/SchoolHistory',
            'order' => 0,
            'old' => 999200,
            'process' => 999200,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999200)->delete();
    }
}
