$j('#titulo').closest('tr').hide();

$j('#definir_titulo').on('click', function(){
  if($j('#definir_titulo').val() == 'on'){
    $j('#titulo').closest('tr').show();
  }else{
    $j('#titulo').closest('tr').hide();
  }
});