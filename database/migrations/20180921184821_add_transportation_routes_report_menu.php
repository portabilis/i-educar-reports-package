<?php

use Phinx\Migration\AbstractMigration;

class AddTransportationRoutesReportMenu extends AbstractMigration
{
    public function up()
    {
        $this->execute(
            '
                INSERT INTO portal.menu_submenu (cod_menu_submenu, ref_cod_menu_menu, cod_sistema, nm_submenu, arquivo, title, nivel) 
                VALUES (21242, 69, 2, \'Relatório de rotas do transporte\', \'module/Reports/TransportationRoutes\', null, 3);
                
                INSERT INTO pmicontrolesis.menu (cod_menu, ref_cod_menu_submenu, ref_cod_menu_pai, tt_menu, ord_menu, caminho, alvo, suprime_menu, ref_cod_tutormenu, ref_cod_ico, tipo_menu) 
                VALUES (21242, 21242, 9998847, \'Relatório de rotas do transporte\', 0, \'module/Reports/TransportationRoutes\', \'_self\', 1, 17, 192, null);
                
                INSERT INTO pmieducar.menu_tipo_usuario (ref_cod_tipo_usuario, ref_cod_menu_submenu, cadastra, visualiza, exclui) 
                VALUES (1, 21242, 1, 1, 1);
            '
        );
    }

    public function down()
    {
        $this->execute(
            '
                DELETE FROM pmieducar.menu_tipo_usuario 
                WHERE ref_cod_menu_submenu = 21242;

                DELETE FROM pmicontrolesis.menu 
                WHERE cod_menu = 21242;

                DELETE FROM portal.menu_submenu 
                WHERE cod_menu_submenu = 21242;
            '
        );
    }
}
