<?php

use App\Menu;
use App\Process;
use Illuminate\Database\Migrations\Migration;

class BaseMenus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', Process::MENU_SCHOOL)->firstOrFail()->getKey(),
            'title' => 'Relatórios',
            'order' => 5,
            'parent_old' => 15,
            'old' => 21126,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', Process::MENU_SCHOOL)->firstOrFail()->getKey(),
            'title' => 'Documentos',
            'order' => 6,
            'parent_old' => 15,
            'old' => 21127,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 21126)->firstOrFail()->getKey(),
            'title' => 'Movimentações',
            'order' => 2,
            'parent_old' => 21126,
            'old' => 999301,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 21126)->firstOrFail()->getKey(),
            'title' => 'Lançamentos',
            'order' => 3,
            'parent_old' => 21126,
            'old' => 999922,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 21126)->firstOrFail()->getKey(),
            'title' => 'Cadastrais',
            'order' => 4,
            'parent_old' => 21126,
            'old' => 999300,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 21126)->firstOrFail()->getKey(),
            'title' => 'Matrículas',
            'order' => 5,
            'parent_old' => 21126,
            'old' => 999923,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 21126)->firstOrFail()->getKey(),
            'title' => 'Indicadores',
            'order' => 6,
            'parent_old' => 21126,
            'old' => 999303,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 21127)->firstOrFail()->getKey(),
            'title' => 'Atestados',
            'order' => 0,
            'parent_old' => 21127,
            'old' => 999400,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 21127)->firstOrFail()->getKey(),
            'title' => 'Boletins',
            'order' => 2,
            'parent_old' => 21127,
            'old' => 999450,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 21127)->firstOrFail()->getKey(),
            'title' => 'Resultados',
            'order' => 3,
            'parent_old' => 21127,
            'old' => 999925,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 21127)->firstOrFail()->getKey(),
            'title' => 'Fichas',
            'order' => 8,
            'parent_old' => 21127,
            'old' => 999861,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 21127)->firstOrFail()->getKey(),
            'title' => 'Históricos',
            'order' => 9,
            'parent_old' => 21127,
            'old' => 999460,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 21127)->firstOrFail()->getKey(),
            'title' => 'Registros',
            'order' => 10,
            'parent_old' => 21127,
            'old' => 999500,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', Process::MENU_LIBRARY)->firstOrFail()->getKey(),
            'title' => 'Relatórios',
            'order' => 3,
            'parent_old' => 16,
            'old' => 999614,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', Process::MENU_LIBRARY)->firstOrFail()->getKey(),
            'title' => 'Documentos',
            'order' => 4,
            'parent_old' => 16,
            'old' => 999831,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999614)->firstOrFail()->getKey(),
            'title' => 'Cadastrais',
            'order' => 1,
            'parent_old' => 999614,
            'old' => 999905,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999614)->firstOrFail()->getKey(),
            'title' => 'Movimentações',
            'order' => 2,
            'parent_old' => 999614,
            'old' => 999906,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999831)->firstOrFail()->getKey(),
            'title' => 'Comprovantes',
            'order' => 3,
            'parent_old' => 999831,
            'old' => 999907,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', Process::MENU_TRANSPORT)->firstOrFail()->getKey(),
            'title' => 'Relatórios',
            'order' => 3,
            'parent_old' => 17,
            'old' => 20712,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 20712)->firstOrFail()->getKey(),
            'title' => 'Cadastrais',
            'order' => 1,
            'parent_old' => 20712,
            'old' => 9998847,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', Process::MENU_EMPLOYEES)->firstOrFail()->getKey(),
            'title' => 'Relatórios',
            'order' => 2,
            'parent_old' => 71,
            'old' => 999913,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', Process::MENU_EMPLOYEES)->firstOrFail()->getKey(),
            'title' => 'Documentos',
            'order' => 3,
            'parent_old' => 71,
            'old' => 999916,
        ]);
        Menu::query()->create([
            'parent_id' => Menu::query()->where('old', 999913)->firstOrFail()->getKey(),
            'title' => 'Cadastrais',
            'order' => 1,
            'parent_old' => 999913,
            'old' => 999914,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Menu::query()->where('old', 999914)->delete();
        Menu::query()->where('old', 999916)->delete();
        Menu::query()->where('old', 999913)->delete();
        Menu::query()->where('old', 9998847)->delete();
        Menu::query()->where('old', 20712)->delete();
        Menu::query()->where('old', 999907)->delete();
        Menu::query()->where('old', 999906)->delete();
        Menu::query()->where('old', 999905)->delete();
        Menu::query()->where('old', 999831)->delete();
        Menu::query()->where('old', 999614)->delete();
        Menu::query()->where('old', 999500)->delete();
        Menu::query()->where('old', 999460)->delete();
        Menu::query()->where('old', 999861)->delete();
        Menu::query()->where('old', 999925)->delete();
        Menu::query()->where('old', 999450)->delete();
        Menu::query()->where('old', 999400)->delete();
        Menu::query()->where('old', 999303)->delete();
        Menu::query()->where('old', 999923)->delete();
        Menu::query()->where('old', 999300)->delete();
        Menu::query()->where('old', 999922)->delete();
        Menu::query()->where('old', 999301)->delete();
        Menu::query()->where('old', 21127)->delete();
        Menu::query()->where('old', 21126)->delete();
    }
}
