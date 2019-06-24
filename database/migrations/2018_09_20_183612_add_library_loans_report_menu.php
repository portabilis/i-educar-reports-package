<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddLibraryLoansReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999906)->firstOrFail()->getKey(),
            'title' => 'Relatório de empréstimos',
            'description' => null,
            'link' => '/module/Reports/LibraryLoans',
            'order' => 1,
            'old' => 999618,
            'process' => 999618,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999618)->delete();
    }
}
