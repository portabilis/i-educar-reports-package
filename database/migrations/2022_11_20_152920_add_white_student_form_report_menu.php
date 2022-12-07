<?php

use Illuminate\Database\Migrations\Migration;
use App\Menu;

class AddWhiteStudentFormReportMenu extends Migration
{
    public function up()
    {
        Menu::query()->updateOrCreate(['old' => 999204], [
            'parent_id' => Menu::query()->where('old', 999861)->firstOrFail()->getKey(),
            'process' => 999204,
            'title' => 'Ficha do aluno em branco',
            'order' => 0,
            'parent_old' => 999861,
            'link' => '/module/Reports/WhiteStudentForm'
        ]);
    }

    public function down()
    {
        Menu::query()->where('old', 999204)->delete();
    }
}
