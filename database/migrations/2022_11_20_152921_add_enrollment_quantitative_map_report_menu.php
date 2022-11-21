<?php

use Illuminate\Database\Migrations\Migration;
use App\Menu;

class AddEnrollmentQuantitativeMapReportMenu extends Migration
{
    public function up()
    {
        Menu::query()->updateOrCreate(['old' => 999218], [
            'parent_id' => Menu::query()->where('old', 999923)->firstOrFail()->getKey(),
            'process' => 999218,
            'title' => 'Mapa quantitativo de matrículas enturmadas',
            'order' => 0,
            'parent_old' => 999923,
            'link' => '/module/Reports/EnrollmentQuantitativeMap'
        ]);
    }

    public function down()
    {
        Menu::query()->where('old', 999218)->delete();
    }
}
