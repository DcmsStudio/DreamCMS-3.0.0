var seconds = 0;
var _seconds = 0;
var _minutes = 0;
var _hours = 0;
var _progress_id = 0;
var _ajax_div 	= $('#ajax_div');
var _progress 	= 0;
var _heute 		= 0; 	// Datumsobjekt erstellen
var _timestamp 	= 0; 	// Zeitstempel in Millisekunden(!) berechnen
var _startTime 	= 0; 	// unix_timestamp
var UP_timer 	= 0; 	//


var bRunning = false;       // merken, dass Stoppuhr laeuft
var ElapsedTimeInterval;
var barwidth;
var progress_status;
var progress_bar;
var progress_message;
var est_time_left;
var elapsed_time;
var path_to_set_progress_script;
var t;
var RunNow = false;
var FirstRunNow = false;
var uploadProgressSettings = new Array();
var uploadProgressTimer = new Array();
var uploadProgressNotFound = new Array();
var uploadProgressActive = new Array();
var uploadProgressData = new Array();
var progressKey = '';
var prconfig;
var netspeed = {};
var fileSpeedStats = [];
var tracking;
function formatUnits(baseNumber, unitDivisors, unitLabels, singleFractional) {
	var i, unit, unitDivisor, unitLabel;

	if (baseNumber === 0) {
		return "0 " + unitLabels[unitLabels.length - 1];
	}
	
	if (singleFractional) {
		unit = baseNumber;
		unitLabel = unitLabels.length >= unitDivisors.length ? unitLabels[unitDivisors.length - 1] : "";
		for (i = 0; i < unitDivisors.length; i++) {
			if (baseNumber >= unitDivisors[i]) {
				unit = (baseNumber / unitDivisors[i]).toFixed(2);
				unitLabel = unitLabels.length >= i ? " " + unitLabels[i] : "";
				break;
			}
		}
		
		return unit + unitLabel;
	} else {
		var formattedStrings = [];
		var remainder = baseNumber;
		
		for (i = 0; i < unitDivisors.length; i++) {
			unitDivisor = unitDivisors[i];
			unitLabel = unitLabels.length > i ? " " + unitLabels[i] : "";
			
			unit = remainder / unitDivisor;
			if (i < unitDivisors.length -1) {
				unit = Math.floor(unit);
			} else {
				unit = unit.toFixed(2);
			}
			if (unit > 0) {
				remainder = remainder % unitDivisor;
				
				formattedStrings.push(unit + unitLabel);
			}
		}
		
		return formattedStrings.join(" ");
	}
}

function formatBPS(baseNumber) {
	var bpsUnits = [1073741824, 1048576, 1024, 1], bpsUnitLabels = ["Gbps", "Mbps", "Kbps", "bps"];
	return formatUnits(baseNumber, bpsUnits, bpsUnitLabels, true);

}

function formatTime(baseNumber) {
	var timeUnits = [86400, 3600, 60, 1], timeUnitLabels = ["d", "h", "m", "s"];
	return formatUnits(baseNumber, timeUnits, timeUnitLabels, false);
}

function formatBytes(baseNumber) {
	var sizeUnits = [1073741824, 1048576, 1024, 1], sizeUnitLabels = ["GB", "MB", "KB", "bytes"];
	return formatUnits(baseNumber, sizeUnits, sizeUnitLabels, true);
}

function formatPercent(baseNumber) {
	return baseNumber.toFixed(2) + " %";
}
function removeTracking(file, trackingList) {
	try {
		trackingList[file.id] = null;
		delete trackingList[file.id];
	} catch (ex) {
	}
};
function updateTracking(file, bytesUploaded) {
	
	tracking = fileSpeedStats[file.id];
	if (!tracking) {
		fileSpeedStats[file.id] = tracking = {};
	}
	
	// Sanity check inputs
	bytesUploaded = bytesUploaded || tracking.bytesUploaded || 0;
	if (bytesUploaded < 0) {
		bytesUploaded = 0;
	}
	if (bytesUploaded > file.size) {
		bytesUploaded = file.size;
	}
	
	var tickTime = (new Date()).getTime();
	if (!tracking.startTime) {
		tracking.startTime = (new Date()).getTime();
		tracking.lastTime = tracking.startTime;
		tracking.currentSpeed = 0;
		tracking.averageSpeed = 0;
		tracking.movingAverageSpeed = 0;
		tracking.movingAverageHistory = [];
		tracking.timeRemaining = 0;
		tracking.timeElapsed = 0;
		tracking.percentUploaded = bytesUploaded / file.size;
		tracking.bytesUploaded = bytesUploaded;
	} else if (tracking.startTime > tickTime) {
		alert("When backwards in time");
	} else {
		// Get time and deltas
		var now = (new Date()).getTime();
		var lastTime = tracking.lastTime;
		var deltaTime = now - lastTime;
		var deltaBytes = bytesUploaded - tracking.bytesUploaded;
		
		if (deltaBytes === 0 || deltaTime === 0) {
			return tracking;
		}
		
		// Update tracking object
		tracking.lastTime = now;
		tracking.bytesUploaded = bytesUploaded;
		
		// Calculate speeds
		tracking.currentSpeed = (deltaBytes * 8 ) / (deltaTime / 1000);
		tracking.averageSpeed = (tracking.bytesUploaded * 8) / ((now - tracking.startTime) / 1000);

		// Calculate moving average
		tracking.movingAverageHistory.push(tracking.currentSpeed);
		if (tracking.movingAverageHistory.length > 30) {
			tracking.movingAverageHistory.shift();
		}
		
		tracking.movingAverageSpeed = calculateMovingAverage(tracking.movingAverageHistory);
		
		// Update times
		tracking.timeRemaining = (file.size - tracking.bytesUploaded) * 8 / tracking.movingAverageSpeed;
		tracking.timeElapsed = (now - tracking.startTime) / 1000;
		
		// Update percent
		tracking.percentUploaded = (tracking.bytesUploaded / file.size * 100);
	}
	
	return tracking;
};




function calculateMovingAverage(history) {
	var vals = [], size, sum = 0.0, mean = 0.0, varianceTemp = 0.0, variance = 0.0, standardDev = 0.0;
	var i;
	var mSum = 0, mCount = 0;
	
	size = history.length;
	
	// Check for sufficient data
	if (size >= 8) {
		// Clone the array and Calculate sum of the values 
		for (i = 0; i < size; i++) {
			vals[i] = history[i];
			sum += vals[i];
		}

		mean = sum / size;

		// Calculate variance for the set
		for (i = 0; i < size; i++) {
			varianceTemp += Math.pow((vals[i] - mean), 2);
		}

		variance = varianceTemp / size;
		standardDev = Math.sqrt(variance);
		
		//Standardize the Data
		for (i = 0; i < size; i++) {
			vals[i] = (vals[i] - mean) / standardDev;
		}

		// Calculate the average excluding outliers
		var deviationRange = 2.0;
		for (i = 0; i < size; i++) {
			
			if (vals[i] <= deviationRange && vals[i] >= -deviationRange) {
				mCount++;
				mSum += history[i];
			}
		}
		
	} else {
		// Calculate the average (not enough data points to remove outliers)
		mCount = size;
		for (i = 0; i < size; i++) {
			mSum += history[i];
		}
	}

	return mSum / mCount;
};



function getElapsedTime()
{
	if (!bRunning)
	{
		_seconds = 0;
		_minutes = 0;
		clearTimeout(ElapsedTimeInterval);
		return;
	}
	
	if ( typeof _seconds == 'undefined' )
	{
		_seconds = 0;
	}
	
	
	if ( typeof _minutes == 'undefined' )
	{
		_minutes = 0;
	}
	
	if ( typeof _hours == 'undefined' )
	{
		_hours = 0;
	}
	
	_seconds++;
	seconds++;
	
	if(_seconds == 60){
		_seconds = 0;
		_minutes++;
	}
	
	if(_minutes == 60){
		_minutes = 0;
		_hours++;
	}
	var hr = "" + ((_hours < 10) ? "0" : "") + _hours;
	var min = "" + ((_minutes < 10) ? "0" : "") + _minutes;
	var sec = "" + ((_seconds < 10) ? "0" : "") + _seconds;

	prconfig.est_time_left.html( (hr != '00' ?  hr + ' hours : ' : '') + (min != '00' ? min + ' min : ' : '') + sec+' sec' );

	hr = null;
	min = null;
	sec = null;
	
	ElapsedTimeInterval = setTimeout("getElapsedTime()", 1000); 
}

function _runProgressTimer(track, total, current)
{
	if ( !bRunning ) return false;
	var byte_speed = 0;
	var time_remaining = 0;

	if ( isNaN(total) || isNaN(current) ) return;
	
	var tracker = updateTracking(track, prconfig.current);
	prconfig.elapsed_time.html( formatTime( tracker.timeRemaining ) );
	//prconfig.elapsed_time.html( (remaining_hours != '00' ?  remaining_hours + ': hours ' : '') + (remaining_min != '00' ? remaining_min + ' min : ' : '') + remaining_sec+' sec' );
	
	
	
	
	
	return;
	
	
	

	if( seconds > 0 ){ byte_speed = total / _seconds; }
	else
	{
		return;
	}
	if(byte_speed > 0){ time_remaining = Math.round((total - current) / byte_speed); }
	
	/*
	timeDiff = new Date() - prconfig.starttimer,
	time_remaining =  Math.round((total_readed - current) / Math.round(timeDiff / _percent_float / 1000 * (_percent_float)) );
	*/
	
	// Calculate time remaining
	var remaining_sec = (time_remaining % 60);
	var remaining_min = (((time_remaining - remaining_sec) % 3600) / 60);
	var remaining_hours = ((((time_remaining - remaining_sec) - (remaining_min * 60)) % 86400) / 3600);
	
	if(remaining_sec < 10){ remaining_sec = '0'+ remaining_sec; }
	if(remaining_min < 10){ remaining_min = '0'+  remaining_min; }
	if(remaining_hours < 10){ remaining_hours = '0'+  remaining_hours; }

	prconfig.elapsed_time.html( (remaining_hours != '00' ?  remaining_hours + ': hours ' : '') + (remaining_min != '00' ? remaining_min + ' min : ' : '') + remaining_sec+' sec' );

}


function setComplMsg( msg, iserror )
{
	var str = '<table cellpadding="4" cellspacing="1" border="0" class="tblborder" width="99%" align="center" style="margin-top:10px;"><tr class="pagenavbg"><td align="'+ (iserror ? 'center' : 'right') +'"'+ (iserror ? ' style="color:red"' : '') +'>'+ (iserror ? 'ERROR: ' : '') + msg +'</td></tr></table>';
	$('#ajax_div').append(str);
	$('#ajax_div').css('display', 'none');
	$('#ajax_div').css('width', '100%');	
	$('#ajax_div').slideToggle();
}


var _data;
var total;
var current;
var notFoundLimit = 10;
var _url = '';


(function($){

	$.fn.doRunProgress = function(options)
	{	
		var defaults = {
			barwidth: 500,
			total: 0,
			current: 0,
			starttimer: 0,
			reset: true,
			data: null,
			progressKey : ''
		};
		
		prconfig = $.extend({}, defaults, options);
		
		if (!prconfig.progressKey )
		{
			alert('Progressbar can not run!');
			return;
		}
		
		if (prconfig.reset)
		{
			fileSpeedStats = [];
			tracking = {};
			
			_seconds = 0;
			_minutes = 0;
			_hours = 0;
			_progress = 0;
			seconds = 0;
			
			prconfig.total = 0;
			prconfig.current = 0;
			prconfig.starttimer = 0;
			prconfig.data = null;
			prconfig.stop = false;
			
			bRunning = false;
			RunNow = false;
			FirstRunNow = false;
			
			progressKey = '';			

			uploadProgressSettings = new Array();
			uploadProgressTimer = new Array();
			uploadProgressNotFound = new Array();
			uploadProgressActive = new Array();
			uploadProgressData = new Array();
		
			$('#ajax_div').hide(); 
			$('#ajax_div').empty();
			
			

			prconfig.progress_bar.css({width: 0 +'px'});
			prconfig.progress_status.html('0 %');
			prconfig.progress_message.html('Bearbeite Ihre Anfrage ...');		
			
			uploadProgressActive[prconfig.progressKey] = true;
			uploadProgressTimer[prconfig.progressKey] = 1;

			
			prconfig.est_time_left.empty();
			
		}
		clearTimeout(t);
		clearTimeout(ElapsedTimeInterval);
		
		bRunning = true; 
		getElapsedTime(); 
		$.fn.progressUpdate();
	},
	
	$.fn.progressUpdate = function()
	{
		if (prconfig.stop)
		{
			bRunning = false;
			clearTimeout(t);
			return false;
		}
	
		if ( !RunNow )
		{
			RunNow = true;
			FirstRunNow = true;
			if ( $('#progress-frame').length == 0)
			{
				$('body').append($('<iframe>').attr({'id': 'progress-frame','height': '0', 'width':'0'}).css({'position': 'absolute', 'top': '-1000px', 'left': '-1000px'}) );
			}
			
			$('#progress-frame').attr('src', prconfig.progress_script + '&RUNID=' + prconfig.progressKey);
			
			prconfig.starttimer = new Date();
		}
		
		
		if (FirstRunNow)
		{
			setTimeout(function(){
				_url = prconfig.progress_script + '&GETDATA='+ prconfig.progressKey;
				
				$.get(_url +'&ajax=1', {}, function(data) {
					if ( responseIsOk(data) )
					{
						FirstRunNow = false;
						$.fn.formatIndexingProgress(data);
					}
					else
					{
						bRunning = false;
						prconfig.stop = true;
						clearTimeout(t);
						clearTimeout(ElapsedTimeInterval);
						alert('Error: '+data.msg);
					}
				}, 'json');
			}, 350);
		}
		else
		{
			_url = prconfig.progress_script + '&GETDATA='+ prconfig.progressKey;
			
			$.get(_url +'&ajax=1', {}, function(data) {
				if ( responseIsOk(data) )
				{
					if ( !prconfig.stop ) { 
						$.fn.formatIndexingProgress(data); 
					}
				}
				else
				{
					uploadProgressNotFound[prconfig.progressKey]++;
				}
			}, 'json');
		}
		
	}
	
	$.fn.formatIndexingProgress = function(data)
	{

		if ( data.output == '' )
		{
			uploadProgressNotFound[prconfig.progressKey]++;
			
			if (uploadProgressNotFound[prconfig.progressKey] >= notFoundLimit) {
				bRunning = false;
				prconfig.stop = true;
				
				uploadProgressActive[prconfig.progressKey] = false;
				uploadProgressData[prconfig.progressKey] = '';
				uploadProgressTimer[prconfig.progressKey] = 0;
				clearTimeout(t);
				clearTimeout(ElapsedTimeInterval);
				
				alert("Error");
				return false; // cancel timer renewal
			}
			
			setTimeout(function(){ $.fn.progressUpdate()}, 500);
		}

		prconfig.data = data.output.split('|');
		
		if ( prconfig.data[0] == 'e' )
		{
			prconfig.stop = true;
			bRunning = false;
			clearTimeout(t);
			clearTimeout(ElapsedTimeInterval);
			
			return false; // cancel timer renewal
		}
		
		prconfig.current = parseInt(prconfig.data[1]);
		prconfig.total = parseInt(prconfig.data[0]);

		
		uploadProgressData[prconfig.progressKey] = prconfig.data;

		if ( isNaN(prconfig.total) == 'NaN' || isNaN(prconfig.current)  )
		{
			uploadProgressNotFound[prconfig.progressKey]++;
			
			if (uploadProgressNotFound[prconfig.progressKey] >= notFoundLimit) {
				bRunning = false;
				prconfig.stop = true;
				
				uploadProgressActive[prconfig.progressKey] = false;
				uploadProgressData[prconfig.progressKey] = '';
				uploadProgressTimer[prconfig.progressKey] = 0;
				clearTimeout(t);
				clearTimeout(ElapsedTimeInterval);
				
				alert("Error");
				return false; // cancel timer renewal
			}
		}
		else 
		{
			var _percent_float 	= (prconfig.current / prconfig.total) * 100;
			var _percent 		= Math.floor(_percent_float);
			
			var track = {
				id: prconfig.progressKey,
				size: prconfig.total,
			};
			
			
			_runProgressTimer(track, prconfig.total, prconfig.current);
			
			
			_percent = isNaN(_percent) ? 0 : _percent;
			_progress = _percent > 99 ? 99 : _percent;
			
			if ( _progress > 0 ) 
			{
				prconfig.progress_status.html(_progress + ' %');
			}


			var w =  Math.round(_percent_float * prconfig.barwidth / 100) ;
			if ( w > prconfig.barwidth ) {
				w = prconfig.barwidth;
			}
			
			var speed = 800;
			if ( prconfig.current >= prconfig.total )
			{
				speed = 100;
				w = prconfig.barwidth;
			}
			
			if ( w > 0 ) 
			{
				$(prconfig.progress_bar).animate({width: w}, speed);
			}
			
			
			
			if ( prconfig.current >= prconfig.total )
			{
				bRunning = false;
				prconfig.stop = true;
				
				prconfig.elapsed_time.html('0');

				clearTimeout(t);
				clearTimeout(ElapsedTimeInterval);
				
				prconfig.progress_status.html('100 %');
				prconfig.progress_message.html('Aufgabe abgeschlossen.');
				
				uploadProgressTimer[prconfig.progressKey] = 0;
				uploadProgressActive[prconfig.progressKey] = false;

				if ( typeof prconfig.data[2] != 'undefined' && prconfig.data[2] != '' )
				{		
					setTimeout(function(){ 
						setComplMsg(prconfig.data[2], false);
						
						var nextlink = $('#ajax_div').find('a');						
						nextlink.each(function() {
							var nexturl = $(this).attr('href');
							$(this).click(function(e) { 
								e.preventDefault();
								prconfig.progress_script = nexturl;
								prconfig.reset = true;
								prconfig.progress_bar.css({width: '0px'});
								$(document).doRunProgress(prconfig);
							});
						});
					}, 1000);
				}
				else
				{
					clearTimeout(ElapsedTimeInterval);
					
					setComplMsg('Suchindex wurde komplett erstellt.', false);
				}

				
				return false; // cancel timer renewal
			}
			else
			{
				t = setTimeout(function(){ $.fn.progressUpdate(); }, 1100);
			}
		}
	}
})(jQuery);


