<?php

use Phinx\Migration\AbstractMigration;

class AddLibraryClientsReportMenu extends AbstractMigration
{
    public function up()
    {
        $this->execute(
            '
                INSERT INTO portal.menu_submenu (cod_menu_submenu, ref_cod_menu_menu, cod_sistema, nm_submenu, arquivo, title, nivel) 
                VALUES (999845, 57, 2, \'Relatório de clientes\', \'module/Reports/LibraryClients\', null, 3);
                
                INSERT INTO pmicontrolesis.menu (cod_menu, ref_cod_menu_submenu, ref_cod_menu_pai, tt_menu, ord_menu, caminho, alvo, suprime_menu, ref_cod_tutormenu, ref_cod_ico, tipo_menu) 
                VALUES (999845, 999845, 999905, \'Relatório de clientes\', 4, \'module/Reports/LibraryClients\', \'_self\', 1, 16, 192, null);
                
                INSERT INTO pmieducar.menu_tipo_usuario (ref_cod_tipo_usuario, ref_cod_menu_submenu, cadastra, visualiza, exclui) 
                VALUES (1, 999845, 1, 1, 1);
            '
        );
    }

    public function down()
    {
        $this->execute(
            '
                DELETE FROM pmieducar.menu_tipo_usuario 
                WHERE ref_cod_menu_submenu = 999845;

                DELETE FROM pmicontrolesis.menu 
                WHERE cod_menu = 999845;

                DELETE FROM portal.menu_submenu 
                WHERE cod_menu_submenu = 999845;
            '
        );
    }
}
