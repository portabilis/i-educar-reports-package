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
        Menu::query()->updateOrCreate([
            'old' => 21126,
        ], [
            'parent_id' => Menu::query()->where('old', Process::MENU_SCHOOL)->firstOrFail()->getKey(),
            'title' => 'Relatórios',
            'order' => 5,
            'parent_old' => 15,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 21127,
        ], [
            'parent_id' => Menu::query()->where('old', Process::MENU_SCHOOL)->firstOrFail()->getKey(),
            'title' => 'Documentos',
            'order' => 6,
            'parent_old' => 15,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 999301,
        ], [
            'parent_id' => Menu::query()->where('old', 21126)->firstOrFail()->getKey(),
            'title' => 'Movimentações',
            'order' => 2,
            'parent_old' => 21126,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 999922,
        ], [
            'parent_id' => Menu::query()->where('old', 21126)->firstOrFail()->getKey(),
            'title' => 'Lançamentos',
            'order' => 3,
            'parent_old' => 21126,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 999300,
        ], [
            'parent_id' => Menu::query()->where('old', 21126)->firstOrFail()->getKey(),
            'title' => 'Cadastrais',
            'order' => 4,
            'parent_old' => 21126,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 999923,
        ], [
            'parent_id' => Menu::query()->where('old', 21126)->firstOrFail()->getKey(),
            'title' => 'Matrículas',
            'order' => 5,
            'parent_old' => 21126,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 999303,
        ], [
            'parent_id' => Menu::query()->where('old', 21126)->firstOrFail()->getKey(),
            'title' => 'Indicadores',
            'order' => 6,
            'parent_old' => 21126,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 999400,
        ], [
            'parent_id' => Menu::query()->where('old', 21127)->firstOrFail()->getKey(),
            'title' => 'Atestados',
            'order' => 0,
            'parent_old' => 21127,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 999450,
        ], [
            'parent_id' => Menu::query()->where('old', 21127)->firstOrFail()->getKey(),
            'title' => 'Boletins',
            'order' => 2,
            'parent_old' => 21127,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 999925,
        ], [
            'parent_id' => Menu::query()->where('old', 21127)->firstOrFail()->getKey(),
            'title' => 'Resultados',
            'order' => 3,
            'parent_old' => 21127,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 999861,
        ], [
            'parent_id' => Menu::query()->where('old', 21127)->firstOrFail()->getKey(),
            'title' => 'Fichas',
            'order' => 8,
            'parent_old' => 21127,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 999460,
        ], [
            'parent_id' => Menu::query()->where('old', 21127)->firstOrFail()->getKey(),
            'title' => 'Históricos',
            'order' => 9,
            'parent_old' => 21127,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 999500,
        ], [
            'parent_id' => Menu::query()->where('old', 21127)->firstOrFail()->getKey(),
            'title' => 'Registros',
            'order' => 10,
            'parent_old' => 21127,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 999614,
        ], [
            'parent_id' => Menu::query()->where('old', Process::MENU_LIBRARY)->firstOrFail()->getKey(),
            'title' => 'Relatórios',
            'order' => 3,
            'parent_old' => 16,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 999831,
        ], [
            'parent_id' => Menu::query()->where('old', Process::MENU_LIBRARY)->firstOrFail()->getKey(),
            'title' => 'Documentos',
            'order' => 4,
            'parent_old' => 16,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 999905,
        ], [
            'parent_id' => Menu::query()->where('old', 999614)->firstOrFail()->getKey(),
            'title' => 'Cadastrais',
            'order' => 1,
            'parent_old' => 999614,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 999906,
        ], [
            'parent_id' => Menu::query()->where('old', 999614)->firstOrFail()->getKey(),
            'title' => 'Movimentações',
            'order' => 2,
            'parent_old' => 999614,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 999907,
        ], [
            'parent_id' => Menu::query()->where('old', 999831)->firstOrFail()->getKey(),
            'title' => 'Comprovantes',
            'order' => 3,
            'parent_old' => 999831,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 20712,
        ], [
            'parent_id' => Menu::query()->where('old', Process::MENU_TRANSPORT)->firstOrFail()->getKey(),
            'title' => 'Relatórios',
            'order' => 3,
            'parent_old' => 17,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 9998847,
        ], [
            'parent_id' => Menu::query()->where('old', 20712)->firstOrFail()->getKey(),
            'title' => 'Cadastrais',
            'order' => 1,
            'parent_old' => 20712,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 999913,
        ], [
            'parent_id' => Menu::query()->where('old', Process::MENU_EMPLOYEES)->firstOrFail()->getKey(),
            'title' => 'Relatórios',
            'order' => 2,
            'parent_old' => 71,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 999916,
        ], [
            'parent_id' => Menu::query()->where('old', Process::MENU_EMPLOYEES)->firstOrFail()->getKey(),
            'title' => 'Documentos',
            'order' => 3,
            'parent_old' => 71,
        ]);
        Menu::query()->updateOrCreate([
            'old' => 999914,
        ], [
            'parent_id' => Menu::query()->where('old', 999913)->firstOrFail()->getKey(),
            'title' => 'Cadastrais',
            'order' => 1,
            'parent_old' => 999913,
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
