(function( $ ) {
	'use strict';

var rangeStart = -1;
var currentDuration = 1;
var currentStartDateTime = 0;
var currentAccommodation = 0;
var postids_to_urls_map = [];
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
            slide: function( s_event, ui ) {
              sliderBaloon(ui.value, handleclass, baloonclass, singularlabel, plurallabel);
            }
          });
  sliderBaloon(init, handleclass, baloonclass, singularlabel, plurallabel);
 }
 
function sliderBaloon(value, handleclass, baloonclass, singularlabel, plurallabel)
{
  var label = plurallabel;
  if (value == 1)
   label = singularlabel;
  var str = value + " " + label;
  $("." + handleclass).html(
         '<div class="' + baloonclass +'">' + str + '</div>');
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
  $("." + dialogclass).fadeIn("fast", "linear", function(e) {
    $("." + buttonclass).off("click");
    $("." + buttonclass).on("click", function(e) {
      $("." + buttonclass).off("click");
      $("." + dialogclass).fadeOut("fast", "linear", function(e) {
        $("." + buttonclass).off("click");
        $("." + buttonclass).on("click", function (e) {
            showSelectionDialog(jqThis, e, argsarray);
        });    
      });
    });
  });
}

function loadPostUrlForPostId(jqThis, event, argsarray)
{
  var restRoute = argsarray[0];
  var namespace = argsarray[1];
  var postid = argsarray[2];
  if (typeof wpv_wp_api === 'undefined' || ies_wp_api == null) 
  {
    wpv_wp_api = WPAPI.discover(restRoute);
  }
  wpv_wp_api.then(function (site) 
  {
    site.namespace(namespace).getLightboxPermalink().
                                    param('postid', postid).
                                              then(function (results)
    {
      var url = results.url;
      if (url != "404")
      {
        postids_to_urls_map[postid] = url;
      }
    });
  });  
}

function viewMoreOfAccommodationItem(jqThis, event, argsarray)
{
  var lightboxclass = argsarray[1];
  var lightboxspeed = argsarray[2];
  var iframeid = argsarray[3];
  var sourcedocument = postids_to_urls_map[argsarray[4]];
  var closebuttonclass = argsarray[5];
  
  event.stopImmediatePropagation();
  event.preventDefault();
  
  $("#" + iframeid).attr("src", sourcedocument);
  $("." + lightboxclass).css("width", $(window).width() + "px");
  $("." + lightboxclass).css("height", $(window).height() + "px");
  $("." + lightboxclass).fadeIn(lightboxspeed);
  $("." + closebuttonclass).on("click", function(e) {
    $("." + lightboxclass).fadeOut(lightboxspeed);
  });
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
