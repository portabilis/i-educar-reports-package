<?php

use Phinx\Migration\AbstractMigration;

class AddLibraryLoanReceiptReportMenu extends AbstractMigration
{
    public function up()
    {
        $this->execute(
            '
                INSERT INTO portal.menu_submenu (cod_menu_submenu, ref_cod_menu_menu, cod_sistema, nm_submenu, arquivo, title, nivel) 
                VALUES (999620, 57, 2, \'Comprovante de empréstimo\', \'module/Reports/LibraryLoanReceipt\', null, 3);
                
                INSERT INTO pmicontrolesis.menu (cod_menu, ref_cod_menu_submenu, ref_cod_menu_pai, tt_menu, ord_menu, caminho, alvo, suprime_menu, ref_cod_tutormenu, ref_cod_ico, tipo_menu) 
                VALUES (999620, 999620, 999907, \'Comprovante de empréstimo\', 1, \'module/Reports/LibraryLoanReceipt\', \'_self\', 1, 16, 1, null);
                
                INSERT INTO pmieducar.menu_tipo_usuario (ref_cod_tipo_usuario, ref_cod_menu_submenu, cadastra, visualiza, exclui) 
                VALUES (1, 999620, 1, 0, 1);
                
                INSERT INTO portal.menu_funcionario (ref_ref_cod_pessoa_fj, cadastra, exclui, ref_cod_menu_submenu) 
                VALUES (1, 0, 0, 999620);
            '

        );
    }

    public function down()
    {
        $this->execute(
            '
                DELETE FROM portal.menu_funcionario 
                WHERE ref_cod_menu_submenu = 999620;
                
                DELETE FROM pmieducar.menu_tipo_usuario 
                WHERE ref_cod_menu_submenu = 999620;

                DELETE FROM pmicontrolesis.menu 
                WHERE cod_menu = 999620;

                DELETE FROM portal.menu_submenu 
                WHERE cod_menu_submenu = 999620;
            '
        );
    }
}
