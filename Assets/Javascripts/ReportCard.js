$j('#observacoes').closest('tr').hide();
$j('#mensagem_aniversario').closest('tr').hide();
$j('#imprimir_mensagem_aniversario').closest('tr').hide();
$j('#grafico_media_turma').closest('tr').hide();
$j('#grafico_preto').closest('tr').hide();

var tipoBoletim;
var tipoBoletimDiferenciado;
var imprimirDoisBoletins = false;

var arrayShowObservacoes = [
    'report-card'
];

var arrayShowMensagemAniversario = [
    'report-card'
];

var arrayShowGraficoMediaTurma = [
    'report-card'
];

var handleGetTipoBoletimTurma = function(dataResponse) {
    tipoBoletim = dataResponse['tipo-boletim'];
    tipoBoletimDiferenciado = dataResponse['tipo-boletim-diferenciado'];
    imprimirDoisBoletins = dataResponse['tipo-boletim-diferenciado']
        && dataResponse['tipo-boletim-diferenciado'] != dataResponse['tipo-boletim'];
    
    if ($j.inArray(tipoBoletim, arrayShowObservacoes) > -1) {
        $j('#observacoes').closest('tr').show();
    } else {
        $j('#observacoes').closest('tr').hide();
    }
    
    if ($j.inArray(tipoBoletim, arrayShowMensagemAniversario) > -1) {
        $j('#imprimir_mensagem_aniversario').closest('tr').show();
        $j('#emitir_pareceres_componente_curricular').closest('tr').show();
    } else {
        $j('#imprimir_mensagem_aniversario').closest('tr').hide();
        $j('#emitir_pareceres_componente_curricular').closest('tr').hide();
    }
    
    if ($j.inArray(tipoBoletim, arrayShowGraficoMediaTurma) > -1) {
        $j('#grafico_media_turma').closest('tr').show();
        $j('#grafico_preto').closest('tr').show();
        $j('#emitir_pareceres_componente_curricular').closest('tr').show();
    } else {
        $j('#grafico_media_turma').closest('tr').hide();
        $j('#grafico_preto').closest('tr').hide();
        $j('#emitir_pareceres_componente_curricular').closest('tr').hide();
    }

};

var getTipoBoletimTurma = function() {
    var $turmaField = $j(this);

    if ($turmaField.val()) {

        var additionalVars = {
            id: $turmaField.val(),
        };

        var options = {
            url: getResourceUrlBuilder.buildUrl('/module/Api/turma', 'tipo-boletim', additionalVars),
            dataType: 'json',
            data: {},
            success: handleGetTipoBoletimTurma,
        };

        getResource(options);
    }
};

$j('#ref_cod_turma').change(getTipoBoletimTurma);

function customPrintReport() {
  if (validatesPresenseOfValueInRequiredFields()) {

    var form_data = $j(document.formcadastro).serialize()+'&print_report_with_get=true';
    var form_url = document.formcadastro.action;

    if(imprimirDoisBoletins && !$j('#ref_cod_matricula').val().length){
      window.open(form_url+'?'+form_data+'&alunos_diferenciados=1', '_blank');
      window.open(form_url+'?'+form_data+'&alunos_diferenciados=2', '_blank');
    }else{
      window.open(form_url+'?'+form_data, '_blank');
    }
  }

  document.getElementById( 'btn_enviar' ).disabled = false;
}

function onImprimirMensagemAniversarioClick() {
    if ($j('#imprimir_mensagem_aniversario').prop('checked')) {
        $j('#mensagem_aniversario').closest('tr').show();
    } else {
        $j('#mensagem_aniversario').closest('tr').hide();
    }
};
