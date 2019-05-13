<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class MigrateReportsMenu extends Migration
{
    /**
     * @return void
     */
    public function migrateSubmenuLevel1()
    {
        DB::unprepared(
            '
                insert into menus("parent_id", "title", "description", "link", "icon", "order", "type", "process", "old", "parent_old", "active")
                select 
                    coalesce(
                        (select id from menus where "old" = m.ref_cod_menu_pai limit 1),
                        (
                            select id from menus where old = (
                                case 
                                    when m.ref_cod_tutormenu = 15 then 55
                                    when m.ref_cod_tutormenu = 16 then 57
                                    when m.ref_cod_tutormenu = 17 then 69
                                    when m.ref_cod_tutormenu = 18 then 25
                                    when m.ref_cod_tutormenu = 19 then 71
                                    when m.ref_cod_tutormenu = 20 then 7
                                    when m.ref_cod_tutormenu = 21 then 68
                                    when m.ref_cod_tutormenu = 22 then 70
                                end
                            )
                        )
                    ) as parent_id,
                    m.tt_menu as title,
                    ms.nm_submenu as description,
                    \'/\' || caminho as link,
                    null as icon,
                    m.ord_menu as "order",
                    11 as type,
                    ms.cod_menu_submenu as process,
                    m.cod_menu as "old",
                    coalesce(
                        m.ref_cod_menu_pai,
                        (
                            case 
                                when m.ref_cod_tutormenu = 15 then 55
                                when m.ref_cod_tutormenu = 16 then 57
                                when m.ref_cod_tutormenu = 17 then 69
                                when m.ref_cod_tutormenu = 18 then 25
                                when m.ref_cod_tutormenu = 19 then 71
                                when m.ref_cod_tutormenu = 20 then 7
                                when m.ref_cod_tutormenu = 21 then 68
                                when m.ref_cod_tutormenu = 22 then 70
                            end
                        )
                    ) as parent_old,
                    true as active
                from pmicontrolesis.menu m 
                left join portal.menu_submenu ms 
                on ms.cod_menu_submenu = m.ref_cod_menu_submenu
                where true 
                and m.cod_menu in (
                    select distinct ref_cod_menu_pai
                    from pmicontrolesis.menu 
                    where caminho ilike \'module/Reports%\'
                    order by ref_cod_menu_pai
                )
                order by parent_old, m.ord_menu, m.tt_menu;
            '
        );
    }

    /**
     * @return void
     */
    public function migrateSubmenuLevel2()
    {
        DB::unprepared(
            '
                insert into menus("parent_id", "title", "description", "link", "icon", "order", "type", "process", "old", "parent_old", "active")
                select 
                    (select id from menus where "old" = m.ref_cod_menu_pai limit 1) as parent_id,
                    m.tt_menu as title,
                    ms.nm_submenu as description,
                    \'/\' || caminho as link,
                    null as icon,
                    m.ord_menu as "order",
                    12 as type,
                    ms.cod_menu_submenu as process,
                    m.cod_menu as "old",
                    m.ref_cod_menu_pai as parent_old,
                    true as active
                from pmicontrolesis.menu m 
                left join portal.menu_submenu ms 
                on ms.cod_menu_submenu = m.ref_cod_menu_submenu
                where true 
                and m.cod_menu in (
                    select cod_menu
                    from pmicontrolesis.menu 
                    where caminho ilike \'module/Reports%\'
                )
                order by parent_old, m.ord_menu, m.tt_menu;
            '
        );
    }

    /**
     * @return void
     */
    public function up()
    {
        $this->migrateSubmenuLevel1();
        $this->migrateSubmenuLevel2();
    }
}
