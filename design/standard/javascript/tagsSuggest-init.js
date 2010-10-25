jQuery(document).ready(function() {

	jQuery('.tagssuggest').tagsSuggest({ajaxResults:true, maxResults:24, minCharacters:1});

	jQuery('.jqmDialog').jqm({modal:true, overlay:60, overlayClass:'whiteOverlay'}).jqDrag('.jqDrag');

});
