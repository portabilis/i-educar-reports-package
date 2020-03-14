<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddBirthdaysReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999923)->firstOrFail()->getKey(),
            'title' => 'Relação de aniversariantes do mês',
            'description' => null,
            'link' => '/module/Reports/Birthdays',
            'order' => 0,
            'old' => 9998911,
            'process' => 9998911,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 9998911)->delete();
    }
}
