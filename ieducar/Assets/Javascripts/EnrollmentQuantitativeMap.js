(function ($) {
  $(document).ready(function () {

    $j('#ref_cod_curso').closest('tr').hide();
    $j('#curso').closest('tr').hide();
    $j('#exibir_quantidade_salas').closest('tr').hide();
    $j('#turno').closest('tr').hide();
    var getTipoBoletimTurma = function () {
      var $modelo = $j(this);

      if ($modelo.val() == 4 || $modelo.val() == 5) {
        $j('#situacao').closest('tr').show();
        $j('#data_ini').closest('tr').hide();
        $j('#data_fim').closest('tr').hide();
        $j('#turno').closest('tr').hide();
        $j('#ref_cod_curso').closest('tr').hide();
        $j('#curso').closest('tr').show();
        $j('#exibir_quantidade_salas').closest('tr').show();

      } else if ($modelo.val() == 6) {
        $j('#situacao').closest('tr').hide();
        $j('#data_ini').closest('tr').hide();
        $j('#data_fim').closest('tr').hide();
        $j('#ref_cod_curso').closest('tr').show();
        $j('#curso').closest('tr').hide();
        $j('#exibir_quantidade_salas').closest('tr').hide();
        $j('#turno').closest('tr').show();
      } else {
        $j('#situacao').closest('tr').show();
        $j('#data_ini').closest('tr').show();
        $j('#data_fim').closest('tr').show();
        $j('#ref_cod_curso').closest('tr').hide();
        $j('#curso').closest('tr').hide();
        $j('#exibir_quantidade_salas').closest('tr').hide();
        $j('#turno').closest('tr').hide();
      }
    }

    $j('#modelo').change(getTipoBoletimTurma);

    function getCurso(cursos) {
      const campoCurso = document.getElementById('ref_cod_curso');

      if (cursos.length) {
        campoCurso.length = 1;
        campoCurso.options[0].text = 'Selecione um curso';
        campoCurso.options[0].value = '';
        campoCurso.disabled = false;

        $j.each(cursos, function(i, item) {
          campoCurso.options[campoCurso.options.length] = new Option(item.name,item.id, false, false);
        });
      } else {
        campoCurso.length = 1;
        campoCurso.options[0].text = 'A institui\u00e7\u00e3o n\u00e3o possui nenhum curso';
      }
    }

    function atualizaCurso() {
      const campoInstituicao = $j('#ref_cod_instituicao').val();
      getApiResource("/api/resource/course",getCurso,{institution:campoInstituicao});
    }

    $j('#ref_cod_instituicao').change( function() {
      atualizaCurso();
      atualizaMultipleCurso();
    });

    $j('#ref_cod_escola').change(function () {
      if (!$j('#ref_cod_escola').val() > 0)
        atualizaCurso();
        atualizaMultipleCurso();
    });

    if ($j('#ref_cod_instituicao').val())
      atualizaCurso();
      atualizaMultipleCurso();
    $j('#ano').change(function (event) {
      atualizaCurso();
      atualizaMultipleCurso();
      event.preventDefault();
    });
  }); // ready

  function atualizaMultipleCurso() {
    let ano = $j('#ano').val();
    let instituicaoId = $j('#ref_cod_instituicao').val();
    let escolaId = $j('#ref_cod_escola').val();
    let cursoField = $j('#curso');

    clearValues(cursoField);

    if (!!instituicaoId === true) {
      let url = getResourceUrlBuilder.buildUrl('/module/Api/Curso', 'cursos', {
        instituicao_id : instituicaoId,
        ano : ano,
        escola_id : escolaId,
        ativo: 1
      });

      let options = {
        url : url,
        dataType : 'json',
        success  : function(dataResponse){
          let selectOptions = {};

          dataResponse.cursos.forEach((curso) => {
              selectOptions[curso.id] = curso.nome;
          }, {});

          updateChozen($j('#curso'), selectOptions);
        }
      };

      getResources(options);
    }
  }

})(jQuery);
