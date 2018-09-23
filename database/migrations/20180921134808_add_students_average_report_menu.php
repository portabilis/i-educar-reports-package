<?php

use Phinx\Migration\AbstractMigration;

class AddStudentsAverageReportMenu extends AbstractMigration
{
    public function up()
    {
        $this->execute(
            '
                INSERT INTO portal.menu_submenu (cod_menu_submenu, ref_cod_menu_menu, cod_sistema, nm_submenu, arquivo, title, nivel) 
                VALUES (999834, 55, 2, \'Relatório de alunos com o melhor desempenho\', \'module/Reports/StudentsAverage\', null, 3);
                
                INSERT INTO pmicontrolesis.menu (cod_menu, ref_cod_menu_submenu, ref_cod_menu_pai, tt_menu, ord_menu, caminho, alvo, suprime_menu, ref_cod_tutormenu, ref_cod_ico, tipo_menu) 
                VALUES (999834, 999834, 999303, \'Relatório de alunos com o melhor desempenho\', 0, \'module/Reports/StudentsAverage\', \'_self\', 1, 15, 127, null);
                
                INSERT INTO pmieducar.menu_tipo_usuario (ref_cod_tipo_usuario, ref_cod_menu_submenu, cadastra, visualiza, exclui) 
                VALUES (1, 999834, 1, 0, 1);
            '
        );
    }

    public function down()
    {
        $this->execute(
            '
                DELETE FROM pmieducar.menu_tipo_usuario 
                WHERE ref_cod_menu_submenu = 999834;

                DELETE FROM pmicontrolesis.menu 
                WHERE cod_menu = 999834;

                DELETE FROM portal.menu_submenu 
                WHERE cod_menu_submenu = 999834;
            '
        );
    }
}
