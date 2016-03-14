(function(){
  'use strict';

  $.EzTags.Select.prototype.fetch_available_tags = function(done){
    var self = this;
    $.get('fixtures/tags.json', function(data){
      self.available_tags = self.parse_remote_tags(data.content.tags);
      console.log(self.available_tags, data);
      done.call(self);
    });
  };

  $.EzTags.Default.prototype.fetch_suggestions = function(){
    if(!this.tags.length){return;}

    $.get('fixtures/tags.json', $.proxy(this.after_fetch_suggestions, this));
  };

  $.EzTags.Default.prototype.fetch_autocomplete = function(e) {
    if($.EzTags.is_key(e, ['UP', 'DOWN', 'LEFT', 'RIGHT', 'ESC', 'RETURN'])){return;}
    var search_string = this.get_tag_name_from_input();

    if(search_string.length < this.opts.minCharacters){return;}
    if(search_string === this.last_search_string){
      this.render_autocomplete_tags();
      this.show_autocomplete();
      return;
    }
    this.last_search_string = search_string;

    $.get('fixtures/tags.json', $.proxy(this.after_fetch_autocomplete, this));
  };

})();
