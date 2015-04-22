/*global $*/

(function() {
  'use strict';

  $.EzTags.MultipleSelects = $.EzTags.Base.extend({
    templates: {
      option: ['<option value="<%= tag.id %>" <%= selected %> ><%= tag.name %></option>'],
      select: ['<select class="js-tag-select form-control"></select>'],
      skeleton: [
      '<div class="selects"></div>'
      ]
    },


    initialize: function(){
      this.fetch_available_tags(function(){
        this.append_select();
      });
      this.$selects = this.$('.selects');
      this.on('change', '.js-tag-select', $.proxy(this.on_select, this));
    },


    on_select: function(e){
      var $select = $(e.target),
          id = $select.val(),
          tag = $select.data('linked_tag') || this.tags.items[0],
          new_tag;

      if(id){
        new_tag = this.available_tags.find(id);
        new_tag.select = $select;
        tag && this.remove(tag.id);
        this.add(new_tag);
        !tag && this.should_append_new_select();
        $select.data('linked_tag', new_tag);
      }else{
        if(tag){
          $select.siblings().filter(function() {
            return $(this).val() === '';
          }).length && tag.select.remove();
          this.remove(tag.id);
          $select.data('linked_tag', null);
        }
      }
      this.update_selects();
    },

    update_selects: function(){
      var self = this;
      this.$('option').removeAttr('disabled');
      $.each(this.tags.items, function(i, tag){
        self.$('option[value="'+tag.id+'"]').attr('disabled', true);
      });
    },

    should_append_new_select: function(){
      if(this.max_tags_limit_reached()){return;}
      this.append_select();
    },


    //TODO: implement real fetch and not autocomplete
    fetch_available_tags: function(done){
      var self = this;
      $.ez('ezjscNgRgm::fillSelect::' + this.opts.parentId, {}, function(data){
        self.available_tags = self.parse_remote_tags(data);
        done.call(self);
      });
    },

    parse_remote_tags: function(data, collection) {
      var tags = collection || new this.CollectionKlass(),
          self = this;

      $.each(data.content, function(i, raw){
        tags.add(self.parse_remote_tag(raw));
      });

      return tags;
    },

    parse_remote_tag: function(raw){
      return new this.TagKlass({
        name: raw.keyword,
        id: raw.id
      });
    },

    setup_select: function($select){
      var self = this, selected;
      var dummy_tag = new this.TagKlass({id: '', name:''});
      $select.append(self.render_template('option', {tag: dummy_tag, selected: null }));
      $.each(this.available_tags.items, function(i, tag){
        selected = self.tags.find(tag.id) ? 'selected="selected"' : '';
        $select.append(self.render_template('option', {tag: tag, selected: selected }));
      });
    },

    append_select: function(){
      var $select = $(this.render_template('select'));
      this.setup_select($select);
      this.$selects.append($select);
    }
  });



})();
