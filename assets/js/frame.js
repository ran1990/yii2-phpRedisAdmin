$(function() {
  if (history.replaceState) {
    //window.parent.history.replaceState({}, '', document.location.href.replace('?', '&').replace(/\/([a-z]*)\.php/, '/?$1'));
    window.parent.history.replaceState({}, '', document.location.href.replace('?', '&').replace('default/', '?'));
  }
  var phpRedisAdmin_csrfToken = $('meta[name="csrf-token"]').attr('content');


  $('#type').change(function(e) {
    $('#hkeyp' ).css('display', e.target.value == 'hash' ? 'block' : 'none');
    $('#indexp').css('display', e.target.value == 'list' ? 'block' : 'none');
    $('#scorep').css('display', e.target.value == 'zset' ? 'block' : 'none');
  }).change();

  $('.canceled').click(function(e) {
    top.location.href = top.location.pathname+$(this).attr('data-url');
  });
  $('.delkey, .delval').click(function(e) {
    e.preventDefault();

    if (confirm($(this).hasClass('delkey') ? 'Are you sure you want to delete this key and all it\'s values?' : 'Are you sure you want to delete this value?')) {
      $.ajax({
        type: "POST",
        url: this.href,
        data: 'post=1&'+csrfName+'=' + phpRedisAdmin_csrfToken,
        success: function(data) {
         if (data.code == 0) {
           alert(data.msg);
           return false;
         }
          top.location.href = top.location.pathname+data.url;
        }
      });
    }
  });
});



