(function ($) {
  'use strict';



  // we just need an onclick handler for choosing the days that are going to be highlighted.
  // then when he is finished choosing we assemble a list of people that are going to be free within that time.
  var DayScheduleSelector = function (el, options) {
      
     //console.log(el);
    // console.log(options); 

    this.$el = $(el);
    this.options = $.extend({}, DayScheduleSelector.DEFAULTS, options);
    this.render();
    this.attachEvents();
    this.$selectingStart = null;
  }

  DayScheduleSelector.DEFAULTS = {
    days        : [0, 1, 2, 3, 4, 5, 6],  // Sun - Sat
    startTime   : '08:00',                // HH:mm format
    endTime     : '20:00',                // HH:mm format
    interval    : 30,                     // minutes
    template    : '<div class="day-schedule-selector">'         +
                    '<table class="schedule-table">'            +
                      '<thead class="schedule-header"></thead>' +
                      '<tbody class="schedule-rows"></tbody>'   +
                    '</table>'                                  +
                  '<div>'
  };

  /**
   * Render the calendar UI
   * @public
   */
  DayScheduleSelector.prototype.render = function () {
    this.$el.html(this.options.template);
    this.renderHeader();
    this.renderRows();
  };

  /**
   * Render the calendar header
   * @public
   */
  DayScheduleSelector.prototype.renderHeader = function () {
    var stringDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
      , days = this.options.days
      , html = '';

    $.each(days, function (_, v) { html += '<th>' + stringDays[v] + '</th>'; });
    this.$el.find('.schedule-header').html('<tr><th></th>' + html + '</tr>');
  };

  /**
   * Render the calendar rows, including the time slots and labels
   * @public
   */
  DayScheduleSelector.prototype.renderRows = function () {
    var start = this.options.startTime
      , end = this.options.endTime
      , interval = this.options.interval
      , days = this.options.days
      , $el = this.$el.find('.schedule-rows');

    $.each(generateDates(start, end, interval), function (i, d) {
      var daysInARow = $.map(new Array(days.length), function (_, i) {
        return '<td class="time-slot" data-time="' + hhmm(d) + '" data-day="' + days[i] + '"></td>'
      }).join();

      $el.append('<tr><td class="time-label">' + hmmAmPm(d) + '</td>' + daysInARow + '</tr>');
    });
  };

  /**
   * Is the day schedule selector in selecting mode?
   * @public
   */
  DayScheduleSelector.prototype.isSelecting = function () {
    return !!this.$selectingStart;
  }

  DayScheduleSelector.prototype.select = function ($slot) { $slot.attr('data-selected', 'selected'); }
  DayScheduleSelector.prototype.deselect = function ($slot) { $slot.removeAttr('data-selected'); }

  function isSlotSelected($slot) { return $slot.is('[data-selected]'); }
  function isSlotSelecting($slot) { return $slot.is('[data-selecting]'); }

  /**
   * Get the selected time slots given a starting and a ending slot
   * @private
   * @returns {Array} An array of selected time slots
   */
  function getSelection(plugin, $a, $b) {
    var $slots, small, large, temp;
    if (!$a.hasClass('time-slot') || !$b.hasClass('time-slot') ||
        ($a.data('day') != $b.data('day'))) { return []; }
    $slots = plugin.$el.find('.time-slot[data-day="' + $a.data('day') + '"]');
    small = $slots.index($a); large = $slots.index($b);
    if (small > large) { temp = small; small = large; large = temp; }
    return $slots.slice(small, large + 1);
  }

  DayScheduleSelector.prototype.attachEvents = function () {
    var plugin = this
      , options = this.options
      , $slots;

    this.$el.on('click', '.time-slot', function () {

      // this should be triggered when the slots are clicked... 
    //  console.log("the square just got clicked");
      var day = $(this).data('day');
      if (!plugin.isSelecting()) {  // if we are not in selecting mode

        //console.log("the intial selection");

        if (isSlotSelected($(this))) { 
          console.log("deleting the redness from the circle");
          console.log($(this)['context']);
          // this is going to delete a square... so we need to check this square/
          console.log($(this)['context'].getAttribute('data-time'));
          console.log($(this)['context'].getAttribute('data-day'));
          var day = $(this)['context'].getAttribute('data-day');
          var time = $(this)['context'].getAttribute('data-time');

          delete_time_slot(day, time);
          plugin.deselect($(this)); 
        
        }


        else {  // then start selecting

          plugin.$selectingStart = $(this);
          $(this).attr('data-selecting', 'selecting');
          plugin.$el.find('.time-slot').attr('data-disabled', 'disabled');
          plugin.$el.find('.time-slot[data-day="' + day + '"]').removeAttr('data-disabled');
        }
      } else {  // if we are in selecting mode
        if (day == plugin.$selectingStart.data('day')) {  // if clicking on the same day column
          // then end of selection

          //console.log(plugin);
         // console.log(plugin.$el);
         // console.log(plugin.$el.find('.time-slot[data-day="' + day + '"]'));
          // lets try to get the outer html from here...
          // console.log(plugin.$el.find('.time-slot[data-day="' + day + '"]').filter('[data-selecting]'));
           var list = plugin.$el.find('.time-slot[data-day="' + day + '"]').filter('[data-selecting]');
           console.log(list);
          getting_selected_list(list);


         // console.log("end the selection");
          plugin.$el.find('.time-slot[data-day="' + day + '"]').filter('[data-selecting]')
          .attr('data-selected', 'selected').removeAttr('data-selecting');

          // we need to grab the outer html and then from there we know which times are being selected atm
          plugin.$el.find('.time-slot').removeAttr('data-disabled');
          plugin.$el.trigger('selected.artsy.dayScheduleSelector', [getSelection(plugin, plugin.$selectingStart, $(this))]);
          plugin.$selectingStart = null;
        }
      }
    });
  

  // this here is getting called everytime you place the mouse over the squares here..
    this.$el.on('mouseover', '.time-slot', function () {
      //console.log("the mouse over");
      var $slots, day, start, end, temp;


      if (plugin.isSelecting()) {  // if we are in selecting mode

       // console.log("we are in selecting mode");
        day = plugin.$selectingStart.data('day');
        $slots = plugin.$el.find('.time-slot[data-day="' + day + '"]');
        $slots.filter('[data-selecting]').removeAttr('data-selecting');
        start = $slots.index(plugin.$selectingStart);
        end = $slots.index(this);
        if (end < 0) return;  // not hovering on the same column
        if (start > end) { temp = start; start = end; end = temp; }
        $slots.slice(start, end + 1).attr('data-selecting', 'selecting');
      }
    });
  };

function delete_time_slot(day, time){

  // we would need to go through that entire day again and see who is available from that. 

  



  console.log("inside the delete timeslot function right now");
}



// lets send the function to a php function.
function getting_selected_list(list){
  
  var day_array = [];

  for(var x = 0; x < list.length; x++){
    console.log(list[x]);
    day_array.push(list[x].getAttribute('data-time'));
    day_array.push(list[x].getAttribute('data-day'));
  }

  //console.log("making the ajax call and awaiting the return!!...");
var object =  $.ajax({
                type: "POST",
                url: "WhosFree.php",   // maybe have to make another url here...
                
                data: {functionname: 'whosfree', arguments: day_array},  // try to pass the array in...

                success: function (obj, textstatus) {
                
                  var listData = html_to_list(obj);

                 // console.log(document.getElementById('freepeople'));

                  // this checks if we have already created a table or not.
                  if(document.getElementById('freepeople') == null) {
                  var listContainer = document.createElement("div");
                  listContainer.id = "freepeople";
                  }else{
                    var listContainer = document.getElementById('freepeople');
                  }
                  document.getElementsByTagName("body")[0].appendChild(listContainer); 
                   
                   // ---> this is the unordered list tag
                      
                  var listElement = document.createElement("ul"); 

                  listContainer.appendChild(listElement);
                    var numberOfListItems = listData.length;
                  // now we set up the loop that goes through every single item here... 

                     for( var i =  0 ; i < numberOfListItems ; ++i){
                
                                        // create a <li> for each one.
                                        var listItem = document.createElement("li");

                                        // add the item text
                                        listItem.innerHTML = listData[i];
                                        listItem.id = listData[i];
                                        // add listItem to the listElement
                                        listElement.appendChild(listItem);
                                }
                  }
            
            });

}


// this functino is going to extradct the lsit from the html... 
function html_to_list(html){

  var string = html.split('{"result":'); // this needs to be split further
     string = string[1];
     string = string.split('}\n</div>');
     string = string[0] 
     //console.log(string);

  var json_obj = jQuery.parseJSON(string);

  var return_array = [];

 if(document.getElementById('freepeople') == null) {

  return json_obj;
                }else{
                  for (var i = 0; i < json_obj.length; i++){

                    if(document.getElementById(json_obj[i]) == null){
                      // then we return this element because it is not already in the list.
                      return_array.push(json_obj[i]);
                    }
                  }
                  return return_array;
          }
}





  /**
   * Serialize the selections
   * @public
   * @returns {Object} An object containing the selections of each day, e.g.
   *    {
   *      0: [],
   *      1: [["15:00", "16:30"]],
   *      2: [],
   *      3: [],
   *      5: [["09:00", "12:30"], ["15:00", "16:30"]],
   *      6: []
   *    }
   */
  DayScheduleSelector.prototype.serialize = function () {
    var plugin = this
      , selections = {};

    $.each(this.options.days, function (_, v) {
      var start, end;
      start = end = false; selections[v] = [];
      plugin.$el.find(".time-slot[data-day='" + v + "']").each(function () {
        if (isSlotSelected($(this)) && !start) { start = $(this).data('time'); }
        else if (!isSlotSelected($(this)) && !!start) {
          end = $(this).data('time');
          selections[v].push([start, end]);
          start = end = false;
        }
      });
    })
    return selections;
  };

  /**
   * Deserialize the schedule and render on the UI
   * @public
   * @param {Object} schedule An object containing the schedule of each day, e.g.
   *    {
   *      0: [],
   *      1: [["15:00", "16:30"]],
   *      2: [],
   *      3: [],
   *      5: [["09:00", "12:30"], ["15:00", "16:30"]],
   *      6: []
   *    }
   */
  DayScheduleSelector.prototype.deserialize = function (schedule) {
    var plugin = this, i;
    $.each(schedule, function(d, ds) {
      var $slots = plugin.$el.find('.time-slot[data-day="' + d + '"]');
      $.each(ds, function(_, s) {
        for (i = 0; i < $slots.length; i++) {
          if ($slots.eq(i).data('time') >= s[1]) { break; }
          if ($slots.eq(i).data('time') >= s[0]) { plugin.select($slots.eq(i)); }
        }
      })
    });
  };

  // DayScheduleSelector Plugin Definition
  // =====================================

  function Plugin(option) {
    return this.each(function (){
      var $this   = $(this)
        , data    = $this.data('artsy.dayScheduleSelector')
        , options = typeof option == 'object' && option;

      if (!data) {
        $this.data('artsy.dayScheduleSelector', (data = new DayScheduleSelector(this, options)));
      }
    })
  }

  $.fn.dayScheduleSelector = Plugin;

  /**
   * Generate Date objects for each time slot in a day
   * @private
   * @param {String} start Start time in HH:mm format, e.g. "08:00"
   * @param {String} end End time in HH:mm format, e.g. "21:00"
   * @param {Number} interval Interval of each time slot in minutes, e.g. 30 (minutes)
   * @returns {Array} An array of Date objects representing the start time of the time slots
   */
  function generateDates(start, end, interval) {
    var numOfRows = Math.ceil(timeDiff(start, end) / interval);
    return $.map(new Array(numOfRows), function (_, i) {
      // need a dummy date to utilize the Date object
      return new Date(new Date(2000, 0, 1, start.split(':')[0], start.split(':')[1]).getTime() + i * interval * 60000);
    });
  }

  /**
   * Return time difference in minutes
   * @private
   */
  function timeDiff(start, end) {   // time in HH:mm format
    // need a dummy date to utilize the Date object
    return (new Date(2000, 0, 1, end.split(':')[0], end.split(':')[1]).getTime() -
            new Date(2000, 0, 1, start.split(':')[0], start.split(':')[1]).getTime()) / 60000;
  }

  /**
   * Convert a Date object to time in H:mm format with am/pm
   * @private
   * @returns {String} Time in H:mm format with am/pm, e.g. '9:30am'
   */
  function hmmAmPm(date) {
    var hours = date.getHours()
      , minutes = date.getMinutes()
      , ampm = hours >= 12 ? 'pm' : 'am';
    return hours + ':' + ('0' + minutes).slice(-2) + ampm;
  }

  /**
   * Convert a Date object to time in HH:mm format
   * @private
   * @returns {String} Time in HH:mm format, e.g. '09:30'
   */
  function hhmm(date) {
    var hours = date.getHours()
      , minutes = date.getMinutes();
    return ('0' + hours).slice(-2) + ':' + ('0' + minutes).slice(-2);
  }

  function hhmmToSecondsSinceMidnight(hhmm) {
    var h = hhmm.split(':')[0]
      , m = hhmm.split(':')[1];
    return parseInt(h, 10) * 60 * 60 + parseInt(m, 10) * 60;
  }

  /**
   * Convert seconds since midnight to HH:mm string, and simply
   * ignore the seconds.
   */
  function secondsSinceMidnightToHhmm(seconds) {
    var minutes = Math.floor(seconds / 60);
    return ('0' + Math.floor(minutes / 60)).slice(-2) + ':' +
           ('0' + (minutes % 60)).slice(-2);
  }

  // Expose some utility functions
  window.DayScheduleSelector = {
    ssmToHhmm: secondsSinceMidnightToHhmm,
    hhmmToSsm: hhmmToSecondsSinceMidnight
  };

})(jQuery);
