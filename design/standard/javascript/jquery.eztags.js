/*global $*/

(function() {
  'use strict';

  // Borrowed from backbonejs with few tweeks
  var klass_extend = function(protoProps, staticProps) {
    var parent = this;
    var child;

    // The constructor function for the new subclass is either defined by you
    // (the "constructor" property in your `extend` definition), or defaulted
    // by us to simply call the parent's constructor.
    if (protoProps && (protoProps.hasOwnProperty('constructor'))) {
      child = protoProps.constructor;
    } else {
      child = function(){ return parent.apply(this, arguments); };
    }

    // Add static properties to the constructor function, if supplied.
    $.extend(child, parent, staticProps);

    // Set the prototype chain to inherit from `parent`, without calling
    // `parent`'s constructor function.
    var Surrogate = function(){ this.constructor = child; };
    Surrogate.prototype = parent.prototype;
    child.prototype = new Surrogate();

    // Add prototype properties (instance properties) to the subclass,
    // if supplied.
    if (protoProps){ $.extend(child.prototype, protoProps);}

    // Set a convenience property in case the parent's prototype is needed
    // later.
    child.__super__ = parent.prototype;

    return child;
  };


  //Declare namspace

  var EzTags = {
    debouncer: function(fn, delay, context){
      var to;
      return function(){
        to && clearTimeout(to);
        to = setTimeout($.proxy.apply($, [fn, context].concat(Array.prototype.slice.apply(arguments))), delay);
      };
    },

    key: {
      ESC: 27,
      TAB: 9,
      RETURN: 13,
      LEFT: 37,
      UP: 38,
      RIGHT: 39,
      DOWN: 40,
      SPACE: 32
    },

    is_key: function(e, keys){
      var self = this;
      !$.isArray(keys) && (keys = [keys]);
      keys = $.map(keys, function(name){ return self.key[name]; });
      return $.inArray(e.which, keys) > -1;
    }
  };


  // Tag ========================================================
  // attributes
  var Tag = function(attributes){
    if(attributes instanceof Tag){return attributes;}
    $.extend(this, {
      id: 0,
      parent_name: '',
      cid: this.constructor.uid(),
      flagSrc: this.constructor.iconPath + attributes.locale + '.gif'
    }, attributes);
  };

  Tag.id = 0;
  Tag.uid = function(){ return 'c'+(++this.id); };
  Tag.prototype.remove = function() { return this.tags_suggest.remove(this.cid); };
  Tag.prototype.parent = function() {
    return this.collection.find_by('id', this.parent_id);
  };

  Tag.prototype.parents = function() {
    if(this._parents){return this._parents;}
    var tag = this;
    this._parents = [];

    while(tag){
      tag = tag.parent();
      tag && this._parents.unshift(tag);
    }
    console.log('parents', this._parents);
    return this._parents;
  };

  Tag.prototype.self_and_parents = function() {
    return this.parents().concat([this]);
  };





  //Simple collection class ========================================================
  var Collection = function(){
    this.items = [];
    this.indexed = {};
  };


  Collection.prototype.find_by_with_index = function(attr, value) {
    var tag = null;
    for (var i = this.items.length - 1; i >= 0; i--) {
      if(this.items[i][attr] === value){
        tag = this.items[i];
        break;
      }
    }
    return tag ? {item: tag, index: i} : null;
  };

  Collection.prototype.filter = function(iterator) {
    return $.grep(this.items, iterator);
  };

  Collection.prototype.find_by = function() {
    var result = this.find_by_with_index.apply(this, arguments);
    return result ? result.item : null;
  };

  Collection.prototype.add = function(item){
    if(!item){throw new Error('Item is not provided');}
    if(item.id && this.indexed[item.id]){return null;} //if exists
    item.collection = this;
    this.items.push(item);
    this.index_add(item);
    return item;
  };

  Collection.prototype.remove = function(item) {
    this.items.splice(this.find_by_with_index('cid', item.cid).index, 1);
    this.index_remove(item);
    item.collection = null;
    return item;
  };


  Collection.prototype.index_add = function(item) {
    this.indexed[item.cid] = item;
    this.indexed[item.id] = item;
  };

  Collection.prototype.index_remove = function(item) {
    delete this.indexed[item.cid];
    delete this.indexed[item.id];
  };

  Collection.prototype.find = function(id_or_cid) {
    return this.indexed[id_or_cid] || null;
  };

  Collection.prototype.length = function() {
    return this.items.length;
  };

  Collection.prototype.push = function(item) { return this.add(item); };

  Collection.prototype.sort_by = function(ids, atomic){
    var self = this, items = $.map(ids, function(id){ return self.find(id); });
    atomic && (this.items = items);
    return items;
  };


  Collection.prototype.clear = function() {
    this.indexed = {};
    this.items = [];
  };





  // TagSuggest ========================================================
  var Base = function(el, opts) {
    opts || (opts = {});
    this.$el = $(el);
    this.opts = $.extend(true, {}, this.constructor.defaults, opts, this.$el.data());
    console.log(this.opts);
    this.opts.templates = $.extend({}, this.constructor.defaults.templates, this.templates, opts.templates);
    this.group_id = this.$el.attr('id').replace(this.opts.main_id_prefix, '');
    this.TagKlass = this.opts.TagKlass || Tag;
    this.TagKlass.iconPath = this.opts.iconPath || '';


    this.CollectionKlass = this.opts.CollectionKlass || Collection;

    this.tags = new this.CollectionKlass();

    this.setup_ui();
    this.setup_events();
    this.unserialize();
    this.setup_tree_picker();
    this.render_tags();
    this.fetch_suggestions();
    this.initialize && this.initialize();
  };



  Base.defaults = {
    main_id_prefix: 'eztags',

    minCharacters: 1, //++
    maxResults: 24, //++
    maxHeight: 150, //++
    suggestTimeout: 500, //++
    subtreeLimit: 0, //++
    hideRootTag: 0, //++
    maxTags: 0, // ++
    isFilter: false,
    hasAddAccess: false, //++
    ezjscAutocomplete: 'ezjsctags::autocomplete', //++
    ezjscSuggest: 'ezjsctags::suggest', //++
    locale: null, //++
    iconPath: null, //++

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
                  '<label><%=tr.selectedTags%>:</label>'
,                  '<div class="tags-list tags-listed no-results">',
                      '<p class="loading"><%=tr.loading%></p>',
                      '<p class="no-results"><%=tr.noSelectedTags%>.</p>',
                      '<ul class="float-break js-tags-selected"></ul>',
                  '</div>',
              '</div>',
              '<div class="tags-input">',
                  '<label><%=tr.suggestedTags%>:</label>',
                  '<div class="tags-list tags-suggested no-results">',
                      '<p class="loading"><%=tr.loading%></p>',
                      '<p class="no-results"><%=tr.noSuggestedTags%>.</p>',
                      '<ul class="float-break js-tags-suggested">',
                  '</div>',
                  '<div class="tagssuggestfieldwrap">',
                      '<input class="tagssuggestfield tags-input-field" type="text" size="70" value="" autocomplete="off" />',
                      '<div class="tagssuggestresults jsonSuggestResults"><div class="results-wrap" /></div>',
                  '</div>',
                  '<input type="button" value="<%=tr.addNew%>" class="button-add-tag button-disabled" disabled="disabled" />',
              '</div>',
          '</div>'
      ],
      suggestedItem: ['<li class="js-suggested-item" data-cid="<%= tag.cid %>" title="<%=tr.clickAddThisTag%>"><img src="<%=tag.flagSrc %>"/><%=tag.name%></li>'],
      selectedItem: ['<li data-cid="<%= tag.cid %>"><img src="<%=tag.flagSrc %>" /><%=tag.name%><a href="#" class="js-tags-remove" title="<%=tr.removeTag%>">&times;</a></li>'],
      autocompleteItem: ['<div data-cid="<%= tag.cid %>" class="js-autocomplete-item resultItem"><a href="#"><img src="<%=tag.flagSrc %>"/><%=tag.name%><span><%= tag.parent_name %></span></a></div>'],
    }
  };

  //TODO: tplCache ????
  Base.prototype.tpl = function(str, data){
    var fn = !/\W/.test(str) ? this.tplCache[str] = this.tplCache[str] || this.tpl($(str, scope).html()) : new Function("obj", "var p=[];with(obj){p.push('" + str.replace(/[\r\t\n]/g, " ").split("<%").join("\t").replace(/((^|%>)[^\t]*)'/g, "$1\r").replace(/\t=(.*?)%>/g, "',$1,'").split("\t").join("');").split("%>").join("p.push('").split("\r").join("\\'") + "');}return p.join('');"); /*jshint ignore:line*/
    return data ? fn(data) : fn;
  };

  Base.prototype.render_template = function(name, data) {
    var t = (this.opts.templates[name] = this.opts.templates[name] || $('.'+name, this.$el).html());
    return this.tpl(t.join ? t.join('') : t, $.extend({}, data, {tr: this.opts.translations}));
  };

  Base.prototype.render_skeleton = function() {
    var $markup = $(this.render_template('skeleton'));
    !this.opts.hasAddAccess && $markup.find('.button-add-tag').remove();
    $markup.find('.tags-listed').removeClass('no-results');
    this.$el.append($markup);
  };


  Base.prototype.setup_ui = function() {
    this.render_skeleton();

    this.$input = this.$('.tags-input-field');
    this.$add_button = this.$('.button-add-tag');

    this.$hidden_inputs = {};
    this.$hidden_inputs.tagids     = this.$('.tagids');
    this.$hidden_inputs.tagnames   = this.$('.tagnames');
    this.$hidden_inputs.tagpids    = this.$('.tagpids');
    this.$hidden_inputs.taglocales = this.$('.taglocales');

    this.$tree_picker_element = $('#parent-selector-tree-'+this.group_id);
    this.$selected_tags = this.$('.js-tags-selected');
    this.$autocomplete_tags = this.$('.results-wrap');

    this.$suggested_tags = this.$('.js-tags-suggested');

  };


  //Suggest ======================================================================================

  Base.prototype.fetch_suggestions = function(e) {
    if(!this.tags.length){return;}


    $.ez(this.opts.ezjscSuggest,
      {
        tag_ids: this.serialize().tagids,
        subtree_limit: this.opts.subtreeLimit,
        hide_root_tag: this.hideRootTag,
        locale: this.opts.locale
      },
      $.proxy(this.after_fetch_suggestions, this) //callback
    );


    //$.get('suggest.json', $.proxy(this.after_fetch_suggestions, this));
  };

  Base.prototype.fetch_autocomplete = function(e) {
    if(EzTags.is_key(e, ['UP', 'DOWN', 'LEFT', 'RIGHT', 'ESC', 'RETURN'])){return;}
    var search_string = this.get_tag_name_from_input();

    if(search_string.length < this.opts.minCharacters){return;}
    if(search_string === this.last_search_string){
      this.render_autocomplete_tags();
      this.show_autocomplete();
      return;
    }
    this.last_search_string = search_string;

    $.ez(this.opts.ezjscAutocomplete, {
      search_string: search_string,
      subtree_limit: this.opts.subtreeLimit,
      hide_root_tag: this.opts.hideRootTag,
      locale: this.opts.locale
    }, $.proxy(this.after_fetch_autocomplete, this));


    //$.get('autocomplete.json', $.proxy(this.after_fetch_autocomplete, this));
  };

  Base.prototype.after_fetch_autocomplete = function(data) {
    var tags = data.content.tags, self = this, tag;
    tags = tags.slice(0, this.opts.maxResults); //TODO: shouldn't administration take care of this?
    this.$autocomplete_tags.empty().parent().hide();

    this.autocomplete_tags = new this.CollectionKlass();
    $.each(tags, function(i, raw){
      tag = new self.TagKlass({
        parent_id: raw.tag_parent_id,
        parent_name: raw.tag_parent_name, //NOTE: why is here parent_name while on selected tags we don't have it ???
        name: raw.tag_name,
        id: raw.tag_id,
        locale: raw.tag_locale
      });
      self.autocomplete_tags.add(tag);
    });

    this.render_autocomplete_tags();
    this.show_autocomplete();

  };


  Base.prototype.render_autocomplete_tags = function() {
    var self = this;
    var items = $.map(this.available_autocomplete_tags(), function(tag){
      return self.render_template('autocompleteItem', {tag: tag});
    });
    this.$autocomplete_tags.html(items);
  };


  Base.prototype.show_autocomplete = function() {
    var available_autocomplete_tags = this.available_autocomplete_tags();
    if(!available_autocomplete_tags.length || this.tree_picker_open){return;}
    this.$autocomplete_tags.height('auto').parent().show();
    if (this.$autocomplete_tags.height() > this.opts.maxHeight){ this.$autocomplete_tags.height(this.opts.maxHeight);}
  };


  Base.prototype.available_autocomplete_tags = function() {
    var self = this;
    return $.map(this.autocomplete_tags.items, function(tag){
      if(!self.tags.find(tag.id)){ return tag; }
    });
  };


  Base.prototype.close_autocomplete = function() {
    this.$('.results-wrap').html('').parent().hide();
  };


  Base.prototype.after_fetch_suggestions = function(data) {
    var tag, self = this;
    this.suggested_tags = new this.CollectionKlass();
    $.each(data.content.tags, function(i, raw){
      tag = new self.TagKlass({
        parent_id: raw.tag_parent_id,
        parent_name: raw.tag_parent_name, //NOTE: why is here parent_name while on selected tags we don't have it ???
        name: raw.tag_name,
        id: raw.tag_id,
        locale: raw.tag_locale
      });
      self.suggested_tags.add(tag);
    });
    this.render_suggested_tags();
    //TODO: show_hide_loader
  };



  Base.prototype.parse_remote_tags = function(data, collection) {
    var tags = collection || new this.CollectionKlass(),
        self = this;

    $.each(data.content.tags, function(i, raw){
      tags.add(self.parse_remote_tag(raw));
    });

    return tags;
  };


  Base.prototype.parse_remote_tag = function(raw) {
    return new this.TagKlass({
        parent_id: raw.tag_parent_id,
        parent_name: raw.tag_parent_name,
        name: raw.tag_name,
        id: raw.tag_id,
        locale: raw.tag_locale,
        depth: raw.tag_depth,
        empty: raw.tag_empty
      });
  };

  Base.prototype.enable_or_disable_add_button = function(){
    this.get_tag_name_from_input() ? this.enable_add_button() : this.disable_add_button();
  };


  Base.prototype.enable_add_button = function() {
    this.$add_button.removeClass('button-disabled').addClass('button').removeAttr('disabled');
  };

  Base.prototype.disable_add_button = function() {
    this.$add_button.addClass('button-disabled').removeClass('button').attr('disabled', true);
  };


  Base.prototype.setup_events = function() {
    this.$add_button.on('click', $.proxy(this.handler_add_buton, this));
    this.$el.on('click', '.js-tags-remove', $.proxy(this.handler_remove_buton, this));
    this.$el.on('click', '.js-suggested-item', $.proxy(this.handler_suggested_tag, this));
    this.$el.on('click', '.js-autocomplete-item', $.proxy(this.handler_autocomplete_tag, this));
    this.$input.on('keyup', $.proxy(this.enable_or_disable_add_button, this));
    this.$input.on('keyup', EzTags.debouncer(this.fetch_autocomplete, this.opts.suggestTimeout, this));
    this.$input.on('keydown', $.proxy(this.navigate_autocomplete_dropdown, this));
    this.$autocomplete_tags.on('keydown', $.proxy(this.navigate_autocomplete_dropdown, this));
    this.on('add:after', $.proxy(this.close_autocomplete, this) );

    this.setup_tree_picker_events();
    this.setup_sortable();
  };

  Base.prototype.handler_autocomplete_tag = function(e){
    e.preventDefault();
    var tag = this.autocomplete_tags.find($(e.target).closest('[data-cid]').data('cid'));
    this.add(tag);
  };

  Base.prototype.navigate_autocomplete_dropdown = function(e) {
    if(EzTags.is_key(e, 'ESC')){ this.close_autocomplete();}

    if(!EzTags.is_key(e, ['UP', 'DOWN', 'SPACE'])){return;}
    var $items = this.$autocomplete_tags.find('a');

    if (!$items.length){ return;}

    //Prevent page from moving
    e.preventDefault();
    e.stopPropagation();

    var index = $items.index(e.target);
    if(e.which === 'SPACE' && index >= 0){
      $items.eq(index).trigger('click');
    }

    EzTags.is_key(e, 'UP') && index >= 0                   && index--;
    EzTags.is_key(e, 'DOWN') && index < $items.length - 1  && index++;


    if(index > -1){
      $items.eq(index).trigger('focus');
    }else{
      this.$input.trigger('focus');
    }
  };

  Base.prototype.handler_suggested_tag = function(e){
    e.preventDefault();
    if(this.max_tags_limit_reached()){return;}
    var tag = this.suggested_tags.find($(e.target).closest('[data-cid]').data('cid'));
    this.suggested_tags.remove(tag);
    this.add(tag);
    this.render_suggested_tags();
  };


  Base.prototype.handler_add_buton = function() {

    this.new_tag_attributes = {
      name: this.get_tag_name_from_input(),
      locale: this.opts.locale
    };

    if(!this._validate(this.new_tag_attributes)){ return; }
    this.show_tree_picker();
    this.close_autocomplete();

  };

  Base.prototype.handler_remove_buton = function(e){
    e.preventDefault();
    return this.remove($(e.target).closest('[data-cid]').data('cid'));
  };


  // Parent picker =================================================================================
  Base.prototype.show_tree_picker = function() {
    this.$tree_picker_element.jqmShow();
  };

  Base.prototype.hide_tree_picker = function() {
    this.$tree_picker_element.jqmHide();
  };


  Base.prototype.setup_tree_picker = function() {
    var self = this;
    this.$tree_picker_element.jqm({
      modal:true,
      overlay:60,
      overlayClass: 'whiteOverlay',
      onShow: function(){
        self.tree_picker_open = true;
        $.jqm.params.onShow.apply(this, arguments);
      },
      onHide: function(){
        self.tree_picker_open = false;
        $.jqm.params.onHide.apply(this, arguments);
      }
    });
    //.jqDrag('.jqDrag');
  };


  Base.prototype.setup_tree_picker_events = function() {
    var self = this;
    this.$tree_picker_element.on('click', 'a', function(e){
      e.preventDefault();
      self.select_parent_id_from_tree_picker($(this).attr('rel')); //parent_id is on rel attribute
    });
  };


  Base.prototype.render_tags = function() {
    var self = this;
    var tags = $.map(this.tags.items, function(tag){
      return self.render_template('selectedItem', {tag: tag});
    });
    this.$selected_tags.html(tags);
  };

  Base.prototype.render_suggested_tags = function() {
    var self = this;
    var tags = $.map(this.suggested_tags.items, function(tag){
      return self.render_template('suggestedItem', {tag: tag});
    });
    this.$suggested_tags.html(tags);
  };


  Base.prototype.select_parent_id_from_tree_picker = function(parent_id){
    this.new_tag_attributes.parent_id = parent_id;
    this.add(this.new_tag_attributes);
  };



  Base.prototype.add = function(attributes, opts) {
    opts || (opts = {});
    if(this.max_tags_limit_reached()){return;}
    var tag = new this.TagKlass(attributes);
    this.trigger('add:before', {tag: tag}, opts);
    this.tags.add(tag);
    tag.tags_suggest = this;
    this.max_tags_handler();

    this.trigger('add:after', {tag: tag}, opts);
    this.after_add(opts);
    return tag;
  };


  Base.prototype.max_tags_handler = function() {
    this.$input.attr('disabled', this.max_tags_limit_reached());
  };

  Base.prototype.max_tags_limit_reached = function() {
    return this.opts.maxTags ? this.tags.length() >= this.opts.maxTags : false;
  };


  Base.prototype.remove = function(id) {
    var tag = this.tags.find(id);

    if(tag === null){return null;}

    this.trigger('remove:before', {tag: tag});
    this.tags.remove(tag);
    this.max_tags_handler();
    this.trigger('remove:after', {tag: tag});
    this.after_remove();
    return tag;
  };

  Base.prototype.add_only_one = function(tag) {
    this.tags.clear();
    this.add(tag);
  };



  Base.prototype.exists = function(name) {
    return this.tags.find_by('name', name) !== null;
  };

  Base.prototype.valid = function(attributes){
    return !this.exists(attributes.name);
  };


  Base.prototype._validate = function(attributes) {
    var result = this.valid(attributes);
    var event = 'tag:' + [result ? 'valid' : 'invalid'];
    this.trigger(event, {attributes: attributes});
    return result;
  };


  Base.prototype.after_add = function(opts) {
    opts || (opts = {});
    if(opts.silent){return;}
    this.update_inputs();
    this.render_tags();
  };

  Base.prototype.after_remove = function() {
    this.update_inputs();
    this.render_tags();
  };

  Base.prototype.serialize = function() {
    var data = {
      tagids:     [],
      taglocales: [],
      tagpids:    [],
      tagnames:   []
    };

    $.each(this.tags.items, function(index, tag){
      data.tagids.push(tag.id);
      data.taglocales.push(tag.locale);
      data.tagpids.push(tag.parent_id);
      data.tagnames.push(tag.name);
    });

    return data;
  };

  Base.prototype.unserialize = function() {
    var self = this;
     var ids  =   this.parse_hidden_input('tagids');
     var names =  this.parse_hidden_input('tagnames');
     var locales =  this.parse_hidden_input('taglocales');
     var pids =  this.parse_hidden_input('tagpids');

    $.each(ids, function(i, id){
      self.add({
        id: id,
        name: names[i],
        locale: locales[i],
        parent_id: pids[i]
      }, {silent: true});
    });

  };


  Base.prototype.parse_hidden_input = function(name) {
    var val = $.trim(this.$hidden_inputs[name].val());
    return val ? val.split('|#') : [];
  };


  Base.prototype.update_inputs = function() {
    var self = this;
    $.each(this.serialize(), function(k, v){
      self.$hidden_inputs[k].val(v.join('|#'));
    });
  };





  Base.prototype.get_tag_name_from_input = function() {
    return $.trim(this.$input.val());
  };

  Base.prototype.clear_input = function() {
    return this.$input.val('');
  };



  Base.prototype.setup_sortable = function() {
    var self = this;
    this.$selected_tags.sortable({
      update: function(/*event, ui*/){
        var new_order = $(this).sortable('toArray', {attribute: 'data-cid'});
        self.on_sortable_update(new_order);
      }
    });
  };


  Base.prototype.on_sortable_update = function(new_order){
    this.tags.sort_by(new_order, true);
    this.update_inputs();
  };


  Base.prototype.destroy = function() {
    //TODO: implement destroy
  };




  Tag.extend = Collection.extend = Base.extend = klass_extend;



  /*Proxy jQuery methods:  trigger, on, off, $ */
  Base.prototype.trigger = function(event, data, opts) {
    if(opts && opts.silent){return;}
    this.$el.trigger(event, $.extend({instance: this}, data));
  };

  Base.prototype.on = function() {
    this.$el.on.apply(this.$el, arguments);
  };

  Base.prototype.off = function() {
    this.$el.off.apply(this.$el, arguments);
  };

  Base.prototype.$ = function(selector){
    return this.$el.find(selector);
  };


  //Exports
  EzTags.Tag = Tag;
  EzTags.Collection = Collection;
  EzTags.Base = Base;



  EzTags.Normal = EzTags.Base.extend({

    after_add: function(opts) {
      opts || (opts = {});
      if(opts.silent){return;}
      this.update_inputs();
      this.$input.val('');
      this.hide_tree_picker();
      this.render_tags();
      this.new_tag_attributes = {};
      EzTags.debouncer(this.fetch_suggestions, this.opts.suggestTimeout, this);
    },

    after_remove: function() {
      this.update_inputs();
      this.render_tags();
      EzTags.debouncer(this.fetch_suggestions, this.opts.suggestTimeout, this);
    }

  });




  //Setup jquery plugin
  var plugin = 'EzTags';

  //Expose as jquery plugin
  $.fn[plugin] = function(options) {
    var method = typeof options === 'string' && options;
    $(this).each(function() {
      var $this = $(this);
      var data = $this.data();
      var instance = data.instance;
      var builder = data.builder || (options && options.builder) || 'Normal';
      if (instance) {
        method && instance[method]();
        return;
      }
      instance = new EzTags[builder](this, options);
      $this.data(plugin, instance);
    });
    return this;
  };

  //Expose class
  $[plugin] = EzTags;


  //Auto initialize
  $(function(){
    $('[data-eztags]').EzTags();
  });


})();
