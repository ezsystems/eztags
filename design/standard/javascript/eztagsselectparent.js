jQuery(document).ready(function() {
	var
		parent_selector_button = jQuery('#parent-selector-button'),
		parent_selector_tree = jQuery('#parent-selector-tree'),
		parent_id = jQuery('#parent_id'),
		parent_keyword = jQuery('#parent_keyword');

	parent_selector_button.click(function() {parent_selector_tree.jqmShow(); return false;});
	parent_selector_tree.jqm({modal:true, overlay:60, overlayClass:'whiteOverlay'}).jqDrag('.jqDrag');

	function getParentTagHierarchy(tag, i) {
		if (tag.attr('rel') == 0) if (i == 0) return '(no parent)'; else return '';
		var parent = getParentTagHierarchy(tag.parents('div:first').prev('a'), ++i);
		return (parent ? parent + ' / ' : '') + tag.find('span').html();
	}

	jQuery('#contentstructure a:not([class^="openclose"])').live('click', function(e) {
		if (jQuery(this).parents('li.disabled').length) return false;
		var tag = jQuery(this);
		parent_keyword.html(getParentTagHierarchy(tag, 0));
		parent_id.val(tag.attr('rel'));
		parent_selector_tree.jqmHide();
		return false;
	});
});