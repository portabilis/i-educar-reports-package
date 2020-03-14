<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddTransferenceCertificateReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999400)->firstOrFail()->getKey(),
            'title' => 'Atestado de transferência',
            'description' => null,
            'link' => '/module/Reports/TransferenceCertificate',
            'order' => 0,
            'old' => 999216,
            'process' => 999216,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999216)->delete();
    }
}
