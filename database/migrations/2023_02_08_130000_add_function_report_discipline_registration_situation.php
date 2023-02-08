<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFunctionReportDisciplineRegistrationSituation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(
            'DROP FUNCTION IF EXISTS relatorio.retorna_situacao_matricula_componente(cod_situacao_matricula numeric, cod_situacao_componente numeric);'
        );

        DB::unprepared(
            file_get_contents(__DIR__ . '/../sqls/functions/report.return_discipline_registration_situation.sql')
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared(
            'DROP FUNCTION relatorio.retorna_situacao_matricula_componente(cod_situacao_matricula numeric, cod_situacao_componente numeric);'
        );
    }
}
