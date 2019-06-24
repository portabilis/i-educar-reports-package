<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddVacancyCertificateReportMenu extends Migration
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
            'title' => 'Atestado de vaga',
            'description' => null,
            'link' => '/module/Reports/VacancyCertificate',
            'order' => 0,
            'old' => 999100,
            'process' => 999100,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999100)->delete();
    }
}
