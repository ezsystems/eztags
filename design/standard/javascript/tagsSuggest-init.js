jQuery(document).ready(function($) {

	$('.tagssuggest').tagsSuggest({ajaxResults:true, maxResults:24, minCharacters:1});

	$('.jqmDialog').each(function(){
		$(this).jqm({modal:true, overlay:60, overlayClass:'whiteOverlay'}).jqDrag('.jqDrag');
	});

});
