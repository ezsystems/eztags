/*global $*/

(function() {
  'use strict';

  $.EzTags.Select = $.EzTags.Base.extend({
    templates: {
      option: ['<option value="<%= tag.id %>" <%= selected %> ><%= tag.name %></option>'],
      select: ['<select class="js-tag-select form-control"></select>'],
      skeleton: [
      '<div class="selects"></div>'
      ]
    },


    initialize: function(){
      var self = this;
      this.fetch_available_tags(function(){
        if(this.tags.length()){
          $.each(this.tags.items, function(i, tag){
            console.log(tag.name);
            self.append_select(tag);
            self.update_selects();
          });
        }else{
          self.append_select();
        }
      });
      this.$selects = this.$('.selects');
      this.on('change', '.js-tag-select', $.proxy(this.on_select, this));
    },


    on_select: function(e){
      var $select = $(e.target),
          id = $select.val(),
          tag = $select.data('linked_tag'),
          new_tag;

      if(id){
        new_tag = this.available_tags.find(id);
        tag && this.remove(tag.id);
        this.add(new_tag);
        !tag && this.should_append_new_select();
        this.link_tag_and_select(new_tag, $select);
      }else{
        if(tag){
          $select.siblings().filter(function() {
            return $(this).val() === '';
          }).length && tag.select.remove();
          this.remove(tag.id);
          this.unlink_tag_and_select(tag, $select);
        }
      }
      this.update_selects();
    },

    link_tag_and_select: function(tag, select){
      tag.select = select;
      select.data('linked_tag', tag);
    },

    unlink_tag_and_select: function(tag, select){
      tag.select = null;
      select.data('linked_tag', null);
    },

    update_selects: function(){
      var self = this, $option, linked_tag;
      this.$('option').removeAttr('disabled').removeAttr('selected');
      this.$('select').each(function(i , select){
        linked_tag = $(this).data('linked_tag');
        $.each(self.tags.items, function(i, tag){
          $option = $('option[value="'+tag.id+'"]', select);
          if(linked_tag && tag.id === linked_tag.id){
            $option.attr('selected', true);
          }else{
            $option.attr('disabled', true);
          }
        });
      });
    },

    should_append_new_select: function(){
      if(this.max_tags_limit_reached()){return;}
      this.append_select();
    },


    //TODO: implement default fetch with Edi this is example from RGM
    fetch_available_tags: function(done){
      var self = this;
      $.ez('ezjscNgRgm::fillSelect::' + this.opts.parentId, {}, function(data){
        self.available_tags = self.parse_remote_tags(data);
        done.call(self);
      });
    },


    setup_select: function($select, unlinked_tag){
      var self = this, selected;
      var dummy_tag = new this.TagKlass({id: '', name:''});
      $select.append(self.render_template('option', {tag: dummy_tag, selected: null }));

      unlinked_tag && this.link_tag_and_select(unlinked_tag, $select);

      $.each(this.available_tags.items, function(i, tag){
        selected = unlinked_tag && tag.id === unlinked_tag.id ? 'selected="selected"' : '';
        $select.append(self.render_template('option', {tag: tag, selected: selected }));
      });
    },

    append_select: function(unlinked_tag){
      var $select = $(this.render_template('select'));
      this.setup_select($select, unlinked_tag);
      this.$selects.append($select);
    }
  });



})();
