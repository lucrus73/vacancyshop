var it_virtualbit_vacancyshop_was_here_global_flag;

if (it_virtualbit_vacancyshop_was_here_global_flag !== true)
{
  it_virtualbit_vacancyshop_was_here_global_flag = true;
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
    var selectableDayCssClass = '';
    var selectedDayCssClass = '';
    var selectedFirstDayCssClass = '';
    var selectedLastDayCssClass = '';
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
    var originalCarouselImgH = 0;
    var originalCarouselImgW = 0;

    var wpv_wp_api;
    
    function updateBookingAvailabilityFromCalendarClick(jqThis, event, argsarray)
    {
      
    }
    
    function chooseAccommodation(jqThis, event, argsarray)
    {
      var accommodationBoxClass = argsarray[0];
      var selectedClass = argsarray[1];
      $("." + accommodationBoxClass).removeClass(selectedClass);
      $(jqThis).addClass(selectedClass);
      currentAccommodation = $(jqThis).data("accunitid");
      updateRecap();
    }

    function clearAccommodation(jqThis, event, argsarray)
    {
      var accommodationMapImageClass = argsarray[0];
      var accommodationBoxClass = argsarray[1];
      var selectedClass = argsarray[2];
      var mapclass = argsarray[3];
      var datamapid = argsarray[4];
      var carouselclass = argsarray[5];

      $("." + accommodationBoxClass).removeClass(selectedClass);
      currentAccommodation = 0;
      updateRecap();
      var mapid = $("." + mapclass).data(datamapid); 
      reloadCarouselImages(mapid, carouselclass);
    }

    function reloadCarouselImages(postid, carouselclass)
    {
      wpv_wp_api.then(function (site)
      {
        site.namespace(restNamespace).getAccommodationImages().
        param('postid', postid).
        then(function (results)
        {
          $("." + carouselclass).html(results.markup);
          parseEventsMappings(results.events);
        })
      });
    }
    
    function updateCarousel(jqThis, event, argsarray)
    {
      var carouselclass = argsarray[1];
      var accid = $(jqThis).data("accunitid"); 
      reloadCarouselImages(accid, carouselclass);
    }
    
    function loadCarousel(jqThis, event, argsarray)
    {
      var mapclass = argsarray[0];
      var datamapid = argsarray[1];
      var carouselclass = argsarray[2];
      var mapid = $("." + mapclass).data(datamapid); 
      reloadCarouselImages(mapid, carouselclass);
    }
    
    function moveCarousel(elem, clicklistener, adderfn)
    {
      if (originalCarouselImgH === 0)
        originalCarouselImgH = elem.height();
      if (originalCarouselImgW === 0)
        originalCarouselImgW = elem.width();
      elem.animate(
              {
                height: 0,
                width: 0
              }, 200, function()
              {
                elem.remove();

                // 2: add child at the end
                adderfn(elem);
                parseEventsMappings(clicklistener);
                elem.animate(
                        {
                          height: originalCarouselImgH,
                          width: originalCarouselImgW,
                        }, 200);
              }
                      );
    }

    function previousImageInCarousel(jqThis, event, argsarray)
    {
      var carouselclass = argsarray[1];
      var carouselimgclass = argsarray[2];
      var clickEventHandler = argsarray[3];
      
      // 1: remove last child
      var last = $('.' + carouselclass).find('.' + carouselimgclass).last();
      moveCarousel(last, clickEventHandler, function(elem)
      {
        $('.' + carouselclass).prepend(elem);
      });
    }
    
    function nextImageInCarousel(jqThis, event, argsarray)
    {
      var carouselclass = argsarray[1];
      var carouselimgclass = argsarray[2];
      var clickEventHandler = argsarray[3];
      
      // 1: remove 1st child
      var first = $('.' + carouselclass).find('.' + carouselimgclass).first();
      moveCarousel(first, clickEventHandler, function(elem)
      {
        $('.' + carouselclass).append(elem);
      });      
    }

    function showCarouselPictureInLightbox(jqThis, event, argsarray)
    {
      var lightboxclass = argsarray[1];
      var lightboximgcontainerclass = argsarray[2];
      var dataid = argsarray[3];
      var closebuttonclass = argsarray[4];
      
      var fullimgsrc = $(jqThis).data(dataid);
      $("." + lightboximgcontainerclass).css("background-image", "url('" + fullimgsrc + "')");
      $("." + lightboxclass).fadeIn();
      
      $("." + closebuttonclass).on('click', function(event){
        $("." + lightboxclass).hide();
      });
      
      
    }
    
    function showSingleAccommodationAvailabilityOnCalendar(dayid, accm_id)
    {
      if (accm_id > 0)
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
    
    function replaceCalendarMarkup(markup, cfg, offset)
    {
      $("." + cfg.wrapperclass).html(markup);

      update_calendar_ui(cfg.festivitiesTarget, true);
      update_calendar_ui(cfg.availabilityTarget, true);
      
      var dayclass = cfg.daySelectionParams[0];
      $("." + dayclass).off("click");
      $("." + dayclass).on("click", function (ev) 
      {
        daySelection($(this), ev, cfg.daySelectionParams);
      });
      registerCalendarClicks(cfg, offset);
      showCurrentSelectionOnCalendar();
    }
    
    function requestCalendarMarkup(cfg, offset)
    {
      wpv_wp_api.then(function (site)
      {
        site.namespace(restNamespace).getCalendarMarkup().
        param('offset', offset).
        param('span', cfg.span).
        param('includeavailabilitytags', false).
        then(function (results)
        {
          replaceCalendarMarkup(results.markup, cfg, offset);
        })
      });
    }
    
    function toggleCalendarMonthsButton(cfg)
    {
      $("." + cfg.selectMonthButton).hide();
      $("." + cfg.selectMonth).show();
      $("." + cfg.selectMonth + " select").selectmenu();
      $("." + cfg.selectMonth + " select").selectmenu("open").on("selectmenuchange", function (ev)
      {
        $("." + cfg.selectMonth).hide();
        $("." + cfg.selectMonthButton).show();
        var selectoffset = this.value;
        requestCalendarMarkup(cfg, selectoffset);
      });
    }
    
    function disableCalendarClicks(cfg)
    {
      $("." + cfg.previousMonth).off("click");
      $("." + cfg.nextMonth).off("click");
      $("." + cfg.selectMonthButton).off("click");      
    }
    
    function enableCalendarClicks(cfg, currentoffset)
    {
      $("." + cfg.previousMonth).on("click", function (event)
      {
        disableCalendarClicks(cfg);
        requestCalendarMarkup(cfg, currentoffset - 1);
      });

      $("." + cfg.nextMonth).on("click", function (event)
      {
        disableCalendarClicks(cfg);
        requestCalendarMarkup(cfg, currentoffset + 1);
      });

      $("." + cfg.selectMonthButton).on("click", function (event)
      {
        disableCalendarClicks(cfg);
        toggleCalendarMonthsButton(cfg);
      });      
    }
    
    function registerCalendarClicks(cfg, currentoffset)
    {
      disableCalendarClicks(cfg);
      enableCalendarClicks(cfg, currentoffset);
    }

    function getLoadCalendarConfig(argsarray)
    {
      var config = 
      {
        wrapperclass: argsarray[1],
        offset: argsarray[2],
        span: argsarray[3],
        previousMonth: argsarray[4],
        nextMonth: argsarray[5],
        selectMonthButton: argsarray[6],
        selectMonth: argsarray[7],
        festivitiesTarget: argsarray[8],
        availabilityTarget: argsarray[9],
        daySelectionParams: argsarray[10]
      };
      return config;
    }
    
    function loadCalendar(jqThis, event, argsarray)
    {
      var cfg = getLoadCalendarConfig(argsarray);
      registerCalendarClicks(cfg, cfg.offset);
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
      
      showCurrentSelectionOnCalendar();

      return calendarClickStateEnum.STARTED; // next calendar click state
    }
    
    function showCurrentSelectionOnCalendar()
    {
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
      
      updateRecap();
      
    }

    function setEndingDayOnCalendar(clickedDate)
    {
      if (clickedDate < (currentStartDate - 2)) // if the user clicked far away before the current start
      {
        clearCalendarSelectionUI();
        return calendarClickStateEnum.NOTHING; // he probably wants to cancel the selected start
      }
      
      if (clickedDate < currentStartDate) // if the user clicked before, but near, the current start
      {
        return setStartingDayOnCalendar(clickedDate); // he likely wants to edit the start date
      }
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
      currentStartDate = 0;
      updateRecap();
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
      var novalue = "---";
      $("." + accunitDetailClass + " ." + recapTarget).html(novalue);
      $("." + accunitTypeClass + " ." + recapTarget).html(novalue);
      $("." + notesClass + " ." + recapTarget).html(novalue);
      $("." + totalPriceClass + " ." + recapTarget).html(novalue);      
      $("." + startDateClass + " ." + recapTarget).html(novalue);
      $("." + endDateClass + " ." + recapTarget).html(novalue);
      if (currentAccommodation > 0)
      {
        var auname = $(document).find("[data-accunitid='" + currentAccommodation + "']").data("accunitname");
        var aucat = $(document).find("[data-accunitid='" + currentAccommodation + "']").data("accunitcat");
        $("." + accunitDetailClass + " ." + recapTarget).html(auname);
        $("." + accunitTypeClass + " ." + recapTarget).html(aucat);
        recapNotes();
        if (currentStartDate > 0 && currentDurationDays > 0)
        {
          recapPrice();
        }
      }
      if (currentStartDate > 0)
      {
        recapDate(currentStartDate, startDateClass);
        recapDate(currentStartDate + limitedCurrentDurationDays, endDateClass);
      }
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
      accunitDetailClass = argsarray[0];
      accunitTypeClass = argsarray[1];
      startDateClass = argsarray[2];
      endDateClass = argsarray[3];
      totalPriceClass = argsarray[4];
      notesClass = argsarray[5];
      recapTarget = argsarray[6];
      restRoute = argsarray[7];
      restNamespace = argsarray[8];
      accommodationAvailableTag = argsarray[9];
      accommodationUnavailableTag = argsarray[10];
      accommodationOkTag = argsarray[11];
      accommodationKoTag = argsarray[12];
      allAccommodationsClass = argsarray[13];
      
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
    
    function parseEventsMappings(events)
    {
      $.each(events, function (index, value)
      {
        var target = value[0];
        if (target === 'public')
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
    }

  /*
   * The ready function parses the jshooks_params array received from the server
   */
    $(document).ready(function ()
    {
      parseEventsMappings(jshooks_params.events);
    });

    $(document).ajaxStart(function () {
      $('body').css({'cursor': 'wait'});
    }).ajaxStop(function () {
      $('body').css({'cursor': 'default'});
    });

  })(jQuery);
}
