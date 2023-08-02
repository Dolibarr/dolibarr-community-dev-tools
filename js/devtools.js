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


	$(".dev-tools-search-input[data-target]").on( "keyup", function() {
		let search = $(this).val();
		let target = $(this).attr('data-target');
		if(target == undefined){
			return;
		}

		if(search.length > 0){
			$(target).hide();
			$(target).each(function(){
				if($(this).text().toUpperCase().indexOf(search.toUpperCase()) != -1){
					$(this).show();
				}
			});
		}else{
			$(target).show();
		}
	});



	/**
	 * Adds an auto sizer for textarea to make text typing easier
	 * @param {HTMLElement} textAreaItem
	 */
	let autoSizedTextArea = document.querySelectorAll('textarea[autoresize="1"]');
	if(autoSizedTextArea.length > 0){
		autoSizedTextArea.forEach(function (textAreaItem) {
			textAreaItem.setAttribute("style", "height:" + (textAreaItem.scrollHeight) + "px;overflow-y:hidden;");
			textAreaItem.addEventListener("input", function(){
				this.style.height = 0;
				this.style.height = (this.scrollHeight) + "px";
			}, false);
		});
	}

	/**
	 * Allow disabling Enter key on textarea who need it.
	 */
	$('textarea[disablenewline="1"]').on( 'keypress', function(event) {
		if (event.key === 'Enter')
		{
			//method to prevent from default behaviour
			event.preventDefault();
			return false;
		}
	});
});
