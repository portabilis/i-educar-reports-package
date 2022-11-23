<?php

use Illuminate\Database\Migrations\Migration;
use App\Menu;

class AddSchoolingCertificateReportMenu extends Migration
{
    public function up()
    {
        Menu::query()->updateOrCreate(['old' => 999810], [
            'parent_id' => Menu::query()->where('old', 999400)->firstOrFail()->getKey(),
            'process' => 999810,
            'title' => 'Atestado de escolaridade',
            'order' => 2,
            'parent_old' => 999400,
            'link' => '/module/Reports/SchoolingCertificate'
        ]);
    }

    public function down()
    {
        Menu::query()->where('old', 999810)->delete();
    }
}
