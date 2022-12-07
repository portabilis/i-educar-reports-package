<?php

use Illuminate\Database\Migrations\Migration;
use App\Menu;

class AddEmployeeAllocatedTimeReportMenu extends Migration
{
    public function up()
    {
        Menu::query()->updateOrCreate(['old' => 999107], [
            'parent_id' => Menu::query()->where('old', 999914)->firstOrFail()->getKey(),
            'process' => 999107,
            'title' => 'Horas alocadas por servidor',
            'order' => 0,
            'parent_old' => 999914,
            'link' => '/module/Reports/EmployeeAllocatedTime'
        ]);
    }

    public function down()
    {
        Menu::query()->where('old', 999107)->delete();
    }
}
