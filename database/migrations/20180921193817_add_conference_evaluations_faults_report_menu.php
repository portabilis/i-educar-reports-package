<?php

use Phinx\Migration\AbstractMigration;

class AddConferenceEvaluationsFaultsReportMenu extends AbstractMigration
{
    public function up()
    {
        $this->execute(
            '
                INSERT INTO portal.menu_submenu (cod_menu_submenu, ref_cod_menu_menu, cod_sistema, nm_submenu, arquivo, title, nivel) 
                VALUES (999809, 55, 2, \'Relatório de conferência de notas e faltas\', \'module/Reports/ConferenceEvaluationsFaults\', null, 3);
                
                INSERT INTO pmicontrolesis.menu (cod_menu, ref_cod_menu_submenu, ref_cod_menu_pai, tt_menu, ord_menu, caminho, alvo, suprime_menu, ref_cod_tutormenu, ref_cod_ico, tipo_menu) 
                VALUES (999809, 999809, 999922, \'Relatório de conferência de notas e faltas\', 0, \'module/Reports/ConferenceEvaluationsFaults\', \'_self\', 1, 15, 192, 2);
                
                INSERT INTO pmieducar.menu_tipo_usuario (ref_cod_tipo_usuario, ref_cod_menu_submenu, cadastra, visualiza, exclui) 
                VALUES (1, 999809, 1, 1, 1);
            '
        );
    }

    public function down()
    {
        $this->execute(
            '
                DELETE FROM pmieducar.menu_tipo_usuario 
                WHERE ref_cod_menu_submenu = 999809;

                DELETE FROM pmicontrolesis.menu 
                WHERE cod_menu = 999809;

                DELETE FROM portal.menu_submenu 
                WHERE cod_menu_submenu = 999809;
            '
        );
    }
}
