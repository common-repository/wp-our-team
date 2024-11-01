(function() {
   tinymce.create('tinymce.plugins.ourteam', {
      init : function(ed, url) {
         ed.addButton('ourteam', {
            title : 'WP Our Team',
            image : url+'/icon-team.png',
            onclick : function() {
               var posts = prompt("Number of Team", "");

              
                  if (posts != null && posts != '')
                     ed.execCommand('mceInsertContent', false, '[WPour_team limit="'+posts+'"]');
                  else
                     ed.execCommand('mceInsertContent', false, '[WPour_team]');
               
            }
         });
      },
      createControl : function(n, cm) {
         return null;
      },
      getInfo : function() {
         return {
            longname : "Our Team",
            author : 'ThemeIdol',
            authorurl : 'http://www.themeidol.com',
            infourl : '',
            version : "1.0"
         };
      }
   });
   tinymce.PluginManager.add('ourteam', tinymce.plugins.ourteam);
})();