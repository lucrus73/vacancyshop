(function ($) {
  'use strict';

  var rangeStart = -1;
  var currentDuration = 1;
  var currentStartDate = 0;
  var currentStartTime = 0;
  var currentAccommodation = 0;
  var festivities_shown = false;
  var availability_shown = false;
  var genericDayCssClass = '';
  var selectedDayCssClass = '';
  var wpv_wp_api;

  function getAccomodationsAvailability(startdate, enddate)
  {

  }

  function getDatesAvailability(accomodations, duration)
  {

  }

  function getDurationAvailability(startdate, accomodations)
  {

  }

  function updateAvailability(jqThis, event, argsarray)
  {
    var inputclass = argsarray[0];
  }

  function showDurationSlider(jqThis, event, argsarray)
  {
    var sliderclass = argsarray[0];
    var handleclass = argsarray[1];
    var baloonclass = argsarray[2];
    var singularlabel = argsarray[3];
    var plurallabel = argsarray[4];
    var init = argsarray[5];
    var min = argsarray[6];
    var max = argsarray[7];
    var step = argsarray[8];
    $("." + sliderclass).slider({
      value: init,
      min: min,
      max: max,
      step: step,
      slide: function (s_event, ui) {
        sliderBaloon(ui.value, handleclass, baloonclass, singularlabel, plurallabel);
        currentDuration = ui.value;
        updateDurationOnCalendar();    
      }
    });
    currentDuration = init;
    sliderBaloon(init, handleclass, baloonclass, singularlabel, plurallabel);
    updateDurationOnCalendar();
  }

  function sliderBaloon(value, handleclass, baloonclass, singularlabel, plurallabel)
  {
    var label = plurallabel;
    if (value == 1)
      label = singularlabel;
    var str = value + " " + label;
    $("." + handleclass).html(
            '<div class="' + baloonclass + '">' + str + '</div>');
    $("." + baloonclass).css("width", str.length + "rem");
  }

  function updateBookingAvailabilityFromDurationDrop(jqThis, event, argsarray)
  {
    event.preventDefault();
    event.stopPropagation();
    var inputclass = argsarray[0];
    var moveclass = argsarray[1];
    var pos = jqThis.position();
  }

  function updateBookingAvailabilityFromAccomodationClick(jqThis, event, argsarray)
  {
    var inputclass = argsarray[0];
  }

  function showSelectionDialog(jqThis, event, argsarray)
  {
    var buttonclass = argsarray[0];
    var dialogclass = argsarray[1];
    $("." + dialogclass).fadeIn("fast", "linear", function (e) {
      $("." + buttonclass).off("click");
      $("." + buttonclass).on("click", function (e) {
        $("." + buttonclass).off("click");
        $("." + dialogclass).fadeOut("fast", "linear", function (e) {
          $("." + buttonclass).off("click");
          $("." + buttonclass).on("click", function (e) {
            showSelectionDialog(jqThis, e, argsarray);
          });
        });
      });
    });
  }

  function initWPAPI(restRoute)
  {
    if (typeof wpv_wp_api === 'undefined' || wpv_wp_api == null)
    {
      wpv_wp_api = WPAPI.discover(restRoute);
    }
  }

  function viewMoreOfAccommodationItem(jqThis, event, argsarray)
  {
    var lightboxclass = argsarray[1];
    var lightboxspeed = argsarray[2];
    var iframeid = argsarray[3];
    var sourcedocument = argsarray[4];
    var closebuttonclass = argsarray[5];

    event.stopImmediatePropagation();
    event.preventDefault();

    $("#" + iframeid).attr("src", sourcedocument);
    $("." + lightboxclass).css("width", $(window).width() + "px");
    $("." + lightboxclass).css("height", $(window).height() + "px");
    $("." + lightboxclass).fadeIn(lightboxspeed);
    $("." + closebuttonclass).on("click", function (e) {
      $("." + lightboxclass).fadeOut(lightboxspeed);
    });
  }

  function update_calendar_ui(target, value)
  {
    if (value === true)
    {
      $("." + target).css("display", "block");
    } else
    {
      $("." + target).css("display", "none");
    }
  }

  function toggleFestivities(jqThis, event, argsarray)
  {
    var target = argsarray[1];
    festivities_shown = !festivities_shown;
    update_calendar_ui(target, festivities_shown);
  }

  function toggleAvailability(jqThis, event, argsarray)
  {
    var target = argsarray[1];
    availability_shown = !availability_shown;
    update_calendar_ui(target, availability_shown);
  }

  function toggleOptions(jqThis, event, argsarray)
  {
    event.stopImmediatePropagation();
    event.preventDefault();
    var panel = argsarray[1];
    $("." + panel).slideToggle(500);
  }
  
  function daySelection(jqThis, event, argsarray)
  {    
    event.stopImmediatePropagation();
    event.preventDefault();
    genericDayCssClass = argsarray[0];
    selectedDayCssClass = argsarray[1];
    $("." + genericDayCssClass).removeClass(selectedDayCssClass);
    $(jqThis).addClass(selectedDayCssClass);
    currentStartDate = $(jqThis).data("wpvdayid");
    updateDurationOnCalendar();
  }

  function loadCalendar(jqThis, event, argsarray)
  {
    var restRoute = argsarray[1];
    var namespace = argsarray[2];
    var wrapperclass = argsarray[3];
    var offset = argsarray[4];
    var span = argsarray[5];
    var previousMonth = argsarray[6];
    var nextMonth = argsarray[7];
    var festivitiesTarget = argsarray[8];
    var availabilityTarget = argsarray[9];
    var optionsPanelTarget = argsarray[10];
    var daySelectionParams = argsarray[11];
    $("." + optionsPanelTarget).slideUp(1);
    initWPAPI(restRoute);
    wpv_wp_api.then(function (site)
    {
      site.namespace(namespace).getCalendarMarkup().
              param('offset', offset).
              param('span', span).
              then(function (results)
              {
                var markup = results.markup;
                $("." + wrapperclass).html(markup);
                $("." + previousMonth).off("click");
                $("." + nextMonth).off("click");
                update_calendar_ui(festivitiesTarget, festivities_shown);
                update_calendar_ui(availabilityTarget, availability_shown);
                var dayclass = daySelectionParams[0];
                $("." + dayclass).off("click");
                $("." + dayclass).on("click", function (ev) 
                {
                  daySelection($(this), ev, daySelectionParams);
                });
                
                $("." + previousMonth).on("click", function (event)
                {
                  var newargs = argsarray;
                  newargs[4]--; // previous month;
                  loadCalendar($(this), event, newargs);
                });
                $("." + nextMonth).off("click");
                $("." + nextMonth).on("click", function (event)
                {
                  var newargs = argsarray;
                  newargs[4]++; // next month;
                  loadCalendar($(this), event, newargs);
                });
                updateDurationOnCalendar();
              });
    });

  }
  
  function updateDurationOnCalendar()
  {
    if (currentStartDate === 0)
      return;
    
    $("." + genericDayCssClass).removeClass(selectedDayCssClass);
    var id;
    for (id = currentStartDate; id < (currentStartDate + currentDuration); id++)
    {
      $(document).find("[data-wpvdayid='" + id + "']").addClass(selectedDayCssClass);
    }
  }

  $(document).ready(function ()
  {
    $.each(jshooks_params.events, function (index, value)
    {
      var event = value[0];
      var fname = value[1];
      var fargs = value[2];
      if (event === 'load')
        eval(fname + '($(this), null, fargs);');
      else
      {
        var cssclasstoselect = fargs[0];
        $('.' + cssclasstoselect).on(event, function (e) {
          return eval(fname + '($(this), e, fargs);');
        });
      }
    });
  });

  $(document).ajaxStart(function () {
    $('body').css({'cursor': 'wait'});
  }).ajaxStop(function () {
    $('body').css({'cursor': 'default'});
  });

})(jQuery);
