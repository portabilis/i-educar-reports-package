<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddLibraryLoanReceiptReportMenu extends Migration
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
            'title' => 'Comprovante de empréstimo',
            'description' => null,
            'link' => '/module/Reports/LibraryLoanReceipt',
            'order' => 1,
            'old' => 999620,
            'process' => 999620,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999620)->delete();
    }
}
