var it_virtualbit_wpvacancy_admin_was_here_global_flag;

if (it_virtualbit_wpvacancy_admin_was_here_global_flag !== true)
{
  it_virtualbit_wpvacancy_admin_was_here_global_flag = true;

  (function( $ ) 
  {
    'use strict';

    var offset = 0;
    var $last_selection = null;
    var $accm_counter = 1;
    var $container = $('.vb_wpvac_cf_accommodation_map_image');

    function registerAccommodationMapClick(jqThis, event, argsarray)
    {
      var id = argsarray[0];
      var url = argsarray[1];
      $('.dashicons-plus').click(function ()
      {
        setTimeout(function ()
        {
          console.log($('.odd.ui-sortable-handle'));
          if ($('.odd.ui-sortable-handle').attr('data-id') == id)
          {
            showAccommodationMapImage(id, url, id);
          }
        }, 1);
      });
    }

    function showAccommodationMapImage(jqThis, event, argsarray)
    {
      var id = argsarray[0];
      var url = argsarray[1];
      var act = argsarray[2];
      if (act == id)
      {
        var sel = 'url("' + url + '")';
        $('.vb_wpvac_cf_accommodation_map_image').css({
          'background-image': sel
        });
      }
    }

    $('.dashicons-plus').click(function ()
    {
      if ($('.dashicons').hasClass('dashicons-minus'))
      {
        $('.ui-sortable-handle').remove();
        $('.added').removeClass('added');
      }
    });

    $(".vb_wpvac_cf_save").click(function (event)
    {
      if ($last_selection != null)
      {
        var percx = Math.round($last_selection.position().left / $container.width() * 1000.0) / 10;
        var percy = Math.round($last_selection.position().top / $container.height() * 1000.0) / 10;
        var percw = Math.round($last_selection.width() / $container.width() * 1000.0) / 10.0;
        var perch = Math.round($last_selection.height() / $container.height() * 1000.0) / 10.0;
        var limits = "X=" + percx + "%, Y=" + percy +
                "%, W=" + percw + "%, H=" + perch + "%";
        $('<li>' + limits + '</li>').appendTo($(".vb_wpvac_cf_accmlist"));
        $accm_counter++;
        $('.vb_wpvac_cf_acc_unit_box_x').val(percx);
        $('.vb_wpvac_cf_acc_unit_box_y').val(percy);
        $('.vb_wpvac_cf_acc_unit_box_w').val(percw);
        $('.vb_wpvac_cf_acc_unit_box_h').val(perch);
        $(".vb_wpvac_cf_save").attr("disabled", "disabled");
      }
    });

    $(".vb_wpvac_cf_clear").click(function (event)
    {
      if ($last_selection != null)
      {
        $last_selection.remove();
        $(".selection-box").remove();
        $(".vb_wpvac_cf_accmlist").children().remove();
        $('.vb_wpvac_cf_acc_unit_box_x').val("");
        $('.vb_wpvac_cf_acc_unit_box_y').val("");
        $('.vb_wpvac_cf_acc_unit_box_w').val("");
        $('.vb_wpvac_cf_acc_unit_box_h').val("");
        $(".vb_wpvac_cf_save").removeAttr("disabled");
      } 
      else
      {
        $last_selection = null;
      }
    });

    $container.on('mousedown', function (e)
    {
      if (!$(".vb_wpvac_cf_save").attr("disabled"))
      {
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
  
    /*
     * The ready function parses the jshooks_params array received from the server
     */
    $(document).ready(function ()
    {
      $.each(jshooks_params.events, function (index, value)
      {
        var target = value[0];
        if (target === 'admin')
        {
          var event = value[1];
          var fname = value[2];
          var fargs = value[3];
          if (event === 'load')
            eval(fname + '($(this), null, fargs);');
          else
          {
            var cssclasstoselect = fargs[0];
            $('.' + cssclasstoselect).on(event, function (e) {
              return eval(fname + '($(this), e, fargs);');
            });
          }
        }
      });
    });

    $(document).ajaxStart(function () {
      $('body').css({'cursor': 'wait'});
    }).ajaxStop(function () {
      $('body').css({'cursor': 'default'});
    });

  })( jQuery );
}
