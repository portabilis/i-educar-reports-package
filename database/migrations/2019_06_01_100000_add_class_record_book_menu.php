<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddClassRecordBookMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999500)->firstOrFail()->getKey(),
            'title' => 'Diário de classe',
            'description' => null,
            'link' => '/module/Reports/ClassRecordBook',
            'order' => 0,
            'old' => 999816,
            'process' => 999816,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999816)->delete();
    }
}
