(function($) {
    var EZT = function () {
        var defaults = {
                minCharacters: 1,
                maxResults: 24,
                maxHeight: 150,
                suggestTimeout: 500,
                subtreeLimit: 0,
                hideRootTag: 0,
                maxTags: 0,
                isFilter: false,
                hasAddAccess: false,
                ezjscAutocomplete: 'ezjsctags::autocomplete',
                ezjscSuggest: 'ezjsctags::suggest',
                ezjscTranslations: 'ezjsctags::tagtranslations',
                locale: false,
                iconPath: false,
                jqmConfig: {modal:true, overlay:60, overlayClass:'whiteOverlay', onHide:function(hash) {hash.w.remove(); hash.o.remove();}},
                translations: {
                    selectedTags: 'SELECTEDTAGS',
                    loading: 'LOADING',
                    noSelectedTags: 'NOSELECTEDTAGS',
                    suggestedTags: 'SUGGESTEDTAGS',
                    noSuggestedTags: 'NOSUGGESTEDTAGS',
                    addNew: 'ADDNEW',
                    clickAddThisTag: 'CLICKADDTHISTAG',
                    removeTag: 'REMOVETAG',
                    translateTag: 'TRANSLATETAG',
                    existingTranslations: 'EXISTINGTRANSLATIONS',
                    noExistingTranslations: 'NOEXISTINGTRANSLATIONS',
                    addTranslation: 'ADDTRANSLATION',
                    cancel: 'CANCEL',
                    ok: 'OK'
                },
                templates: {
                    skeleton: [
                        '<div class="tagssuggest-ui">',
                            '<div class="tags-output">',
                                '<label><%=tr.selectedTags%>:</label>',
                                '<div class="tags-list tags-listed no-results">',
                                    '<p class="loading"><%=tr.loading%></p>',
                                    '<p class="no-results"><%=tr.noSelectedTags%>.</p>',
                                    '<ul class="float-break" />',
                                '</div>',
                            '</div>',
                            '<div class="tags-input">',
                                '<label><%=tr.suggestedTags%>:</label>',
                                '<div class="tags-list tags-suggested no-results">',
                                    '<p class="loading"><%=tr.loading%></p>',
                                    '<p class="no-results"><%=tr.noSuggestedTags%>.</p>',
                                    '<ul class="float-break" />',
                                '</div>',
                                '<div class="tagssuggestfieldwrap">',
                                    '<input class="tagssuggestfield" type="text" size="70" value="" autocomplete="off" />',
                                    '<div class="tagssuggestresults jsonSuggestResults"><div class="results-wrap" /></div>',
                                '</div>',
                                '<input type="button" value="<%=tr.addNew%>" class="button-add-tag button-disabled" disabled="disabled" />',
                            '</div>',
                        '</div>'
                    ],
                    suggestedItem: ['<li title="<%=tr.clickAddThisTag%>"><img src="<%=flagSrc%>"/>&nbsp;<%=content%></li>'],
                    selectedItem: ['<li><img src="<%=flagSrc%>" />&nbsp;<%=content%><a href="#" title="<%=tr.removeTag%>">&times;</a></li>'],
                    autocompleteItem: ['<div class="resultItem"><div><img src="<%=flagSrc%>"/>&nbsp;<%=content%><span><%=parent%></span></div></div>'],
                    translationSkeleton: [
                        '<label><%=tr.existingTranslations%>:</label>',
                        '<div class="translations">',
                            '<p class="no-results"><%=tr.noExistingTranslations%>.</p>',
                        '</div>',
                        '<label><%=tr.addTranslation%>:</label>',
                        '<div class="translation">',
                        '</div>',
                        '<div>',
                            '<input type="button" class="button button-disabled button-add-translation" value="<%=tr.ok%>" disabled="disabled" />',
                            '<input type="button" class="button button-cancel-translation" value="<%=tr.cancel%>" />',
                        '</div>'
                    ],
                    translationItem: ['<div><img src="<%=flagSrc%>" /><%=translation%></div>'],
                    translationField: ['<img src="<%=flagSrc%>" /><input type="text" value="" />'],
                    modal: [
                        '<div id="ezt-translation-modal" class="jqmDialog">',
                            '<div class="jqmdIn">',
                                '<div class="jqmdTC"><span class="jqmdTCLeft"></span><span class="jqDrag"><%=title%></span><span class="jqmdTCRight"></span></div>',
                                '<div class="jqmdBL"><div class="jqmdBR"><div class="jqmdBC"><div class="jqmdBCIn"></div></div></div></div>',
                                '<a href="#" class="jqmdX jqmClose"></a>',
                            '</div>',
                        '</div>'
                    ]
                }
            },
            pageX,
            pageY,
            tplCache = {},
            tpl = function(str, data) {
                var fn = !/\W/.test(str) ? tplCache[str] = tplCache[str] || tpl(document.getElementById(str).innerHTML) : new Function("obj", "var p=[];with(obj){p.push('" + str.replace(/[\r\t\n]/g, " ").split("<%").join("\t").replace(/((^|%>)[^\t]*)'/g, "$1\r").replace(/\t=(.*?)%>/g, "',$1,'").split("\t").join("');").split("%>").join("p.push('").split("\r").join("\\'") + "');}return p.join('');");
                return data ? fn(data) : fn;
            },
            addUI = function(options) {
                var markup = $(tpl(options.templates.skeleton.join(''), {tr:options.translations})), w = options.w,
                    tagsData = [w.find('.tagnames').val(), w.find('.tagpids').val(), w.find('.tagids').val(), w.find('.taglocales').val()];
                if (tagsData[0] && tagsData[1] && tagsData[2] && tagsData[3]) {
                    tagsData = $.map(tagsData, function(items){return [$.map(items.split('|#'), function(subitem){return $.trim(subitem);})];});
                    markup.find('.tags-listed').removeClass('no-results');
                    if (!options.hasAddAccess) markup.find('.button-add-tag').remove();
                    $.each(tagsData[0], function(i, value) {
                        addTagToList({'tag_name': value, 'tag_parent_id': tagsData[1][i], 'tag_id': tagsData[2][i], 'tag_locale': tagsData[3][i]}, markup.find('.tags-listed ul'), removeTagFromList, true, options);
                    });
                }
                options.w.append(markup).find('.tagssuggestresults').css({'left': markup.find('.tagssuggestfield').position().left + 'px'}).hide();
            },
            showHideInputElements = function(options) {
                if (options.maxTags > 0) {
                    var tagsInput = options.w.find('.tags-input');
                    if (options.w.find('li', '.tags-output').length >= options.maxTags) tagsInput.hide();
                    else tagsInput.show();
                }
            },
            getFlagSrc = function(locale, options) {return options.iconPath + locale + '.gif';},
            addTagToList = function(item, list, callback, selected, options) {
                var tag = $(tpl(selected ? options.templates.selectedItem.join('') : options.templates.suggestedItem.join(''), {
                        content:item.tag_name,
                        flagSrc:getFlagSrc(item.tag_locale, options),
                        tr:options.translations})).data('tag', item);
                // disable tag translator temporarily
                // if (selected && item.tag_locale !== options.locale) tag.addClass('untranslated').click(function(e) {openTranslator(tag, options);});
                if (selected) tag.find('a').click(function(e) {callback(tag, options); return false;});
                else tag.click(function(e) {callback(tag, options); return false;});
                list.append(tag).parent('.tags-list').removeClass('no-results');
            },
            removeTagFromList = function(tag, options) {
                $(tag).remove();
                updateValues(options);
                showHideInputElements(options);
            },
            moveTag = function(tag, options) {
                addTagToList(tag.data('tag'), options.w.find('.tags-listed ul'), removeTagFromList, true, options);
                removeTagFromList(tag, options);
            },
            updateValues = function(options) {
                var tagsData = ['', '', '', ''];//names, pids, ids, locale
                options.w.find('.tags-output li').each(function(i) {
                    var tagData = $(this).data('tag');
                    tagsData[0] += (tagsData[0] ? '|#' : '') + tagData.tag_name;
                    tagsData[1] += (tagsData[1] ? '|#' : '') + tagData.tag_parent_id;
                    tagsData[2] += (tagsData[2] ? '|#' : '') + tagData.tag_id;
                    tagsData[3] += (tagsData[3] ? '|#' : '') + tagData.tag_locale;
                });
                options.w.find('.tagnames').val(tagsData[0]).end().find('.tagpids').val(tagsData[1]).end().find('.tagids').val(tagsData[2]).end().find('.taglocales').val(tagsData[3]);
                if (!tagsData[0] && !tagsData[1] && !tagsData[2] && !tagsData[3]) options.w.find('.tags-listed').addClass('no-results');
                runSuggest(options);
            },
            emptyResults = function(options) {options.w.find('.results-wrap').scrollTop(0).empty();},
            hideResults = function(options) {if (!options.isFilter) options.w.find('.tagssuggestresults').hide();},
            showResults = function(options) {if (options.w.find('.results-wrap div').length) options.w.find('.tagssuggestresults').show();},
            emptyAndHideResults = function(options) {emptyResults(options); hideResults(options)},
            openParentSelector = function(options) {options.w.siblings('.parent-selector-tree:eq(0)').jqmShow();},
            openTranslator = function(tag, options) {
                if (!$('#ezt-translation-modal').length) {
                    options.w.append(tpl(options.templates.modal.join(''), {title:options.translations.addTranslation, tr:options.translations}));
                    var modal = $('#ezt-translation-modal').jqm(options.jqmConfig).jqDrag('.jqDrag');
                    modal.find('.jqmdBCIn').append(tpl(options.templates.translationSkeleton.join(''), {tr:options.translations})).find('.button-cancel-translation').click(function() {$('#ezt-translation-modal').jqmHide();});
                    $.ez(options.ezjscTranslations, {'tag_id': tag.data('tag').tag_id}, function(data) {
                        var translations = data.content.translations;
                        if (!translations.length) modal.find('.translations').addClass('no-results');
                        for (i in translations) {
                            modal.find('.translations').append(tpl(options.templates.translationItem.join(''), {flagSrc:getFlagSrc(translations[i].locale, options), translation:translations[i].translation, tr:options.translations}));
                        }
                        modal.find('.translation').append(tpl(options.templates.translationField.join(''), {flagSrc:getFlagSrc(options.locale, options), tr:options.translations}));
                    });
                }
                $('#ezt-translation-modal').jqmShow();
            },
            bindParentSelectorTreeEvents = function(options) {
                var parentSelector = options.w.siblings('.parent-selector-tree:eq(0)');
                $('#' + parentSelector.attr('id') + ' .contentstructure a:not([class^=openclose])').live('click', function(e) {
                    addTagToList({'tag_name': $.trim(options.w.find('.tagssuggestfield').val()), 'tag_parent_id': $(this).attr('rel'), 'tag_id': '0', 'tag_locale': options.locale}, options.w.find('.tags-listed ul'), removeTagFromList, true, options);
                    showHideInputElements(options);
                    updateValues(options);
                    clearTagSearchField(options);
                    emptyAndHideResults(options);
                    if (options.isFilter) runAutocomplete(options);
                    parentSelector.jqmHide();
                    return false;
                });
            },
            setParentSelectorButtonState = function(options) {
                var button = options.w.find('.button-add-tag');
                if ($.trim(options.w.find('.tagssuggestfield').val())) button.removeClass('button-disabled').addClass('button').removeAttr('disabled');
                else button.removeClass('button').addClass('button-disabled').attr('disabled', true);
            },
            clearTagSearchField = function(options) {
                options.w.find('.tagssuggestfield').val('').data('value', '');
                setParentSelectorButtonState(options);
            },
            selectResultItem = function(options, item) {
                emptyAndHideResults(options);
                addTagToList(item, options.w.find('.tags-listed ul'), removeTagFromList, true, options);
                showHideInputElements(options);
                updateValues(options);
                clearTagSearchField(options);
                if (options.isFilter) runAutocomplete(options);
            },
            setHoverClass = function(el, options) {
                options.w.find('.resultItem').removeClass('hover');
                $(el).addClass('hover');
            },
            runSuggest = function(options) {
                var tagsSuggested = options.w.find('.tags-suggested ul').empty();
                var tag_ids = options.w.find('.tagids').val();
                if (tag_ids) {
                    tagsSuggested.parent('.tags-list').removeClass('no-results').addClass('loading');
                    $.ez(options.ezjscSuggest, {'tag_ids': tag_ids, 'subtree_limit': options.subtreeLimit, 'hide_root_tag': options.hideRootTag, 'locale': options.locale}, function(data) {
                        var tags = data.content.tags;
                        tagsSuggested.parent('.tags-list').removeClass('loading');
                        if (!tags.length) {
                            tagsSuggested.parent('.tags-list').addClass('no-results');
                            return true;
                        }
                        for (i in tags) addTagToList(tags[i], tagsSuggested, moveTag, false, options);
                    });
                }
                else tagsSuggested.parent('.tags-list').addClass('no-results').removeClass('loading');
            },
            runAutocomplete = function(options) {
                var suggestFieldVal = options.w.find('.tagssuggestfield').val();
                if (suggestFieldVal || options.isFilter)
                    $.ez(options.ezjscAutocomplete, {'search_string': suggestFieldVal, 'subtree_limit': options.subtreeLimit, 'hide_root_tag': options.hideRootTag, 'locale': options.locale}, function(data) {
                        var oddRow = false, resultsWrap = options.w.find('.results-wrap'), tags = data.content.tags;
                        emptyAndHideResults(options);
                        for (i in tags) {
                            if (i >= options.maxResults) break;
                            $(tpl(options.templates.autocompleteItem.join(''), {content:tags[i].tag_name, parent:tags[i].tag_parent_name, flagSrc:getFlagSrc(tags[i].tag_locale, options), tr:options.translations})).
                                addClass((oddRow = !oddRow) ? 'odd' : 'even').
                                click(function(n) {return function() {selectResultItem(options, tags[n]);};}(i)).
                                mouseover(function() {setHoverClass(this, options);}).
                                appendTo(resultsWrap);
                        }
                        if ($('div', resultsWrap).length > 0) {
                            resultsWrap.height('auto').parent().show();
                            if (resultsWrap.height() > options.maxHeight) resultsWrap.height(options.maxHeight);
                        }
                    });
                else emptyAndHideResults(options);
            },
            setScrollOffset = function(results, resultsWrap, currentSelection, down, options) {
                setHoverClass(currentSelection, options);
                var csot = currentSelection.offsetTop,
                    rwst = resultsWrap.scrollTop(),
                    csh = $(currentSelection).height(),
                    rh = results.height();//,
                    //visible = csot >= rwst && (csot + csh) <= (rwst + rh);
                if (csot < rwst || (csot + csh) > (rwst + rh)) resultsWrap.scrollTop(csot + (down ? (csh - rh) : 0));
            },
            keyListener = function(e, options) {
                var results = options.w.find('.tagssuggestresults'), resultsWrap = results.find('.results-wrap');
                switch (e.keyCode) {
                    case 9://tab key
                    case 13://return key
                        e.preventDefault();
                        if (e.type === 'keydown') results.filter(':visible').find('.hover').click();
                        return false;
                    case 38://up key
                    case 40://down key
                        if (e.type === 'keydown') {
                            var direction = e.keyCode === 40,
                                currentSelection = direction ? results.find('.hover').next().get(0) || resultsWrap.find('.resultItem:first').get(0) : results.find('.hover').prev().get(0) || resultsWrap.find('.resultItem:last').get(0);
                            setScrollOffset(results, resultsWrap, currentSelection, direction, options);
                        }
                        return false;
                }
                var target = $(e.currentTarget);
                if (e.type === 'keyup' && target.val() !== target.data('value')) {
                    window.clearTimeout(target.data('timeoutHandler'));
                    target.data('value', target.val()).data('timeoutHandler', setTimeout(function() {runAutocomplete(options);}, options.suggestTimeout));
                }
                setParentSelectorButtonState(options);
            };
        $(document).mousemove(function(e) {pageX = e.pageX; pageY = e.pageY;});
        $(function() {
            var parent_selector_buttons = $('[id^="eztags-parent-selector-button-"]'),
                parent_selector_tree = $('.parent-selector-tree'),
                parent_id, parent_keyword;
            if (parent_selector_buttons.length > 0 && parent_selector_tree.length > 0) {
                parent_selector_buttons.click(function() {
                    parent_id = $('#' + $(this).attr('id').replace('eztags-parent-selector-button-', 'eztags_parent_id_'));
                    parent_keyword = $('#' + $(this).attr('id').replace('eztags-parent-selector-button-', 'eztags_parent_keyword_'));
                    parent_selector_tree.jqmShow(); return false;
                });
                parent_selector_tree.jqm({modal:true, overlay:60, overlayClass:'whiteOverlay'}).jqDrag('.jqDrag');
                function getParentTagHierarchy(tag, i) {
                    if (tag.attr('rel') === '0') if (i === 0) return '(no parent)'; else return '';
                    var parent = getParentTagHierarchy(tag.parents('div:first').prev('a'), ++i);
                    return (parent ? parent + ' / ' : '') + tag.parent().find('span').html();
                }
                $('.contentstructure a:not([class^="openclose"])').live('click', function(e) {
                    var tag = $(this);
                    if (tag.parents('li.disabled').length) return false;
                    parent_keyword.html(getParentTagHierarchy(tag, 0));
                    parent_id.val(tag.attr('rel'));
                    parent_selector_tree.jqmHide();
                    return false;
                });
            }
        });
        return {
            init: function(opts) {
                opts = $.extend(true, {}, defaults, opts||{});
                return this.each(function(i, widget) {
                    var widget = $(widget), options = $.extend({}, opts);
                    options.w = widget;
                    addUI(options);
                    bindParentSelectorTreeEvents(options);
                    widget.find('.button-add-tag').click(function() {openParentSelector(options);});
                    runSuggest(options);
                    if (options.isFilter) runAutocomplete(options);
                    var results = widget.find('.tagssuggestresults');
                    widget.find('.tagssuggestfield').bind('keydown keyup', function(e) {keyListener(e, options);}).blur(function(e) {
                        var resPos = results.offset();
                        resPos.bottom = resPos.top + results.height();
                        resPos.right = resPos.left + results.width();
                        if (pageY < resPos.top || pageY > resPos.bottom || pageX < resPos.left || pageX > resPos.right) hideResults(options);
                    }).focus(function(e) {showResults(options)});
                    showHideInputElements(options);
                });
            }
        };
    }();
    $.fn.extend({
        eZTags: EZT.init
    });
})(jQuery);
