<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddClassRecordBookMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("INSERT INTO portal.menu_submenu VALUES (999816, 55, 2, 'Diário de classe', 'module/Reports/ClassRecordBook', '', 3);");
        DB::unprepared("INSERT INTO pmicontrolesis.menu VALUES (999816, 999816, 999500, 'Diário de classe', 0, 'module/Reports/ClassRecordBook', '_self', 1, 15, 192, null);");
        DB::unprepared("INSERT INTO pmieducar.menu_tipo_usuario VALUES (1, 999816, 1, 1, 1);");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DELETE FROM pmieducar.menu_tipo_usuario WHERE ref_cod_menu_submenu = 999816;");
        DB::unprepared("DELETE FROM pmicontrolesis.menu WHERE cod_menu = 999816;");
        DB::unprepared("DELETE FROM portal.menu_submenu WHERE cod_menu_submenu = 999816;");
    }
}
