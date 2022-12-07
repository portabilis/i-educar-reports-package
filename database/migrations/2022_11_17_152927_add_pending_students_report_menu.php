<?php

use Illuminate\Database\Migrations\Migration;
use App\Menu;

class AddPendingStudentsReportMenu extends Migration
{
    public function up()
    {
        Menu::query()->updateOrCreate(['old' => 999883], [
            'parent_id' => Menu::query()->where('old', 999303)->firstOrFail()->getKey(),
            'process' => 999883,
            'title' => 'Quantitativo de alunos sem nota',
            'order' => 0,
            'parent_old' => 999303,
            'link' => '/module/Reports/PendingStudents'
        ]);
    }

    public function down()
    {
        Menu::query()->where('old', 999883)->delete();
    }
}
