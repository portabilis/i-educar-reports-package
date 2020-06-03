<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddStudentSheetReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999861)->firstOrFail()->getKey(),
            'title' => 'Ficha do aluno',
            'description' => null,
            'link' => '/module/Reports/StudentSheet',
            'order' => 0,
            'old' => 999203,
            'process' => 999203,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999203)->delete();
    }
}
