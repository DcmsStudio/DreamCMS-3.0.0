
(function ($, undefined) {
	


	
    $.fn.datetimepicker = function (options) {
        if(typeof options == typeof '') return this.datepicker.apply(this, arguments);
        options = $.extend({}, options);
        options.showTime = true;
		options.timeFormat = 'hh:mm tt';
        options.constrainInput = false;
		options.currentTime = 'Time';
        options.stepHour =  .05;
        options.stepMinute =  .05;
        options.stepSecond =  .05;
        options.ampm =  false;
        options.hour =  0;
        options.minute =  0;
        options.second =  0;
		options.timeOnly =  false;
		options.timeDefined = false;
		options.alwaysSetTime = true;
		
		
		options.showAnim = 'fadeIn';


		options.formattedDate = '';
		options.formattedTime = '';
		options.alwaysSetTime = '';
		options.formattedDateTime = '';

		
        return this.datepicker(options);

    }
	
	$.datepicker.hour =  0;
	$.datepicker.minute =  0;
	$.datepicker.second =  0;
	$.datepicker.ampm =  '';
	$.datepicker.formattedDate =  '';
	$.datepicker.formattedTime = '';
	$.datepicker.formattedDateTime = '';
	$.datepicker.divHour = null;
	$.datepicker.divMin = null;
	$.datepicker.timeDefined = false;
	$.datepicker.alwaysSetTime = true;
	$.datepicker.timeOnly =  false;
	
	
	//########################################################################
	// figure out position of time elements.. cause js cant do named captures
	//########################################################################
	$.datepicker.getFormatPositions = function(inst) {
		var finds = this._get(inst, 'timeFormat').toLowerCase().match(/(h{1,2}|m{1,2}|s{1,2}|t{1,2})/g);
		var orders = { h: -1, m: -1, s: -1, t: -1 };

		if (finds) {
			for (var i = 0; i < finds.length; i++) {
				if (orders[finds[i].toString().charAt(0)] == -1)
					orders[finds[i].toString().charAt(0)] = i + 1;
			}
		}

		return orders;
	}

    $.datepicker._getTimeText = function (inst, h, m) {
        h = h || inst.selectedHour || 0;
        m = m || inst.selectedMinute || 0;

        if (this._get(inst, 'clockType') == 12) {
            return (h == 0 || h == 12 ? '12' : h >= 12 ? h - 12 : h)
            + ':' + (m < 10 ? '0' + m : m)
            + ' ' + (h < 12 ? 'AM' : 'PM');
        } else return (h< 10 ? '0' +h :h) + ':' + (m < 10 ? '0' + m : m);
    }
	
    $.datepicker._getTimeHour = function (inst, h, m) {
        h = h || inst.selectedHour || 0;
        m = m || inst.selectedMinute || 0;

        if (this._get(inst, 'clockType') == 12) {
            return (h == 0 || h == 12 ? '12' : h >= 12 ? h - 12 : h);
        } else return (h< 10 ? '0' +h :h);
    }
	
    $.datepicker._getTimeMinute = function (inst, h, m) {
        h = h || inst.selectedHour || 0;
        m = m || inst.selectedMinute || 0;

        if (this._get(inst, 'clockType') == 12) {
            return (m < 10 ? '0' + m : m);
        } else return (m < 10 ? '0' + m : m);
    }


	
	/*
    var formatDate = $.datepicker.formatDate;
    $.datepicker.formatDate = function (format, date, settings) {
        //console.log(this);
        var inst = $.datepicker._curInst;
        var showTime = inst ? this._get(inst, 'showTime') : false;
        if (showTime) {
            date.setHours(inst.selectedHour || 0);
            date.setMinutes(inst.selectedMinute || 0);
        }

        return formatDate.apply(this, arguments) + (showTime ? ' ' + this._getTimeText(inst) : '');
    }
	
	*/
    $.datepicker._defaults.clockType = 12;

    var _hideDatepicker = $.datepicker._hideDatepicker;
    $.datepicker._hideDatepicker = /*overrides*/function () {
        var inst = $.datepicker._curInst;
        inst.selectedHour = inst.currentHour = inst.selectedMinute = inst.currentMinute = undefined;
        _hideDatepicker.apply(this, arguments);
    }

    var _getDate = $.datepicker._getDate;
	
    $.datepicker._getDate = /*overrides*/function (inst) {
        var date = _getDate.apply(this, arguments);

        date.setHours(inst.selectedHour || inst.currentHour);
        date.setMinutes(inst.selectedMinute || inst.currentMinute);
        return date;
    }

    var parseDate = $.datepicker.parseDate;
    var rxTime = /\s+([0-9]+)(\:[0-9]+){0,2}(\:[0-9]+){0,2}(\s*[apm]+){0,1}/i;
    $.datepicker.parseDate = function (format, value, settings) {
        var h = 0, m = 0;

        var time = value.match(rxTime);
        if ($.isArray(time)) {
		
            h = parseFloat(time[1]);
            m = parseFloat((time[2] || '').replace(':', ''));
            if (isNaN(h)) h = 0;
            if (isNaN(m)) m = 0;

            if ((settings.clockType || $.datepicker._defaults.clockType) == 12) {
                if (h == 12) h = 0;
                if (time[0].toLowerCase().indexOf('p') > -1) h += 12;
            }

            value = value.replace(time[0], '');
        }

        var val = parseDate.apply(this, arguments);
        val.setHours(h);
        val.setMinutes(m);

        return val;
    }
	
	/* Action for selecting a day. */
	var _selectDay = $.datepicker._selectDay;
	$.datepicker._selectDay = /*overrides*/function(id, month, year, td) {
			var target = $(id);
			if ($(td).hasClass($.datepicker._unselectableClass) || $.datepicker._isDisabledDatepicker(target[0])) {
					return;
			}
			var inst = this._getInst(target[0]);
			inst.selectedDay = inst.currentDay = $('a', td).html();
			inst.selectedMonth = inst.currentMonth = month;
			inst.selectedYear = inst.currentYear = year;
			$.datepicker._selectDate(id, $.datepicker._formatDate(inst,
					inst.currentDay, inst.currentMonth, inst.currentYear));
	}
	
	/* Update the input field with the selected date. */
	var _selectDate = $.datepicker._selectDate;
	$.datepicker._selectDate = /*overrides*/function(id, dateStr) {
			var target = $(id);
			var inst = this._getInst(target[0]);
			
			var formattedTime = $.datepicker.formatTime(inst);
			
			
			dateStr = (dateStr != null ? dateStr +''+ formattedTime : $.datepicker._formatDate(inst));
			if (inst.input)
					inst.input.val(dateStr);
			$.datepicker._updateAlternate(inst);
			
			//return;
			
			
			var onSelect = this._get(inst, 'onSelect');
			if (onSelect)
					onSelect.apply((inst.input ? inst.input[0] : null), [dateStr, inst]);  // trigger custom callback
			else if (inst.input)
					inst.input.trigger('change'); // fire the change event
			if (inst.inline)
					$.datepicker._updateDatepicker(inst);
			else {
					//$.datepicker._hideDatepicker();
					this._lastInput = inst.input[0];
					if (typeof(inst.input[0]) != 'object')
							inst.input.focus(); // restore focus
					this._lastInput = null;
			}
	},
	
    $.datepicker._setDateFromField = /*overrides*/function (inst, noDefault) {
        if (inst.input.val() == inst.lastVal) {
            return;
        }
        var dateFormat = this._get(inst, 'dateFormat');
        var dates = inst.lastVal = inst.input ? inst.input.val() : null;
        var date, defaultDate;
        date = defaultDate = this._getDefaultDate(inst);
        var settings = this._getFormatConfig(inst);
        try {
            date = $.datepicker.parseDate(dateFormat, dates, settings) || defaultDate;
        } catch (event) {
            this.log(event);
            dates = (noDefault ? '' : dates);
        }
        inst.selectedDay = date.getDate();
        inst.drawMonth = inst.selectedMonth = date.getMonth();
        inst.drawYear = inst.selectedYear = date.getFullYear();
        inst.currentDay = (dates ? date.getDate() : 0);
        inst.currentMonth = (dates ? date.getMonth() : 0);
        inst.currentYear = (dates ? date.getFullYear() : 0);
        inst.currentHour = (dates ? date.getHours() : 0);
        inst.currentMinute = (dates ? date.getMinutes() : 0);
        this._adjustInstDate(inst);
    }

    var _updateDatepicker = $.datepicker._updateDatepicker;
    $.datepicker._updateDatepicker = /*overrides*/function (inst)
	{
	
	


	
	
	
	
	
	
	
	
	
	
	
	
	
	
        var showTime = this._get(inst, 'showTime');
        var buttons = this._get(inst, 'buttons');

        var self = this;
			//$.datepicker._setDateFromField(inst, false);
			
        if (buttons) inst.settings['showButtonPanel'] = true;

        _updateDatepicker.apply(this, arguments);

		var regstr = this._get(inst, 'timeFormat').toString()
						.replace(/h{1,2}/ig, '(\\d?\\d)')
						.replace(/m{1,2}/ig, '(\\d?\\d)')
						.replace(/s{1,2}/ig, '(\\d?\\d)')
						.replace(/t{1,2}/ig, '(am|pm|a|p)?')
						.replace(/\s/g, '\\s?') + '$';

		if (!this._get(inst, 'timeOnly')) {
			//the time should come after x number of characters and a space.  x = at least the length of text specified by the date format
			regstr = '\\S{' + this._get(inst, 'timeFormat').length + ',}\\s+' + regstr;
		}

		
		var currDT = inst.input.val();
		var order = $.datepicker.getFormatPositions(inst);
		var treg = currDT.match(new RegExp(regstr, 'i'));

		if (treg) {
			if (order.t !== -1)
				$.datepicker.ampm = ((treg[order.t] == undefined || treg[order.t].length == 0) ? '' : (treg[order.t].charAt(0).toUpperCase() == 'A') ? 'AM' : 'PM').toUpperCase();

			if (order.h !== -1) {
				if ($.datepicker.ampm == 'AM' && treg[order.h] == '12')
					$.datepicker.hour = 0; // 12am = 0 hour
				else if ($.datepicker.ampm == 'PM' && treg[order.h] != '12')
					$.datepicker.hour = (parseFloat(treg[order.h]) + 12).toFixed(0); //12pm = 12 hour, any other pm = hour + 12
				else
					$.datepicker.hour = treg[order.h];
			}

			if (order.m !== -1)
				$.datepicker.minute = treg[order.m];

			if (order.s !== -1)
				$.datepicker.second = treg[order.s];
		}
		
		//alert($.datepicker.minute);
		
		
        // inst.dpDiv.css({ zIndex: 10000 });
        if (buttons) {
            var panel = inst.dpDiv.find('.ui-datepicker-buttonpane:first').html('');

            for (var i in buttons) {
                var button = $('<button class="ui-datepicker-current ui-state-default ui-priority-secondary ui-corner-all"/>');

                (function (func) {
                    if (typeof func == 'object') {
                        if (func.attr)
                            button.attr(func.attr);
                        if (func.css)
                            button.css(func.css);
                        func = func.click || function () { };
                    }
                    button.text(i).click(function () { func.apply(self, ['#' + inst.id, inst]); }).appendTo(panel);
                })(buttons[i]);
            }
            
            $('.ui-datepicker-close').removeAttr('onclick').unbind('click').click(function(){ _hideDatepicker(); });
        }
       
        if (showTime) {
		
			// Cleanup Patch
			inst.dpDiv.find('th.lblTime').remove();
			inst.dpDiv.find('td.tdHour').remove();
			inst.dpDiv.find('td.tdMin').remove();
			
			
			
            var table = inst.dpDiv.find('table:last');		
            var thead = table.find('thead>tr:first');
            var tbody = table.find('tbody>tr:first');
            var numRows = tbody.parent().children('tr').length;			
			var labelDiv = $('<div class="ui-time-current-container"></div>');
			
			var label = $('<div class="ui-time-current"></div>');
			var labelTitle = $('<span class="ui-time-current-title">'+this._get(inst,"currentTime")+':</span>');
			
				//labelDiv.append(labelTitle);
				labelDiv.append(label);
			
			
            var lblTime = $('<th colspan="2" style="white-space:nowrap;text-align:center"></th>').appendTo(thead);
				lblTime.append(labelDiv);

			
            lblTime.width(lblTime.width());
            var dpwidth = inst.dpDiv.width();
            inst.dpDiv.width(dpwidth + lblTime.width());
            var groups = inst.dpDiv.find('.ui-datepicker-group');
            groups.width(dpwidth/groups.length);
            groups.eq(groups.length-1).width(dpwidth/groups.length+lblTime.width());
            
            var height = table.height() - table.find('td:first').height() * 2;
            var tdHour = $('<td/>').css({ height: height, marginTop: 10, paddingLeft:20, width: 30 +'px' }).attr('rowspan', numRows).appendTo(tbody);
            var tdMin = $('<td/>').css({ height: height, marginTop: 10,  paddingLeft:5, paddingRight:10}).attr('rowspan', numRows).appendTo(tbody);

            $.datepicker.divHour = $('<div/>').appendTo(tdHour).css({marginLeft:3});
            $.datepicker.divMin = $('<div/>').appendTo(tdMin).css({marginLeft: 5, marginRight:10});
			
			
			// Add class fpr cleanup Patch
			lblTime.addClass('lblTime');
			tdHour.addClass('tdHour');
			tdMin.addClass('tdMin');
			
			
			
			var txtHour = $('<input id="txtHour" value="12" size="2"/>').css({marginLeft: -5, marginTop: 10, width:20}).appendTo(tdHour);			
				tdHour.append('<span style="float:right;position:relative;right:0;width:3px;bottom:-10px;font-weight : bold;"> : </span>');
				
			var txtMin = $('<input id="txtMin" value="00" size="2"/>').css({marginLeft: -5, marginRight: 10, marginTop: 10, width:20}).appendTo(tdMin);
			txtHour.unbind('change');
			txtMin.unbind('change');
			
			var thisInst = inst;
			
			txtHour.change( function(){ 
				$.datepicker.hour = $(this).val(); 
				$.datepicker.divHour.slider("value", $(this).val() ); 
				$.datepicker.onTimeChange(thisInst); 
			});
			
			txtMin.change( function(){ 
				$.datepicker.minute = $(this).val(); 
				$.datepicker.divMin.slider("value", $(this).val() ); 
				$.datepicker.onTimeChange(thisInst); 
			});
			
			
            inst.selectedHour = $.datepicker.hour || 0; // || inst.selectedHour || inst.currentHour;
            inst.selectedMinute = $.datepicker.minute || 0; // || inst.selectedMinute || inst.currentMinute;	
			
			
			txtHour.val( $.datepicker.hour );
			txtMin.val( $.datepicker.minute );			
           // label.text( $.datepicker._getTimeText(inst) );
			
			
			$.datepicker.divHour.slider("value", $.datepicker.hour );
			$.datepicker.divMin.slider("value", $.datepicker.minute );

            $.datepicker.divHour.slider({ min: 0, max: 23, value: $.datepicker.hour, orientation: 'vertical', slide: function (e, ui) {
                $.datepicker.divHour.slider( "option", "value", ui.value );
				//$.datepicker.hour = ui.value;
				/*
                if (inst.input)
                    inst.input.val($.datepicker._formatDate(inst));
				*/
               // label.text($.datepicker._getTimeText(inst));				

				$.datepicker.onTimeChange(inst); 				
				
				txtHour.val($.datepicker.hour);
				txtMin.val($.datepicker.minute);
            }
            });
			
            $.datepicker.divMin.slider({ min: 0, max: 59, value: $.datepicker.minute, orientation: 'vertical', slide: function (e, ui) {
                $.datepicker.divMin.slider( "option", "value", ui.value );
				//$.datepicker.minute = ui.value;
				/*
                if (inst.input)
                    inst.input.val($.datepicker._formatDate(inst));
				*/
                //label.text( $.datepicker._getTimeText(inst));

				
				$.datepicker.onTimeChange(inst); 
				
				txtHour.val($.datepicker.hour);
				txtMin.val($.datepicker.minute);				

            }
            });
        }
    }
	
	
	
	
	
	
	
	

	//########################################################################
	// when a slider moves..
	// on time change is also called when the time is updated in the text field
	//########################################################################
	$.datepicker.onTimeChange = function(dp_inst) {
		var hour = $.datepicker.divHour.slider('value');
		var minute = $.datepicker.divMin.slider('value');
		// var second = $.datepicker.second_slider.slider('value');
		var ampm = ($.datepicker.hour < 12) ? 'AM' : 'PM';

		var hasChanged = false;

		// if the update was done in the input field, this field should not be updated
		// if the update was done using the sliders, update the input field
		if ($.datepicker.hour != hour || $.datepicker.minute != minute || /*$.datepicker.second != second ||*/ ($.datepicker.ampm.length > 0 && $.datepicker.ampm != ampm))
			hasChanged = true;

		$.datepicker.hour = parseFloat(hour).toFixed(0);
		$.datepicker.minute = parseFloat(minute).toFixed(0);
		// $.datepicker.second = parseFloat(second).toFixed(0);
		$.datepicker.ampm = ampm;
		$.datepicker.formatTime(dp_inst);

		//$.datepicker.$timeObj.text(this.formattedTime);

		if (hasChanged) {
			$.datepicker.updateDateTime(dp_inst);
			$.datepicker.timeDefined = true;
		}
	}

	//########################################################################
	// format the time all pretty...
	//########################################################################
	$.datepicker.formatTime = function(inst) {
		var tmptime = this._get(inst, "timeFormat").toString();
		var hour12 = (($.datepicker.ampm == 'AM') ? ($.datepicker.hour) : ($.datepicker.hour % 12));
		hour12 = (hour12 == 0) ? 12 : hour12;

		if (this._get(inst, "ampm") == true) {
			tmptime = tmptime.toString()
				.replace(/hh/g, ((hour12 < 10) ? '0' : '') + hour12)
				.replace(/h/g, hour12)
				.replace(/mm/g, (($.datepicker.minute < 10) ? '0' : '') + $.datepicker.minute)
				.replace(/m/g, $.datepicker.minute)
				.replace(/ss/g, (($.datepicker.second < 10) ? '0' : '') + $.datepicker.second)
				.replace(/s/g, $.datepicker.second)
				.replace(/TT/g, $.datepicker.ampm.toUpperCase())
				.replace(/tt/g, $.datepicker.ampm.toLowerCase())
				.replace(/T/g, $.datepicker.ampm.charAt(0).toUpperCase())
				.replace(/t/g, $.datepicker.ampm.charAt(0).toLowerCase());
		}
		else {
			tmptime = tmptime.toString()
				.replace(/hh/g, (($.datepicker.hour < 10) ? '0' : '') + $.datepicker.hour)
				.replace(/h/g, $.datepicker.hour)
				.replace(/mm/g, (($.datepicker.minute < 10) ? '0' : '') + $.datepicker.minute)
				.replace(/m/g, $.datepicker.minute)
				.replace(/ss/g, (($.datepicker.second < 10) ? '0' : '') + $.datepicker.second)
				.replace(/s/g, $.datepicker.second);
			tmptime = $.trim(tmptime.replace(/t/gi, ''));
		}

		$.datepicker.formattedTime = tmptime;
		return $.datepicker.formattedTime;
	}

	//########################################################################
	// update our input with the new date time..
	//########################################################################
	$.datepicker.updateDateTime = function(inst) {
		var dt = this._getDefaultDate(inst);

		if (dt == null)
			$.datepicker.formattedDate = this.formatDate(this._get(inst, 'dateFormat'), new Date(), this._getFormatConfig(inst));
			
		else $.datepicker.formattedDate = this.formatDate(this._get(inst, 'dateFormat'), dt, this._getFormatConfig(inst));

		if (this._get(inst, "alwaysSetTime")) {
			$.datepicker.formattedDateTime = $.datepicker.formattedDate + ' ' + $.datepicker.formattedTime;
		}
		else {
			if (dt == null || !$.datepicker.timeDefined || $.datepicker.timeDefined == false) {
				$.datepicker.formattedDateTime = $.datepicker.formattedDate;
			}
			else {
				$.datepicker.formattedDateTime = $.datepicker.formattedDate + ' ' + $.datepicker.formattedTime;
			}
		}
		//-----------------------------

		if (this._get(inst, "timeOnly") == true)
			inst.input.val($.datepicker.formattedTime);
		else inst.input.val($.datepicker.formattedDateTime);
	}

	
	
	
	
	
	
	
	
	
	
})(jQuery);