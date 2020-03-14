<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddLibraryDevolutionReceiptReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999907)->firstOrFail()->getKey(),
            'title' => 'Comprovante de devolução',
            'description' => null,
            'link' => '/module/Reports/LibraryDevolutionReceipt',
            'order' => 2,
            'old' => 999621,
            'process' => 999621,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999621)->delete();
    }
}
