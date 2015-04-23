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

})();
