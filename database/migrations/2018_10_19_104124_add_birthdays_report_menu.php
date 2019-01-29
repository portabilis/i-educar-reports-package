<?php

use Illuminate\Database\Migrations\Migration;

class AddBirthdaysReportMenu extends Migration
{
    public function up()
    {
        DB::unprepared(
            '
                INSERT INTO portal.menu_submenu (cod_menu_submenu, ref_cod_menu_menu, cod_sistema, nm_submenu, arquivo, title, nivel) 
                VALUES (9998911, 55, 2, \'Relação de aniversariantes do mês\', \'module/Reports/Birthdays\', null, 3);
                
                INSERT INTO pmicontrolesis.menu (cod_menu, ref_cod_menu_submenu, ref_cod_menu_pai, tt_menu, ord_menu, caminho, alvo, suprime_menu, ref_cod_tutormenu, ref_cod_ico, tipo_menu) 
                VALUES (9998911, 9998911, 999923, \'Relação de aniversariantes do mês\', 0, \'module/Reports/Birthdays\', \'_self\', 1, 15, 155, 2);
                
                INSERT INTO pmieducar.menu_tipo_usuario (ref_cod_tipo_usuario, ref_cod_menu_submenu, cadastra, visualiza, exclui) 
                VALUES (1, 9998911, 1, 0, 1);
                
                INSERT INTO portal.menu_funcionario (ref_ref_cod_pessoa_fj, cadastra, exclui, ref_cod_menu_submenu) 
                VALUES (1, 0, 0, 9998911);
            '
        );
    }

    public function down()
    {
        $this->execute(
            '
                DELETE FROM portal.menu_funcionario 
                WHERE ref_cod_menu_submenu = 9998911;
                
                DELETE FROM pmieducar.menu_tipo_usuario 
                WHERE ref_cod_menu_submenu = 9998911;

                DELETE FROM pmicontrolesis.menu 
                WHERE cod_menu = 9998911;

                DELETE FROM portal.menu_submenu 
                WHERE cod_menu_submenu = 9998911;
            '
        );
    }
}
