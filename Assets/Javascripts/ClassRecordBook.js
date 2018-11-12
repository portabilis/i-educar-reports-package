$j(function(){
  $j('#ref_cod_componente_curricular').closest('tr').hide();
  $j('#servidor').closest('tr').hide();
  $j('#disciplina').closest('tr').hide();
  $j('#buscar_disciplina').closest('tr').hide();
  $j('#professor').closest('tr').show();
  $j('#buscar_professor').closest('tr').show();

  $j('#modelo').on('change', function(){
    if($j('#modelo').val() == 2){
      $j('#buscar_disciplina').closest('tr').show();
      $j('#disciplina').closest('tr').show();
    }
    else{
      $j('#buscar_disciplina').prop('checked', false);
      $j('#buscar_disciplina').closest('tr').hide();
      $j('#disciplina').closest('tr').hide();
      $j('#ref_cod_componente_curricular').val("");
      $j('#ref_cod_componente_curricular').closest('tr').hide();
    }
   });

  $j('#buscar_disciplina').on('click', function(){
    if($j('#buscar_disciplina').val() == 'on'){
      $j('#disciplina').val("");
      $j('#disciplina').closest('tr').hide();
      $j('#ref_cod_componente_curricular').closest('tr').show();
    }else{
      $j('#disciplina').closest('tr').show();
      $j('#ref_cod_componente_curricular').val("");
      $j('#ref_cod_componente_curricular').closest('tr').hide();
    }
  });

  if ($j('#exibir_apenas_professores_alocados').val() == 1) {
    $j('#buscar_professor').val('on').attr('checked', 'checked');
    $j('#buscar_professor').closest('tr').hide();
    $j('#servidor').closest('tr').show();
  }

  $j('#buscar_professor').on('click', function(){
    if($j('#buscar_professor').val() == 'on'){
      $j('#professor').val("");
      $j('#professor').closest('tr').hide();
      $j('#servidor').closest('tr').show();
    }else{
      $j('#professor').closest('tr').show();
      $j('#servidor').val("");
      $j('#servidor_id').val(0);
      $j('#servidor').closest('tr').hide();
    }
  });
});