(function($){
	$.fn.tagsSuggest = function(settings) {
		var defaults = {
			searchId: $(this).attr('id'),
			minCharacters: 1,
			maxResults: undefined,
			wildCard: "",
			caseSensitive: false,
			notCharacter: "!",
			maxHeight:350,
			highlightMatches: true,
			ajaxResults: false,
			suggestTimeout: 500,
			wrapPlacement: false,
			ezjscAutocomplete:'ezjsctagssuggest::autocomplete',
			ezjscSuggest:'ezjsctagssuggest::suggest'
		};
		settings = $.extend(defaults, settings);

		var timeout = null;

		return this.each(function() {

			function regexEscape(txt, omit) {
				var specials = ['/', '.', '*', '+', '?', '|',
								'(', ')', '[', ']', '{', '}', '\\'];
				if (omit) {
					for (var i=0; i < specials.length; i++) {
						if (specials[i] === omit) { specials.splice(i,1); }
					}
				}
				var escapePatt = new RegExp('(\\' + specials.join('|\\') + ')', 'g');
				return txt.replace(escapePatt, '\\$1');
			}

			var
				obj = $(this).find('.tagssuggestfield'),
				names = $(this).find('.tagnames'),
				parent_ids = $(this).find('.tagpids'),
				parent_selector_tree = $(this).next('.parent-selector-tree'),
				parent_selector_button = $(this).find('input[type="button"]'),
				parentSelector = false,
				wildCardPatt = new RegExp(regexEscape(settings.wildCard || ''),'g'),
				results = $('<div />'),
				currentSelection, pageX, pageY;

			parent_selector_button.click(function() {openParentSelector();});

			$(this).find('div.tags-listed').append('<ul class="float-break" />');
			$(this).find('div.tags-suggested').append('<ul class="float-break" />');

			var tags_listed = $(this).find('div.tags-listed ul');
			var tags_suggested = $(this).find('div.tags-suggested ul');

			if (names.val() && parent_ids.val()) {
				tags_listed.parent('div.tags-list').removeClass('no-results');
				var tag_names_array = names.val().split(',');
				var tag_parent_ids_array = parent_ids.val().split(',');
				$.each(tag_names_array, function(index, value) {
					addTagToList({'tag_name': value.replace(/^\s+|\s+$/g, ''), 'tag_parent_id': tag_parent_ids_array[index].replace(/^\s+|\s+$/g, '')}, tags_listed, removeTagFromList, '&times;');
				});
				//$.ez(settings.ezjscSuggest, {'tags_string': names.val() + ', ' + $('input[id$="title"]:first').val()}, function(data){buildSuggest(data);});
			}
			//else {
			//	$.ez(settings.ezjscSuggest, {'tags_string': $('input[id$="title"]:first').val()}, function(data){buildSuggest(data);});
			//}
			runSuggest();

			function addTagToList(item, list, callback, icon) {
				var tag = $('<li' + (!icon ? ' title="Add this tag"' : '') + '>' + item.tag_name + (icon ? '<a href="#" title="Remove tag">' + icon + '</a>' : '') + '</li>').data('tag', {'tag_parent_id': item.tag_parent_id, 'tag_name': item.tag_name});
				if (icon) {
					tag.find('a').click(function(e) {callback(tag); return false;})
				}
				else {
					tag.click(function(e) {callback(tag); return false;});
				}
				list.append(tag);
				list.parent('div.tags-list').removeClass('no-results');
			}

			function removeTagFromList(tag) {
				$(tag).remove();
				updateValues();
			}

			function moveTag(tag) {
				var tag_data = $(tag).data('tag');
				addTagToList({'tag_parent_id': tag_data.tag_parent_id, 'tag_name': tag_data.tag_name}, tags_listed, removeTagFromList, '&times;');
				removeTagFromList(tag);
				//updateValues();
			}

			function updateValues() {
				var tag_names = '';
				var tag_parent_ids = '';
				tags_listed.find('li').each(function(i) {
					tag_names += (tag_names == '' ? '' : ', ') + $(this).data('tag').tag_name;
					tag_parent_ids += (tag_parent_ids == '' ? '' : ', ') + $(this).data('tag').tag_parent_id;
				});
				names.val(tag_names);
				parent_ids.val(tag_parent_ids);
				if (!tag_names && !tag_parent_ids) tags_listed.parent('div.tags-list').addClass('no-results');
				runSuggest();
			}

			function openParentSelector() {
				$(results).hide();
				if (!parentSelector) {
					parentSelector = $('#content-tree');
					bindParentSelectorTreeEvents();
				}
				$(parentSelector).show();
				parent_selector_tree.jqmShow(); 
			}

			function bindParentSelectorTreeEvents() {
				$('a:not([class^=openclose])', parentSelector).live('click', function(e) {
					addTagToList({'tag_name': obj.val().replace(/^\s+|\s+$/g, ''), 'tag_parent_id': $(this).attr('rel')}, tags_listed, removeTagFromList, '&times;');
					updateValues();
					clearTagSearchField();
					$(results).html('').hide();
					$(parentSelector).hide();
					parent_selector_tree.jqmHide(); 
					return false;
				});
			}

			function setParentSelectorButtonState() {
				if (obj.val().replace(/^\s+|\s+$/g, '')) {
					parent_selector_button.removeClass('button-disabled').addClass('button').removeAttr('disabled');
				}
				else {
					parent_selector_button.removeClass('button').addClass('button-disabled').attr('disabled', 'disabled');
				}
			}

			function clearTagSearchField() {
				obj.val('');
				setParentSelectorButtonState();
			}

			function selectResultItem(item) {
				obj.val(item.tag_name);
				$(results).html('').hide();
				addTagToList(item, tags_listed, removeTagFromList, '&times;');
				updateValues();
				clearTagSearchField();
			}

			function setHoverClass(el) {
				$('div.resultItem', results).removeClass('hover');
				$(el).addClass('hover');
				currentSelection = el;
			}

			function buildAutocomplete(resultObjects, sFilterTxt) {
				//strings = sFilterTxt.split(' ');
				//sFilterTxt = "(" + strings[strings.length - 1] + ")";
				sFilterTxt = "(" + sFilterTxt + ")";

				var bOddRow = true, i, iFound = 0,
					filterPatt = settings.caseSensitive ? new RegExp(sFilterTxt, "g") : new RegExp(sFilterTxt, "ig");

				$(results).html('').hide();

				for (i = 0; i < resultObjects.length; i += 1) {
					var item = $('<div />');
					if (settings.highlightMatches === true) {
						//text = text.replace(filterPatt, "<strong>$1</strong>");
						// suggested feature by Olivier Portier

						//text = text.replace(filterPatt, "$1<strong>");
						//text = text + '</strong>';

						//text = '<span class="count">(' + resultObjects[i].num + ')</span>' + text;
					}

					//text = '<span class="count">(' + resultObjects[i].num + ')</span>' + text;

					$(item).append('<p class="text">' + (resultObjects[i].tag_parent_name ? '<span class="count">(' + resultObjects[i].tag_parent_name + ')</span>' : '') + resultObjects[i].tag_name + '</p>');

					$(item).addClass('resultItem').
						addClass((bOddRow) ? 'odd' : 'even').
						click(function(n) { return function() {
							selectResultItem(resultObjects[n]);
							obj.focus();
							obj.val(obj.val());//move cursor to the string end (everybody say hello to ie)
						};}(i)).
						mouseover(function(el) { return function() {
							setHoverClass(el);
						};}(item));

					$(results).append(item);

					bOddRow = !bOddRow;

					iFound += 1;
					if (typeof settings.maxResults === 'number' && iFound >= settings.maxResults) {
						break;
					}
				}

				$(results).find('.resultItem').wrapAll('<div class="results-wrap"></div>');

				if ($('.results-wrap div', results).length > 0) {
					currentSelection = undefined;
					$(results).prepend('<iframe frameborder="0"></iframe>').show().css('height', 'auto');

					if ($('.results-wrap', results).height() > settings.maxHeight) {
						$('.results-wrap', results).css({'height': settings.maxHeight + 'px'});
					}
				}
			}

			function runSuggest() {
				tags_suggested.empty();
				var tag_names = names.val();
				//var content_title = $('input[id$="title"]:first').val();
				if (tag_names) {
					tags_suggested.parent('div.tags-list').removeClass('no-results').addClass('loading');
					$.ez(settings.ezjscSuggest, {'tags_string': tag_names}, function(data){
						if (!data.content.tags.length) {
							tags_suggested.parent('div.tags-list').addClass('no-results').removeClass('loading');
							return true;
						}

						tags_suggested.parent('div.tags-list').removeClass('loading');

						for (i = 0; i < data.content.tags.length; i += 1) {
							addTagToList(data.content.tags[i], tags_suggested, moveTag, false);
						}
					});
				}
				else {
					tags_suggested.parent('div.tags-list').addClass('no-results').removeClass('loading');
				}
			}

			function runAutocomplete() {
				if (obj.val()) $.ez(settings.ezjscAutocomplete, {'search_string': obj.val()}, function(data){
					var
						resultObjects = [],
						sFilterTxt = (!settings.wildCard) ? regexEscape(obj.val()) : regexEscape(obj.val(), settings.wildCard).replace(wildCardPatt, '.*'),
						bMatch = true, 
						filterPatt, i;

					if (settings.notCharacter && sFilterTxt.indexOf(settings.notCharacter) === 0) {
						sFilterTxt = sFilterTxt.substr(settings.notCharacter.length, sFilterTxt.length);
						if (sFilterTxt.length > 0) { bMatch = false; }
					}

					sFilterTxt = sFilterTxt || '.*';
					sFilterTxt = settings.wildCard ? '^' + sFilterTxt : sFilterTxt;
					filterPatt = settings.caseSensitive ? new RegExp(sFilterTxt) : new RegExp(sFilterTxt, "i");

					if (typeof data === 'string') {
						data = JSON.parse(data);
					}

					buildAutocomplete(data.content.tags, sFilterTxt);
				});
				else {
					$(results).html('').hide();
				}
			}

			function keyListener(e) {
				switch (e.keyCode) {
					case 9:// tab key
						if (e.type == 'keydown') {
							if ($(results).css('display') == 'block' && $(results).find('.resultItem.hover').length) {
								e.preventDefault();
								$(currentSelection).trigger('click');
							}
						}
						return true;
					case 13: // return key
						if (e.type == 'keydown') {
							e.preventDefault();
							if ($(results).css('display') == 'block' && $(results).find('.resultItem.hover').length) {
								$(currentSelection).trigger('click');
							}
							return true;
						}
						return false;
					case 40:// down key
						if (e.type == 'keydown') {
							currentSelection = $(currentSelection).next().get(0);
							if (typeof currentSelection === 'undefined') {
								currentSelection = $('div.resultItem:first', results).get(0);
							}
							setHoverClass(currentSelection);
							if (currentSelection) {
								$('.results-wrap', results).scrollTop(currentSelection.offsetTop);
							}
						}
						return false;
					case 38:// up key
						if (e.type == 'keydown') {
							currentSelection = $(currentSelection).prev().get(0);
							if (typeof currentSelection === 'undefined') {
								currentSelection = $('div.resultItem:last', results).get(0);
							}
							setHoverClass(currentSelection);
							if (currentSelection) {
								$('.results-wrap', results).scrollTop(currentSelection.offsetTop);
							}
						}

						return false;
					default:
						if (e.type == 'keyup') {
							if (timeout) window.clearTimeout(timeout);
							//var runSuggest_var = function() {runSuggest.apply(this, [e])};
							//function aux() { runSuggest(); };
							//timeout = window.setTimeout(function () {runSuggest.apply(this, [e])}, settings.suggestTimeout);
							//if (obj.val()) {
							timeout = setTimeout(runAutocomplete, settings.suggestTimeout);
							//}
						}
				}
				setParentSelectorButtonState();
			}

			// Prepare the input box to show suggest results by adding in the events
			// that will initiate the search and placing the element on the page
			// that will show the results.
			$(results).addClass('jsonSuggestResults').
				css({
					//'top': (obj.position().top + obj.height() + 5) + 'px',
					'left': obj.position().left + 'px'//,
					//'width': (obj.width() + 12) + 'px'
				}).hide();

			if (settings.wrapPlacement) obj.parent().after(results);
			else obj.after(results);

			obj.after(parentSelector);

			obj.keydown(keyListener).keyup(keyListener).blur(function(e) {
				// We need to make sure we don't hide the result set
				// if the input blur event is called because of clicking on
				// a result item.
				var resPos = $(results).offset();
				resPos.bottom = resPos.top + $(results).height();
				resPos.right = resPos.left + $(results).width();

				//console.log(pageX + ':' + pageY);

				if (pageY < resPos.top || pageY > resPos.bottom || pageX < resPos.left || pageX > resPos.right) {
					$(results).hide();
				}
			}).focus(function(e) {
				if ($('div', results).length > 0) {
					$(results).show();
				}
			}).attr('autocomplete', 'off');

			$('body').mousemove(function(e) {pageX = e.pageX; pageY = e.pageY;});

			// Opera doesn't seem to assign a keyCode for the down
			// key on the keyup event. why?
			if ($.browser.opera) {
				obj.keydown(function(e) {
					if (e.keyCode === 40) { // up key
						return keyListener(e);
					}
				});
			}

			// Escape the not character if present so that it doesn't act in the regular expression
			settings.notCharacter = regexEscape(settings.notCharacter || '');
		});
	};
})(jQuery);