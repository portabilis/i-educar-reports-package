$j('#modelo').change(onModeloClick);
$j('#cor_de_fundo').closest('tr').hide();

function onModeloClick(){
  if($j('#modelo').val() == 1){
    $j('#imprimir_serie').closest('tr').show();
    $j('#cor_de_fundo').closest('tr').show();
  }else{
    $j('#imprimir_serie').closest('tr').hide();
    $j('#cor_de_fundo').closest('tr').hide();
  }
}

$j('#modelo').trigger('change');




$j('#validade').css('width', '229px').mask("99/9999", {placeholder: "__/____"});

        