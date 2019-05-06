function immstart(imm,act){
      (function ($) {
            'use strict';
              $(function () {
             if (act == imm.id){
               var url = imm.url;
               var sel = 'url("'+url+'")';
               $('#vb_wpvac_cf_hotel').css({
                  'background-image':sel 
               });
             }
});
})(jQuery);
}

function immagine(imm){
      (function ($) {
            'use strict';
              $(function () {      
           $('.dashicons-plus').click(function(){
             setTimeout(function(){
               console.log($('.odd.ui-sortable-handle'));
                if ($('.odd.ui-sortable-handle').attr('data-id')==imm.id){
               var url = imm.url;
               var sel = 'url("'+url+'")';
               $('#vb_wpvac_cf_hotel').css({
                 'background-image':sel 
               });
             }
             },1);
   });
});
})(jQuery);
}

(function ($) {
  'use strict';
  /* 
   * To change this license header, choose License Headers in Project Properties.
   * To change this template file, choose Tools | Templates
   * and open the template in the editor.
   */
  $(function () {
    
    $('.dashicons-plus').click(function(){
      if($('.dashicons').hasClass('dashicons-minus')){
        $('.ui-sortable-handle').remove();
        $('.added').removeClass('added');
      }
    });
    
    var offset = 0;
    var $last_selection = null;
    var $accm_counter = 1;
    var $container = $('#vb_wpvac_cf_hotel');

    $("#vb_wpvac_cf_save").click(function (event)
    {
      if ($last_selection != null)
      {
        var percx = Math.round($last_selection.position().left / $container.width() * 1000.0) / 10;
        var percy = Math.round($last_selection.position().top / $container.height() * 1000.0) / 10;
        var percw = Math.round($last_selection.width() / $container.width() * 1000.0) / 10.0;
        var perch = Math.round($last_selection.height() / $container.height() * 1000.0) / 10.0;
        var limits = "X=" + percx + "%, Y=" + percy +
                "%, W=" + percw + "%, H=" + perch + "%";
        $('<li>' + limits + '</li>').appendTo($("#vb_wpvac_cf_accmlist"));
        $accm_counter++;
        $('#vb_wpvac_cf_acc_unit_box_x').val(percx);
        $('#vb_wpvac_cf_acc_unit_box_y').val(percy);
        $('#vb_wpvac_cf_acc_unit_box_w').val(percw);
        $('#vb_wpvac_cf_acc_unit_box_h').val(perch);
        $("#vb_wpvac_cf_save").attr("disabled","disabled");
      }
    });

    $("#vb_wpvac_cf_clear").click(function (event)
    {
      if ($last_selection != null){
        $last_selection.remove();
        $(".selection-box").remove();
        $("#vb_wpvac_cf_accmlist").children().remove();
        $('#vb_wpvac_cf_acc_unit_box_x').val("");
        $('#vb_wpvac_cf_acc_unit_box_y').val("");
        $('#vb_wpvac_cf_acc_unit_box_w').val("");
        $('#vb_wpvac_cf_acc_unit_box_h').val("");
        $("#vb_wpvac_cf_save").removeAttr("disabled");
      }else{
        $last_selection = null;
      
      }
    });


    $container.on('mousedown', function (e) {
      if(!$("#vb_wpvac_cf_save").attr("disabled")){
      if ($last_selection != null)
      $last_selection.remove();
      $last_selection = null;
      var $selection = $('<div>').addClass('vb_wpvac_cf_selection-box');
      offset = $(this).offset();
      var click_y = e.pageY - offset.top;
      var click_x = e.pageX - offset.left;
      $selection.css(
              {
                'top': click_y + "px",
                'left': click_x + "px",
                'width': 0,
                'height': 0
              });
      $selection.appendTo($container);

      $container.on('mousemove', function (e)
      {
        var move_x = e.pageX - offset.left,
                move_y = e.pageY - offset.top,
                width = Math.abs(move_x - click_x),
                height = Math.abs(move_y - click_y),
                new_x, new_y;


        new_x = (move_x < click_x) ? (click_x - width) : click_x;
        new_y = (move_y < click_y) ? (click_y - height) : click_y;

        $selection.css({
          'width': width + "px",
          'height': height + "px",
          'top': new_y + "px",
          'left': new_x + "px"
        });
      }).on('mouseup', function (e)
      {
        $container.off('mousemove');
        $last_selection = $selection;
      });
    }
    }); 
    
  });
  /**
   * All of the code for your admin-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

})(jQuery);
