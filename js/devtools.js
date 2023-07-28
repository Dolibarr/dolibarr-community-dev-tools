$(function() {

	/**
	 * Search filter
	 */


	$("#search-dev-tools-form-input").on( "keyup", function() {
		let txt = $('#search-dev-tools-form-input').val();
		if(txt.length > 0){
			$('.box-flex-container .box-flex-item').hide();
			$('.box-flex-container .box-flex-item.filler').show();

			$('.box-flex-container .box-flex-item').each(function(){
				if($(this).text().toUpperCase().indexOf(txt.toUpperCase()) != -1){
					$(this).show();
				}
			});

		}else{
			$('.box-flex-container .box-flex-item').show();
		}
	});
});