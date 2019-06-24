<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddStudentsMovementReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999301)->firstOrFail()->getKey(),
            'title' => 'Movimento de alunos e enturmações',
            'description' => null,
            'link' => '/module/Reports/StudentsMovement',
            'order' => 0,
            'old' => 999201,
            'process' => 999201,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999201)->delete();
    }
}
