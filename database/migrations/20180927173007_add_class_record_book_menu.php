<?php

use Phinx\Migration\AbstractMigration;

class AddClassRecordBookMenu extends AbstractMigration
{
    public function up()
    {
        $this->execute("INSERT INTO portal.menu_submenu VALUES (999816, 55, 2, 'Diário de classe', 'module/Reports/ClassRecordBook', '', 3);");
        $this->execute("INSERT INTO pmicontrolesis.menu VALUES (999816, 999816, 999500, 'Diário de classe', 0, 'module/Reports/ClassRecordBook', '_self', 1, 15, 192, null);");
        $this->execute("INSERT INTO pmieducar.menu_tipo_usuario VALUES (1, 999816, 1, 1, 1);");
    }

    public function down()
    {
        $this->execute("DELETE FROM pmieducar.menu_tipo_usuario WHERE ref_cod_menu_submenu = 999816;");
        $this->execute("DELETE FROM pmicontrolesis.menu WHERE cod_menu = 999816;");
        $this->execute("DELETE FROM portal.menu_submenu WHERE cod_menu_submenu = 999816;");
    }
}
