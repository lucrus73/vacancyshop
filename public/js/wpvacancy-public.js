var wpvacancy_was_here_global_flag;

if (wpvacancy_was_here_global_flag !== true)
{
  wpvacancy_was_here_global_flag = true;
  (function ($) {
    'use strict';

    var defaultDurationDays = 1;
    var currentDurationDays = defaultDurationDays;
    var calendarClickStateEnum = Object.freeze({NOTHING:1, STARTED:2, COMPLETE:3}); // enum (sort of)
    var calendarClickState = calendarClickStateEnum.NOTHING; // enum (sort of)
    var singleDayBooking = false;
    var limitedCurrentDurationDays = 0;
    var currentDurationMinutes = 0;
    var limitedCurrentDurationMinutes = 0;
    var currentStartDate = 0;
    var currentStartTime = 0;
    var currentAccommodation = 0;
    var festivities_shown = false;
    var availability_shown = false;
    var selectableDayCssClass = '';
    var selectedDayCssClass = '';
    var selectedFirstDayCssClass = '';
    var selectedLastDayCssClass = '';
    var sliderClass = '';
    var durationSlider = '';
    var loading = '';
    var accunitDetailClass = '';
    var accunitTypeClass = '';
    var startDateClass = '';
    var endDateClass = '';
    var totalPriceClass = '';
    var notesClass = '';
    var recapTarget = '';
    var restRoute;
    var restNamespace;
    var accommodationAvailableTag = '';
    var accommodationUnavailableTag = '';
    var accommodationOkTag;
    var accommodationKoTag;
    var allAccommodationsClass;
    var recapTimer;

    var wpv_wp_api;
    
    function updateBookingAvailabilityFromCalendarClick(jqThis, event, argsarray)
    {
      
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

    function chooseThisAccommodationItem(jqThis, event, argsarray)
    {
      currentAccommodation = argsarray[1];
      var selectionclass = argsarray[2];
      var selectedunitclass = argsarray[3];

      $("." + allAccommodationsClass).removeClass(selectionclass);
      $("." + selectedunitclass).addClass(selectionclass);
      
      updateRecap();
    }

    function showSingleAccommodationAvailabilityOnCalendar(dayid, accm_id)
    {
      if (availability_shown === true && accm_id > 0)
      {
        var available = $(document).find("[data-accunitid='" + accm_id + "']").data("bookable");
        if (available.includes(dayid))
        {
          $(document).find("[data-wpvdayid='" + dayid + "'] ." + accommodationAvailableTag).show();
        }
        else
        {
          $(document).find("[data-wpvdayid='" + dayid + "'] ." + accommodationUnavailableTag).show();
        }
      }
    }

    function clearSingleAccommodationAvailabilityOnCalendar()
    {
      $("." + accommodationAvailableTag).hide();
      $("." + accommodationUnavailableTag).hide();
    }
    
    function clearPeriodAvailabilityOnMap()
    {
      $("." + accommodationOkTag).hide();
      $("." + accommodationKoTag).hide();
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
      selectableDayCssClass = argsarray[0];
      selectedFirstDayCssClass = argsarray[1];
      selectedDayCssClass = argsarray[2];
      selectedLastDayCssClass = argsarray[3];
      var clickedDate = $(jqThis).data("wpvdayid");
      onCalendarClick(clickedDate);
    }

    function loadCalendar(jqThis, event, argsarray)
    {
      var wrapperclass = argsarray[1];
      var offset = argsarray[2];
      var span = argsarray[3];
      var previousMonth = argsarray[4];
      var nextMonth = argsarray[5];
      var festivitiesTarget = argsarray[6];
      var availabilityTarget = argsarray[7];
      var daySelectionParams = argsarray[8];
      /*
      wpv_wp_api.then(function (site)
      {
        site.namespace(restNamespace).getCalendarMarkup().
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
            newargs[2]--; // offset for previous month;
            loadCalendar($(this), event, newargs);
          });
          $("." + nextMonth).off("click");
          $("." + nextMonth).on("click", function (event)
          {
            var newargs = argsarray;
            newargs[2]++; // offset for next month;
            loadCalendar($(this), event, newargs);
          });
          updateDurationOnCalendar();
          $("." + loading).hide();
        });
      });
      */
      

    }

    function onCalendarClick(clickedDate)
    {
      switch(calendarClickState)
      {
        case calendarClickStateEnum.NOTHING:
          calendarClickState = setStartingDayOnCalendar(clickedDate);
          break;
        case calendarClickStateEnum.STARTED:
          calendarClickState = setEndingDayOnCalendar(clickedDate);
          break;
        case calendarClickStateEnum.COMPLETE:
          calendarClickState = editOrClearCalendarSelection(clickedDate);
          break;
      }
    }
    
    function setStartingDayOnCalendar(clickedDate)
    {
      if (clickedDate === 0)
        return calendarClickStateEnum.NOTHING; // next calendar slick state is the same as current

      currentStartDate = clickedDate;
      
      var id;
      for (id = currentStartDate + limitedCurrentDurationDays; id >= currentStartDate; id--)
        $(document).find("[data-wpvdayid='" + id + "']").removeClass(selectedLastDayCssClass);

      limitedCurrentDurationDays = currentDurationDays;

      clearCalendarSelectionUI();
      
      var requiredDays = '';
      for (id = currentStartDate; id <= (currentStartDate + currentDurationDays); id++)
      {
        if (id < (currentStartDate + currentDurationDays)) // avoid last day
        {
          showSingleAccommodationAvailabilityOnCalendar(id, currentAccommodation);
        }
        var selectionTarget = $(document).find("[data-wpvdayid='" + id + "']");
        var classToAdd = selectedDayCssClass;
        if (id === currentStartDate && currentDurationDays > 0)
          classToAdd = selectedFirstDayCssClass;
        else
          if (id === (currentStartDate + currentDurationDays) && currentDurationDays > 0)
            classToAdd = selectedLastDayCssClass;
        if (selectionTarget.hasClass(selectableDayCssClass))
        {
          selectionTarget.addClass(classToAdd);
          if (0 < requiredDays.length)
            requiredDays += ',';
          requiredDays += id;
        }
        else
        {
          selectionTarget.addClass(selectedLastDayCssClass);
          limitedCurrentDurationDays = id - currentStartDate;
          break;
        }
      }
      if (availability_shown)
      {
        $("." + allAccommodationsClass).each(function(index) 
        {
          var thisid = $(this).attr('id');
          var av = $(this).data("bookable");
          if (av.includes(requiredDays))
          {
            $("#" + thisid + " ." + accommodationOkTag).show();
          }
          else
          {
            $("#" + thisid + " ." + accommodationKoTag).show();
          }
        });
      }
      
      updateRecap();
      
      return calendarClickStateEnum.STARTED; // next calendar click state
    }

    function setEndingDayOnCalendar(clickedDate)
    {
      if (clickedDate <= currentStartDate || clickedDate === 0) // 2nd test is useless but here for readability
        return calendarClickStateEnum.STARTED; // next calendar slick state is the same as current
      
      currentDurationDays = clickedDate - currentStartDate;
      setStartingDayOnCalendar(currentStartDate);
      return calendarClickStateEnum.COMPLETE;
    }
    
    function editOrClearCalendarSelection(clickedDate)
    {
      // if the user clicked within 1 day more or 1 day less than the current start
      // and the period is 3 days or longer
      // we assume he wants to edit the current start
      if (Math.abs(clickedDate - currentStartDate) <= 1 && currentDurationDays >= 3) 
      {
        var currentEnd = currentStartDate + limitedCurrentDurationDays;
        setStartingDayOnCalendar(clickedDate);
        setEndingDayOnCalendar(currentEnd);
        return calendarClickStateEnum.COMPLETE; // we stay in the current state
      }
      // if the user clicked within 1 day more or 1 day less than the current end
      // and the period is 3 days or longer
      // we assume he wants to edit the current end
      if (Math.abs(clickedDate - (currentStartDate + limitedCurrentDurationDays)) <= 1 && currentDurationDays >= 3) 
      {
        setEndingDayOnCalendar(clickedDate);
        return calendarClickStateEnum.COMPLETE; // we stay in the current state
      }
      
      // in all other cases we assume the user is clicking far away from the
      // current start and end, so he wants to cancel the selection
      
      clearCalendarSelectionUI();
      currentDurationDays = defaultDurationDays;
      return calendarClickStateEnum.NOTHING;
    }
    
    function clearCalendarSelectionUI()
    {
      clearSingleAccommodationAvailabilityOnCalendar();
      clearPeriodAvailabilityOnMap();
      $("." + selectableDayCssClass).removeClass(selectedFirstDayCssClass);
      $("." + selectableDayCssClass).removeClass(selectedDayCssClass);
      $("." + selectableDayCssClass).removeClass(selectedLastDayCssClass);
      $(document).find("[data-wpvdayid='" + (currentStartDate + currentDurationDays + 1) + "']").removeClass(selectedLastDayCssClass);
    }
    
    function recapDate(dayIdToShow, targetClass)
    {
      if (dayIdToShow > 0)
      {
        wpv_wp_api.then(function (site)
        {
          site.namespace(restNamespace).getRecapInfo().
            param('key', 'dateFromDayId').
            param('dayid', dayIdToShow).
            then(function (results)
            {
              $("." + targetClass + " ." + recapTarget).html(results.value);
            }
          )
        });
      }
    }

    function recapNotes()
    {
      wpv_wp_api.then(function (site)
      {
        site.namespace(restNamespace).getRecapInfo().
                param('key', 'getNotesForAccommodation').
                param('accid', currentAccommodation).
                then(function (results)
                {
                  $("." + notesClass + " ." + recapTarget).html(results.value);
                }
                    )
      });
    }
    
    function recapPrice()
    {
      initWPAPI();
      wpv_wp_api.then(function (site)
      {
        site.namespace(restNamespace).getRecapInfo().
                param('key', 'getPriceForBooking').
                param('accid', currentAccommodation).
                param('startdayid', currentStartDate).
                param('enddayid', currentStartDate + limitedCurrentDurationDays).
                then(function (results)
                {
                  $("." + totalPriceClass + " ." + recapTarget).html(results.value);      
                }
                    );
      });
    }

    function updateRecap()
    {
      if (currentAccommodation > 0)
      {
        $("." + accunitDetailClass + " ." + recapTarget).html($(document).find("[data-accunitid='" + currentAccommodation + "']").data("accunitname"));
        $("." + accunitTypeClass + " ." + recapTarget).html($(document).find("[data-accunitid='" + currentAccommodation + "']").data("accunitcat"));
        recapNotes();
        if (currentStartDate > 0 && currentDurationDays > 0)
        {
          recapPrice();
        }
          
      }
      recapDate(currentStartDate, startDateClass);
      recapDate(currentStartDate + limitedCurrentDurationDays, endDateClass);
    }
    
    function addToCart(jqThis, event, argsarray)
    {
      wpv_wp_api.then(function (site)
      {
        site.namespace(restNamespace).addToCart().
                param('accid', currentAccommodation).
                param('startDate', currentStartDate).
                param('endDate', currentStartDate + limitedCurrentDurationDays).
                param('startTime', currentStartTime).
                param('endTime', currentStartTime + limitedCurrentDurationMinutes).
                then(function (results)
                {
                  //$("." + notesClass + " ." + recapTarget).html(results.value);
                  if (results.value == "booked")
                    updateCart(true);
                  else
                    message(results.message);
                }
                    );
      });
    }

    function getMapParams(mapid)
    {
      wpv_wp_api.then(function (site)
      {
        site.namespace(restNamespace).getMapParams().
                param('mapid', mapid).
                then(function (results)
                {
                  defaultDurationDays = Number(results.defaultSliderDuration);
                  currentDurationDays = defaultDurationDays;
                  singleDayBooking = Number(results.allowSingleDayBooking) > 0 ? true : false;
                }
                    );
      });
    }
    
    function updateCart(reload)
    {
      wpv_wp_api.then(function (site)
      {
        site.namespace(restNamespace).getCartMarkup().
                param('update', reload).
                then(function (results)
                {
                  if (results.status == "ok")
                  {
                    $("." + results.wrapperclass).html(results.markup);
                    showCart(null, null, [null, results.wrapperclass, null]);
                  }
                  else
                    message(results.message);
                }
                    );
      });
    }
    
    function showCart(jqThis, event, argsarray)
    {
      var cartbuttonwrapperclass = argsarray[0];
      var cartwrapperclass = argsarray[1];
      var numberofitemsclass = argsarray[2];
      var currentLeft = $("." + cartwrapperclass).css("left");
      var currentRight = $("." + cartwrapperclass).css("right");
      var offset = $(window).width();
      var newLeft = -offset * 1.2;
      var newRight = offset * 1.2;
      $("." + cartwrapperclass).css("left", newLeft + "px");
      $("." + cartwrapperclass).css("right", newRight + "px");
      $("." + cartwrapperclass).css("display", "flex");
      $("." + cartwrapperclass).animate({
                                          left: currentLeft,
                                          right: currentRight,
                                        }, 1000, function() {
      // Animation complete.
          });
    }

    function hideCart(jqThis, event, argsarray)
    {
      var cartwrapperclass = argsarray[0];
      var currentLeft = $("." + cartwrapperclass).css("left");
      var currentRight = $("." + cartwrapperclass).css("right");
      var currentwidth = $("." + cartwrapperclass).width() * 1.2;
      $("." + cartwrapperclass).animate({
                                          left: -currentwidth,
                                          right: currentwidth,
                                        }, 1000, function() {      
            $("." + cartwrapperclass).css("display", "none");
            $("." + cartwrapperclass).css("left", currentLeft);
            $("." + cartwrapperclass).css("right", currentRight);
          });
      
    }

    function setupDefaults(jqThis, event, argsarray)
    {
      loading = argsarray[0];
      accunitDetailClass = argsarray[1];
      accunitTypeClass = argsarray[2];
      startDateClass = argsarray[3];
      endDateClass = argsarray[4];
      totalPriceClass = argsarray[5];
      notesClass = argsarray[6];
      recapTarget = argsarray[7];
      restRoute = argsarray[8];
      restNamespace = argsarray[9];
      accommodationAvailableTag = argsarray[10];
      accommodationUnavailableTag = argsarray[11];
      accommodationOkTag = argsarray[12];
      accommodationKoTag = argsarray[13];
      allAccommodationsClass = argsarray[14];
      
      initWPAPI();
    }

    function loadMapParams(jqThis, event, argsarray)
    {
      var containerclass = argsarray[0];
      var dataid = argsarray[1];
      
      // KNOWN BUG - (DO NOT) FIXME - DESIGN FLAW: all the code assumes there's 
      // only one map in the page, while WP assumes you can have more than one
      // (because it's a shortcode), so we could potentially find more than one 
      // element tagged with containerclass and dataid. 
      // We should use only the first, or rewrite the whole plugin handle more 
      // than one map per page.
      // We actually do neither one and blindly call getMapParams for every
      // map in the page: that will screw up everything if the page has more
      // than one map and they use different params, because the second map
      // params will overwrite the variables of the first.
      // But, even if they used the same params, it's likely that two maps on the 
      // same page won't work anyway, because the UI wasn't designed to handle
      // more than one map per page.

      $("." + containerclass).each(function(index)
      {
        var mapid = $(this).data(dataid);
        getMapParams(mapid);
      });
      
    }
    
    function initWPAPI()
    {
      if (typeof wpv_wp_api === 'undefined' || wpv_wp_api == null)
      {
        wpv_wp_api = WPAPI.discover(restRoute);
      }
    }

  /*
   * The ready function parses the jshooks_params array received from the server
   * (see 
   */
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
}
