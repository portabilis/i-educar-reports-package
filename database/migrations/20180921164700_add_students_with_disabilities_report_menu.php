<?php

use Phinx\Migration\AbstractMigration;

class AddStudentsWithDisabilitiesReportMenu extends AbstractMigration
{
    public function up()
    {
        $this->execute(
            '
                INSERT INTO portal.menu_submenu (cod_menu_submenu, ref_cod_menu_menu, cod_sistema, nm_submenu, arquivo, title, nivel) 
                VALUES (999227, 55, 2, \'Relatório de alunos com deficiência\', \'module/Reports/StudentsWithDisabilities\', null, 3);
                
                INSERT INTO pmicontrolesis.menu (cod_menu, ref_cod_menu_submenu, ref_cod_menu_pai, tt_menu, ord_menu, caminho, alvo, suprime_menu, ref_cod_tutormenu, ref_cod_ico, tipo_menu) 
                VALUES (999227, 999227, 999300, \'Relatório de alunos com deficiência\', 0, \'module/Reports/StudentsWithDisabilities\', \'_self\', 1, 15, 192, 2);
                
                INSERT INTO pmieducar.menu_tipo_usuario (ref_cod_tipo_usuario, ref_cod_menu_submenu, cadastra, visualiza, exclui) 
                VALUES (1, 999227, 1, 1, 1);
            '
        );
    }

    public function down()
    {
        $this->execute(
            '
                DELETE FROM pmieducar.menu_tipo_usuario 
                WHERE ref_cod_menu_submenu = 999227;

                DELETE FROM pmicontrolesis.menu 
                WHERE cod_menu = 999227;

                DELETE FROM portal.menu_submenu 
                WHERE cod_menu_submenu = 999227;
            '
        );
    }
}
