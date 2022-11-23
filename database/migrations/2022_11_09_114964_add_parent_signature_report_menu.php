<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddParentSignatureReportMenu extends Migration
{
    public function up()
    {
        Menu::query()->updateOrCreate(['old' => 999870], [
            'parent_id' => Menu::query()->where('old', 999300)->firstOrFail()->getKey(),
            'process' => 999870,
            'title' => 'Lista de alunos para assinatura dos pais',
            'order' => 0,
            'parent_old' => 999300,
            'link' => '/module/Reports/ParentSignature'
        ]);
    }

    public function down()
    {
        Menu::query()->where('old', 999870)->delete();
    }
}
