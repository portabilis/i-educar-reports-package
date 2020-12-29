<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;

class AddRegistrationCertificateReportMenu extends Migration
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
            'title' => 'Atestado de matrícula',
            'description' => null,
            'link' => '/module/Reports/RegistrationCertificate',
            'order' => 0,
            'old' => 999103,
            'process' => 999103,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('process', 999103)->delete();
    }
}
