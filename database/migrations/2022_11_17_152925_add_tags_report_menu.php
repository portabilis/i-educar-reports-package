<?php

use Illuminate\Database\Migrations\Migration;
use App\Menu;

class AddTagsReportMenu extends Migration
{
    public function up()
    {
        Menu::query()->updateOrCreate(['old' => 999235], [
            'parent_id' => Menu::query()->where('old', 999300)->firstOrFail()->getKey(),
            'process' => 999235,
            'title' => 'Relatório de etiquetas para mala direta',
            'order' => 0,
            'parent_old' => 999300,
            'link' => '/module/Reports/Tags'
        ]);
    }

    public function down()
    {
        Menu::query()->where('old', 999235)->delete();
    }
}
