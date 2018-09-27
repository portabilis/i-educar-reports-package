<?php

use Phinx\Migration\AbstractMigration;

class AddDriversReportMenu extends AbstractMigration
{
    public function up()
    {
        $this->execute(
            '
                INSERT INTO portal.menu_submenu (cod_menu_submenu, ref_cod_menu_menu, cod_sistema, nm_submenu, arquivo, title, nivel) 
                VALUES (21252, 69, 2, \'Relatório de motoristas do transporte\', \'module/Reports/Drivers\', null, 3);
                
                INSERT INTO pmicontrolesis.menu (cod_menu, ref_cod_menu_submenu, ref_cod_menu_pai, tt_menu, ord_menu, caminho, alvo, suprime_menu, ref_cod_tutormenu, ref_cod_ico, tipo_menu) 
                VALUES (21252, 21252, 9998847, \'Relatório de motoristas do transporte\', 0, \'module/Reports/Drivers\', \'_self\', 1, 17, 192, null);
                
                INSERT INTO pmieducar.menu_tipo_usuario (ref_cod_tipo_usuario, ref_cod_menu_submenu, cadastra, visualiza, exclui) 
                VALUES (1, 21252, 1, 1, 1);
            '
        );
    }

    public function down()
    {
        $this->execute(
            '
                DELETE FROM pmieducar.menu_tipo_usuario 
                WHERE ref_cod_menu_submenu = 21252;

                DELETE FROM pmicontrolesis.menu 
                WHERE cod_menu = 21252;

                DELETE FROM portal.menu_submenu 
                WHERE cod_menu_submenu = 21252;
            '
        );
    }
}
