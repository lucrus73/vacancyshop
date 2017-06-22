(function( $ ) {
	'use strict';

var rangeStart = -1;

function getAccomodationsAvailability(startdate, enddate)
{
  
}

function getDatesAvailability(accomodations, duration)
{
  
}

function getDurationAvailability(startdate, accomodations)
{
  
}

function updateBookingAvailabilityFromCalendarClick(jqThis, event, argsarray)
{
  var inputclass = argsarray[0];
}

function showDurationSlider(jqThis, event, argsarray)
{
  var sliderclass = argsarray[0];
  var handleclass = argsarray[1];
  $("." + sliderclass).slider({
            value: 1,
            min: 1,
            max: 60,
            step: 1,
            slide: function( s_event, ui ) {
              $("." + handleclass).html(ui.value + " giorni");
            }
          });
 }

function updateBookingAvailabilityFromDurationDrag(jqThis, event, argsarray)
{
  event.preventDefault();  
  event.stopPropagation();
  var inputclass = argsarray[0];
  var moveclass = argsarray[1];
  var pos = jqThis.position();  
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
 
$(document).ready(function()
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
      $('.' + cssclasstoselect).on(event, function(e) {
        return eval(fname + '($(this), e, fargs);');
      });
    }
  });
});

$(document).ajaxStart(function() {
    $('body').css({'cursor' : 'wait'});
}).ajaxStop(function() {
    $('body').css({'cursor' : 'default'});
});

})( jQuery );
