(function( $ ) {
	'use strict';

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

function updateBookingAvailabilityFromDurationInput(jqThis, event, argsarray)
{
  var inputclass = argsarray[0];
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
