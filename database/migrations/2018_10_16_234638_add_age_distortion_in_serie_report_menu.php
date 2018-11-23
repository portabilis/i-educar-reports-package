<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddAgeDistortionInSerieReportMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(
            '
                INSERT INTO portal.menu_submenu (cod_menu_submenu, ref_cod_menu_menu, cod_sistema, nm_submenu, arquivo, title, nivel) 
                VALUES (999840, 55, 2, \'Gráfico de distorção idade/série\', \'module/Reports/AgeDistortionInSerie\', null, 3);
                
                INSERT INTO pmicontrolesis.menu (cod_menu, ref_cod_menu_submenu, ref_cod_menu_pai, tt_menu, ord_menu, caminho, alvo, suprime_menu, ref_cod_tutormenu, ref_cod_ico, tipo_menu) 
                VALUES (999840, 999840, 999303, \'Gráfico de distorção idade/série\', 0, \'module/Reports/AgeDistortionInSerie\', \'_self\', 1, 15, 192, 1);
                
                INSERT INTO pmieducar.menu_tipo_usuario (ref_cod_tipo_usuario, ref_cod_menu_submenu, cadastra, visualiza, exclui) 
                VALUES (1, 999840, 1, 1, 1);
            '
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared(
            '
                DELETE FROM pmieducar.menu_tipo_usuario 
                WHERE ref_cod_menu_submenu = 999840;

                DELETE FROM pmicontrolesis.menu 
                WHERE cod_menu = 999840;

                DELETE FROM portal.menu_submenu 
                WHERE cod_menu_submenu = 999840;
            '
        );
    }
}
