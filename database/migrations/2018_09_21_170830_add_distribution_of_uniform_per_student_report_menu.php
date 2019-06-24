<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddDistributionOfUniformPerStudentReportMenu extends Migration
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
            'title' => 'Distribuição de uniforme por aluno',
            'description' => null,
            'link' => '/module/Reports/DistributionOfUniformPerStudent',
            'order' => 0,
            'old' => 999224,
            'process' => 999224,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999224)->delete();
    }
}
