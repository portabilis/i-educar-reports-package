
$j("#orientacao").on('click', function(){
	if($j("#orientacao").val() == 'paisagem'){
  		$j("#emitir_assinaturas").closest('tr').show();
	}else{
  		$j("#emitir_assinaturas").closest('tr').hide();
  	}
});