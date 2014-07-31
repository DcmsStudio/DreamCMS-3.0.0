/* 
 * DreamCMS 3.0
 * 
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE Version 2
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-2.0.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@dcms-studio.de so we can send you a copy immediately.
 * 
 * PHP Version 5.3.6
 * @copyright	Copyright (c) 2008-2013 Marcel Domke (http://www.dcms-studio.de)
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 */



var IndexerProgress = function () {

    var t, prconfig;
    var defaults = {
        barwidth: 500,
        total: 0,
        current: 0,
        starttimer: 0,
        reset: true,
        data: null,
        progressKey: '',
        currentModul: ''

    };


    if (!$('#ajax_div').length)
    {
        $('body').append($('<div id="ajax_div"/>').hide());
    }



    var notFoundLimit = 50;

    // var seconds = 0;
    var _seconds = 0;
    var _minutes = 0;
    var _hours = 0;
    var _progress_id = 0;
    var _ajax_div = $('#ajax_div');
    var _progress = 0;
    var _heute = 0; 	// Datumsobjekt erstellen
    var _timestamp = 0; 	// Zeitstempel in Millisekunden(!) berechnen
    var _startTime = 0; 	// unix_timestamp
    var UP_timer = 0; 	//


    var ElapsedTimeInterval;
    var barwidth;
    var progress_status;
    var progress_bar;
    var progress_message;
    var est_time_left;
    var elapsed_time;

    var _seconds = 0, _minutes = 0, _hours = 0, _progress = 0, seconds = 0;
    var RunNow = false;
    var bRunning = false;
    var FirstRunNow = false;
    var uploadProgressSettings = new Array();
    var uploadProgressTimer = new Array();
    var uploadProgressNotFound = new Array();
    var uploadProgressActive = new Array();
    var uploadProgressData = new Array();
    var fileSpeedStats = new Array();
    var tracking = {};
    var fileSpeedStats = [];
    var progressKey = '';


    var modules = {};


    function formatUnits (baseNumber, unitDivisors, unitLabels, singleFractional) {
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
                if (i < unitDivisors.length - 1) {
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

    function formatBPS (baseNumber) {
        var bpsUnits = [1073741824, 1048576, 1024, 1], bpsUnitLabels = ["Gbps", "Mbps", "Kbps", "bps"];
        return formatUnits(baseNumber, bpsUnits, bpsUnitLabels, true);
    }

    function formatTime (baseNumber) {
        var timeUnits = [86400, 3600, 60, 1], timeUnitLabels = ["d", "h", "m", "s"];
        return formatUnits(baseNumber, timeUnits, timeUnitLabels, false);
    }

    function formatBytes (baseNumber) {
        var sizeUnits = [1073741824, 1048576, 1024, 1], sizeUnitLabels = ["GB", "MB", "KB", "bytes"];
        return formatUnits(baseNumber, sizeUnits, sizeUnitLabels, true);
    }

    function formatPercent (baseNumber) {
        return baseNumber.toFixed(2) + " %";
    }


    function calculateMovingAverage (history) {
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
    }




    function getElapsedTime ()
    {
        if (!bRunning)
        {
            _seconds = 0;
            _minutes = 0;
            clearTimeout(ElapsedTimeInterval);
            return;
        }

        if (typeof _seconds == 'undefined')
        {
            _seconds = 0;
        }


        if (typeof _minutes == 'undefined')
        {
            _minutes = 0;
        }

        if (typeof _hours == 'undefined')
        {
            _hours = 0;
        }

        _seconds++;
        // seconds++;

        if (_seconds == 60) {
            _seconds = 0;
            _minutes++;
        }

        if (_minutes == 60) {
            _minutes = 0;
            _hours++;
        }
        var hr = "" + ((_hours < 10) ? "0" : "") + _hours;
        var min = "" + ((_minutes < 10) ? "0" : "") + _minutes;
        var sec = "" + ((_seconds < 10) ? "0" : "") + _seconds;

        prconfig.est_time_left.html((hr != '00' ? hr + ' hours : ' : '') + (min != '00' ? min + ' min : ' : '') + sec + ' sec');

        hr = null;
        min = null;
        sec = null;

        ElapsedTimeInterval = setTimeout(function () {
            getElapsedTime();
        }, 1000);
    }

    function updateTracking (file, bytesUploaded) {

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
            tracking.currentSpeed = (deltaBytes * 8) / (deltaTime / 1000);
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
    }


    function _runProgressTimer (track, total, current)
    {
        if (!bRunning)
            return false;
        var byte_speed = 0;
        var time_remaining = 0;

        if (isNaN(total) || isNaN(current))
            return;

        var tracker = updateTracking(track, prconfig.current);
        prconfig.elapsed_time.html(formatTime(tracker.timeRemaining));
        return;
    }



    function setComplMsg (msg)
    {
        //     $('#progress-frame').remove();
        $('#progess-container').hide();
        prconfig.progress_message.html(msg);
        prconfig.stop = true;


        uploadProgressNotFound[prconfig.progressKey] = 0;

        bRunning = false;
        RunNow = false;
        FirstRunNow = false;


        setTimeout(function () {
            $.get(prconfig.progress_script, {stop: true, id: prconfig.progressKey}, function (data) {
                prconfig.progressKey = '';
                prconfig.reset = true;
                Indexer.stopIndexing();

            });

        }, 2000);
    }

    this.doRunProgress = function (options)
    {

        prconfig = $.extend({}, defaults, options);

        if (!prconfig.progressbarIndex)
        {
            Indexer.stopIndexing();
            alert('Progressbar can not run!');
            return false;
        }

        if (prconfig.reset)
        {
            fileSpeedStats = [];
            tracking = {};

            _seconds = 0;
            _minutes = 0;
            _hours = 0;
            _progress = 0;
            //    seconds = 0;

            prconfig.total = 0;
            prconfig.current = 0;
            prconfig.starttimer = 0;
            prconfig.data = null;
            prconfig.stop = false;

            bRunning = false;
            RunNow = false;
            FirstRunNow = false;

            //   progressKey = '';
            //   uploadProgressSettings = new Array();
            uploadProgressTimer = new Array();
            uploadProgressNotFound = new Array();
            uploadProgressActive = new Array();
            uploadProgressData = new Array();


            $('#ajax_div').hide();
            $('#ajax_div').empty();

            prconfig.barwidth = prconfig.progressbar_width;
            prconfig.progress_status = $('#' + prconfig.progressbarName + '_state');
            prconfig.progress_bar = $('#' + prconfig.progressbarName + '_bar');
            prconfig.progress_message = $('#' + prconfig.progressbarName + '_message');
            prconfig.est_time_left = $('#' + prconfig.progressbarName + '_est_time_left');
            prconfig.elapsed_time = $('#' + prconfig.progressbarName + '_elapsed_time');
            prconfig.progress_script = 'admin.php?adm=indexer';
            prconfig.iframeurl = prconfig.iframeurlSet || 'admin.php?adm=indexer';
            prconfig.progressKey = prconfig.progressbarIndex;




            prconfig.progress_bar.css({width: 0 + 'px'});
            prconfig.progress_status.html('0 %');
            prconfig.progress_message.html('Bearbeite Ihre Anfrage ...');

            uploadProgressActive[prconfig.progressKey] = true;
            uploadProgressTimer[prconfig.progressKey] = 1;


            prconfig.est_time_left.empty();


            modules = prconfig.modules;


            clearTimeout(t);
            clearTimeout(ElapsedTimeInterval);
        }
        else
        {
            prconfig.iframeurl = prconfig.iframeurlSet || 'admin.php?adm=indexer';
            prconfig.progressKey = prconfig.progressbarIndex;

            RunNow = false;
            FirstRunNow = false;


        }


        bRunning = true;


        getElapsedTime();

        // this._execute(1, 1);
        this.loadIframe(1, 1);
        this.progressUpdate();
    };



    this.stop = function ()
    {
        clearTimeout(t);
        clearTimeout(ElapsedTimeInterval);

        $(prconfig.progress_bar).stop(true, true).css({width: 0});

        RunNow = false;
        FirstRunNow = false;
        bRunning = false;
        prconfig.total = 0;
        prconfig.current = 0;
        prconfig.starttimer = 0;
        prconfig.data = null;
        prconfig.stop = true;

        uploadProgressActive[prconfig.progressKey] = false;
        uploadProgressData[prconfig.progressKey] = '';
        uploadProgressTimer[prconfig.progressKey] = 0;
        uploadProgressNotFound[prconfig.progressKey] = 0;

        //   $('#progress-frame').remove();

        $.get(prconfig.progress_script, {stop: true, id: prconfig.progressKey}, function (data) {
            prconfig.progressKey = '';
            prconfig.reset = true;
            //Indexer.stopIndexing();
        });
    };


    this.currentModul = null;
    this.executedModules = [];
    this.executeComplete = false;

    this._execute = function (page, current)
    {
        var self = this;
        // var modul = modules.shift();

        //  this.currentModul = modul;







        $.ajax({
            url: 'admin.php',
            type: 'GET',
            dataType: 'json',
            timeout: 1000000,
            data: {
                'adm': 'indexer',
                // action: 'index',
                'page': page,
                'current': current,
                //   nextmodul: modul,
                'RUNID': prconfig.progressKey,
                'ts': new Date().getTime()
            },
            cache: false,
            success: function (data)
            {
                if (Tools.responseIsOk(data)) {
                    self.executeComplete = true;

                    if (typeof data.nextpage != 'undefined' && data.nextpage > 0) {
                        uploadProgressNotFound[prconfig.progressKey] = 0;
                        self._execute(data.nextpage, data.current);
                    }

                    if (typeof data.msg != 'undefined') {
                        clearTimeout(t);
                        clearTimeout(ElapsedTimeInterval);
                        setComplMsg('Suchindex wurde komplett erstellt.', false);
                        return false; // cancel timer renewal
                    }
                }
            }
        });
    };



    this.loadIframe = function (page, current) {


        if ($('#progress-frame').length == 0)
        {
            $('body').append($('<iframe/>').attr({'id': 'progress-frame', 'height': '0', 'width': '0'}).css({'position': 'absolute', 'top': '-2000px', 'left': '-2000px', opacity: '0'}));
        }

        var self = this, url = prconfig.iframeurl;

        $('#progress-frame').attr('src', 'admin.php?adm=indexer&ajax=1&page=' + page + '&current=' + current + '&RUNID=' + prconfig.progressKey);
        prconfig.starttimer = new Date();
        uploadProgressNotFound[prconfig.progressKey] = 0;


        $('#progress-frame').unbind('load').load(function () {




            //  console.log($(this).contents().text());



            var data = jQuery.parseJSON($(this).contents().text());


            if (data && typeof data != 'undefined') {
                if (typeof data.success != 'undefined') {
                    if (Tools.responseIsOk(data)) {

                        self.executeComplete = true;

                        if (typeof data.nextpage != 'undefined' && data.nextpage > 0) {
                            self.loadIframe(data.nextpage, data.current);
                        }


                        if (typeof data.msg != 'undefined') {
                            uploadProgressNotFound[prconfig.progressKey] = 0;
                            clearTimeout(t);
                            clearTimeout(ElapsedTimeInterval);
                            $(this).unbind('load').remove();
                            setComplMsg('Suchindex wurde komplett erstellt.', false);
                            return false; // cancel timer renewal
                        }
                    }
                }
                else {
                    $(this).remove();
                }
            }
            else {
                $(this).remove();
            }

        });

    };


    this.progressUpdate = function ()
    {
        var self = this;

        if (prconfig.stop)
        {
            bRunning = false;
            clearTimeout(t);
            return false;
        }

        if (!RunNow)
        {
            RunNow = true;
            FirstRunNow = true;

            /*
             if ($('#progress-frame').length == 0)
             {
             $('body').append($('<iframe/>').attr({'id': 'progress-frame', 'height': '0', 'width': '0'}).css({'position': 'absolute', 'top': '-2000px', 'left': '-2000px', opacity: '0'}));
             }
             
             
             var url = prconfig.iframeurl;
             
             $('#progress-frame').attr('src', url + '&RUNID=' + prconfig.progressKey);
             prconfig.starttimer = new Date();
             uploadProgressNotFound[prconfig.progressKey] = 0;
             
             
             */
            prconfig.starttimer = new Date();
            uploadProgressNotFound[prconfig.progressKey] = 0;


        }


        if (FirstRunNow)
        {
            setTimeout(function () {
                var _url = prconfig.progress_script + '&GETDATA=' + prconfig.progressKey;

                $.get(_url + '&ajax=1', {}, function (data) {

                    FirstRunNow = false;


                    if (Tools.responseIsOk(data))
                    {

                        if (!prconfig.stop) {
                            uploadProgressNotFound[prconfig.progressKey] = 0;
                            self.formatIndexingProgress(data);
                        }
                    }
                    else
                    {
                        //       console.log('Indexer Hard Stop!');

                        bRunning = false;
                        prconfig.stop = true;
                        clearTimeout(t);
                        clearTimeout(ElapsedTimeInterval);
                        Indexer.stopIndexing();
                        alert('Error: ' + data.msg);
                    }
                }, 'json');
            }, 1000);
        }
        else
        {

            var _url = prconfig.progress_script + '&GETDATA=' + prconfig.progressKey;


            $.get(_url + '&ajax=1', {}, function (data) {
                if (Tools.responseIsOk(data))
                {
                    if (!prconfig.stop) {
                        uploadProgressNotFound[prconfig.progressKey] = 0;
                        self.formatIndexingProgress(data);
                    }
                }
                else
                {
                    uploadProgressNotFound[prconfig.progressKey]++;
                }
            }, 'json');
        }

    };

    this.formatIndexingProgress = function (data)
    {
        var self = this;

        console.log(data.output + ' Errors:' + uploadProgressNotFound[prconfig.progressKey]);

        if (!data.globalTotal)
        {
            uploadProgressNotFound[prconfig.progressKey]++;

            if (uploadProgressNotFound[prconfig.progressKey] >= notFoundLimit) {
                //    console.log('Indexer Hard Stop!');

                bRunning = false;
                prconfig.stop = true;

                uploadProgressActive[prconfig.progressKey] = false;
                uploadProgressData[prconfig.progressKey] = '';
                uploadProgressTimer[prconfig.progressKey] = 0;
                clearTimeout(t);
                clearTimeout(ElapsedTimeInterval);

                alert("Error");

                Indexer.stopIndexing();

                return false; // cancel timer renewal
            }

            setTimeout(function () {
                self.progressUpdate()
            }, 1000);

            return;
        }

        var page = prconfig.page;
        var modul = prconfig.modul || false;



        prconfig.data = data;

        if (data.filenotexists)
        {
            //     console.log('Indexer Hard Stop!');

            prconfig.stop = true;
            bRunning = false;
            clearTimeout(t);
            clearTimeout(ElapsedTimeInterval);

            Indexer.stopIndexing();

            return false; // cancel timer renewal
        }


        // $this->total . '|' . $mod . '|' . $total_found . '|' . $y . '|' . ($page + 1) . '|'

        prconfig.modul = data.nextModul || '';
        prconfig.globalTotal = parseInt(data.globalTotal);


        prconfig.current = parseInt(data.current);
        prconfig.total = parseInt(prconfig.data[2]);
        prconfig.page = parseInt(data.page);
        prconfig.message = data.message;


        uploadProgressData[prconfig.progressKey] = prconfig.data;

        if (isNaN(prconfig.globalTotal) || isNaN(prconfig.current))
        {
            uploadProgressNotFound[prconfig.progressKey]++;

            if (uploadProgressNotFound[prconfig.progressKey] >= notFoundLimit) {

                //    console.log('Indexer Hard Stop!');

                bRunning = false;
                prconfig.stop = true;

                uploadProgressActive[prconfig.progressKey] = false;
                uploadProgressData[prconfig.progressKey] = '';
                uploadProgressTimer[prconfig.progressKey] = 0;
                clearTimeout(t);
                clearTimeout(ElapsedTimeInterval);

                Indexer.stopIndexing();

                alert("Error");
                return false; // cancel timer renewal
            }
        }
        else
        {
            uploadProgressNotFound[prconfig.progressKey] = 0;

            var _percent_float = (prconfig.current / prconfig.globalTotal) * 100;
            var _percent = Math.floor(_percent_float);

            var track = {
                id: prconfig.progressKey,
                size: prconfig.globalTotal,
            };


            _runProgressTimer(track, prconfig.globalTotal, prconfig.current);


            _percent = isNaN(_percent) ? 0 : _percent;
            _progress = _percent > 99 ? 100 : _percent;

            if (_progress > 0)
            {
                prconfig.progress_status.html(_progress + ' %');
            }


            var w = Math.round(_percent_float * prconfig.progressbarWidth / 100);

            if (w > prconfig.progressbarWidth) {
                w = prconfig.progressbarWidth;
            }

            var speed = 800;
            if (prconfig.current >= prconfig.globalTotal)
            {
                speed = 100;
                w = prconfig.progressbarWidth;
            }

            if (w > 0)
            {
                $(prconfig.progress_bar).stop(true, true).animate({width: w}, speed);
            }



            if (_progress >= 100 || prconfig.message || prconfig.current >= prconfig.globalTotal)
            {
                clearTimeout(t);
                clearTimeout(ElapsedTimeInterval);
                setComplMsg('Suchindex wurde komplett erstellt.', false);
                return false; // cancel timer renewal
            }

            if (this.executeComplete) {
                //  this.executeComplete = false;
                //  this.execute(prconfig.page);
            }

            /*
             if (modul !== false && prconfig.modul != '' && modul != prconfig.modul && prconfig.page == 1)
             {
             $('#progress-frame').remove();
             $('body').append($('<iframe/>').attr({'id': 'progress-frame', 'height': '0', 'width': '0'}).css({'position': 'absolute', 'top': '-2000px', 'left': '-2000px', opacity: '0'}));
             $('#progress-frame').attr('src', 'admin.php?adm=indexer&nextmodul=' + prconfig.modul + '&page=1&current=' + prconfig.current + '&RUNID=' + prconfig.progressKey);
             prconfig.starttimer = new Date();
             
             RunNow = true;
             FirstRunNow = true;
             t = setTimeout(function () {
             self.progressUpdate();
             }, 500);
             
             return;
             }
             
             
             if (prconfig.modul != '' && (modul == false && page < prconfig.page) || (modul !== false && prconfig.modul != '' && modul == prconfig.modul && page < prconfig.page))
             {
             $('#progress-frame').remove();
             $('body').append($('<iframe/>').attr({'id': 'progress-frame', 'height': '0', 'width': '0'}).css({'position': 'absolute', 'top': '-2000px', 'left': '-2000px', opacity: '0'}));
             $('#progress-frame').attr('src', 'admin.php?adm=indexer&nextmodul=' + prconfig.modul + '&page=' + prconfig.page + '&current=' + prconfig.current + '&RUNID=' + prconfig.progressKey);
             prconfig.starttimer = new Date();
             
             RunNow = true;
             FirstRunNow = true;
             t = setTimeout(function () {
             self.progressUpdate();
             }, 500);
             
             return;
             }
             
             */
            t = setTimeout(function () {
                self.progressUpdate();
            }, 1000);


        }
    };
};



var Indexer = {
    isVisible: false,
    arrowHeight: 10,
    inited: false,
    runIndexing: false,
    searchPopup: '<div id="searchPopup" class="indexer-popup"></div>',
    tpl: '<div id="progess-container"><table class="progressb" cellpading="0" cellspacing="0">'
            + '	<tr>'
            + '	    <td>'
            + '         <div class="progressborder">'
            + '             <div id="[progressbar_name]_state" class="progressb_state">0 %</div>'
            + '             <div id="[progressbar_name]_bar" class="progress_bar" style="width:0px;"></div>'
            + '         </div>'
            + '     </td>'
            + '	</tr>'
            + '</table>'
            + '<div class="elapsed" style="display:block;width:100%;text-align:center;">'
            + '    <div style="display:inline-block;">Laufzeit: <span id="[progressbar_name]_est_time_left">0</span></div>'
            + '    <div style="display:inline-block;margin-left:10px;">verbliebenen Zeit: <span id="[progressbar_name]_elapsed_time">0</span></div>'
            + '    <div id="cancel-indexing-btn"></div>'
            + '</div></div>'
            + '<div id="[progressbar_name]_message" class="progress_work_text">Bearbeite Ihre Anfrage...</div>',
    progress: null,
    searchDiv: null,
    searchResultDiv: null,
    indexingDiv: null,
    opts: {
        progressbarName: 'searchindex',
    },
    setOptions: function (options) {
        this.opts = $.extend({}, this.opts, options);
    },
    init: function ()
    {
        var self = this, indexer;

        if (Desktop.isWindowSkin) {
            if (!$('#Tasks-Core #indexer').length)
            {
                indexer = $('<div id="indexer" class="Taskbar-Item"/>').append('<span></span>');
                $('#Tasks-Core').append(indexer);
            }
            else
            {
                indexer = $('#Tasks-Core #indexer');
            }
        }
        else {
            if (!$('#menu-extras #indexer').length)
            {
                indexer = $('<span id="indexer" title="' + cmslang.search + '" class="Taskbar-Item"/>').append('<span class="fa fa-search"></span>');
                indexer.insertAfter($('#menu-extras #dcmsFav'));
            }
            else
            {
                indexer = $('#menu-extras #indexer');
            }
        }

        var popUp = $('#searchPopup');
        if (!$('#searchPopup').length)
        {
            var pop = $(self.searchPopup).hide();
            pop.append($('<div id="searcher"/>').append('<div class="searcher-txt">Spotlight <input type="text" name="q" value="" /></div>').append($('<div id="create-search-index"><span>Create Search Index</span></div>')).append($('<div id="searcher-result"><div id="calc-result" class="row"><div class="left-pos">Rechner</div><div class="right-pos"></div></div><div id="doc-result"></div></div>').hide()));

            this.tpl = this.tpl.replace(/\[progressbar_name\]/g, this.opts.progressbarName);
            pop.append($('<div id="searcher-indexing"/>').hide().append(this.tpl));

            if ($('#fullscreenContainer').length) {
                $('#fullscreenContainer').append(pop);
            }
            else {
                $('body').append(pop);
            }
                        
            popUp = $('#searchPopup');
        }

        this.searchDiv = $('#searcher', popUp);
        this.indexingDiv = $('#searcher-indexing', popUp);
        this.searchResultDiv = $('#searcher-result', popUp);
        
        this.createIndexBtn = $('#create-search-index', popUp);
        this.inputField = this.searchDiv.find('input:first');
        this.inputField.attr('autocomplete', 'off');



        this.createIndexBtn.find('span:first').unbind('click.indexer').bind('click.indexer', function () {
            if (self.progress === null) {
                self.createIndex();
            }
        });


        $('#cancel-indexing-btn', popUp).unbind('click.indexer').bind('click.indexer', function () {
            if (self.progress !== null) {
                self.stopIndexing();
            }
        });


        indexer.unbind('click.indexer').bind('click.indexer', function () {
            if (!popUp.is(':visible'))
            {
                $(this).addClass('active');
                var o = indexer.offset();
                popUp.css({left: o.left - popUp.width() + indexer.outerWidth(true), top: $('#Taskbar').outerHeight(true)}).show();
                self.isVisible = true;


                if (self.inputField.val()) {
                    var resultDiv = $('#calc-result div:last');
                    if (resultDiv.text()) {
                        $('#calc-result').show();
                        self.searchResultDiv.show();
                    }
                    if (self.searchResultDiv.find('#doc-result').find('.row').length) {
                        self.searchResultDiv.find('#doc-result').find('.hover').removeClass('hover');
                        self.searchResultDiv.show();
                    }
                }



            }
            else
            {
                $(this).removeClass('active');
                popUp.hide();
                self.isVisible = false;
            }
        });

        this.simpleCalculator();

        this.inputField.unbind('keyup.search').on('keyup.search', function (e) {
            self.createIndexBtn.hide();
            self.isVisible = true;
            self.searchResultDiv.show().find('#doc-result');
            self.search(e);
        });

        this.inputField.unbind('blur.search').on('blur.search', function (e) {
            if (!$(e.originalEvent.target).parents('#searcher').length) {
                self.createIndexBtn.show();
                self.searchResultDiv.hide();
                self.isVisible = false;
            }
        });


        $('#desktop,#DesktopIcons,#Taskbar,#dock').unbind('click.searcher').on('click.searcher', function (e) {
            if (!$(e.target).parents('#searcher').length) {
                self.createIndexBtn.show();
                $('.search-result-preview').hide();
                self.searchResultDiv.hide();
                self.isVisible = false;
            }
        });



    },
    st: null,
    search: function (e) {
        clearTimeout(this.st);
        var self = this, length = this.inputField.val().length;

        $('.search-result-preview').remove();

        if (length >= 3) {
            this.st = setTimeout(function () {

                $.ajax({
                    url: 'admin.php',
                    type: 'POST',
                    dataType: 'json',
                    timeout: 30000,
                    data: {adm: 'search', action: 'index', q: self.inputField.val()},
                    cache: false,
                    success: function (data)
                    {
                        if (Tools.responseIsOk(data)) {
                            self.renderSearchResult(data);
                        }
                    }
                });

            }, 500);
        }


    },
    pi: null,
    renderSearchResult: function (data) {

        this.isVisible = false;

        if (data && data.searchresult && data.searchresult.length) {

            var self = this, hoverDelay, tmp = [];
            var res = this.searchResultDiv.show().find('#doc-result');
            res.empty();

            for (var i = 0; i < data.searchresult.length; ++i) {
                var d = data.searchresult[i];
                res.append($('<div class="row">').data('r', d).attr('rel', d.id).append($('<div class="left-pos">' + (typeof d.label === 'string' ? d.label : '') + '</div><div class="right-pos"><a href="' + d.url + '" target="_blank">' + d.title + '</a></div>')));
            }


            res.find('div.row').each(function () {

                var row = $(this);

                hoverDelay = setTimeout(function () {

                    row.hover(function (e) {
                        clearTimeout(self.pi);
                        var r = $(this).parent().find('div.hover').data('r');
                        if (r) {
                            $(this).parent().find('div.hover').removeClass('hover');
                            $('#search-preview-' + r.id).hide();
                        }

                        r = $(this).data('r');
                        $(this).addClass('hover');
                        if ($('#search-preview-' + r.id).length) {

                            var offset = $(e.target).offset();
                            var ResultOffset = self.searchResultDiv.offset();
                            var top = offset.top, diff = 0;
                            var desktopHeight;

                            if (Desktop.isWindowSkin) {
                                desktopHeight = $('#desktop').height() - $('#Taskbar').outerHeight(true) - $('#dock').outerHeight(true) - 20;
                            }
                            else {
                                desktopHeight = $('#main-content-inner').height() - 20;
                            }

                            if ($('#search-preview-' + r.id).outerHeight(true) + top > desktopHeight) {
                                diff = $('#search-preview-' + r.id).outerHeight(true) + top - desktopHeight;
                            }

                            $('#search-preview-' + r.id + ' .search-result-preview-arrow').css({top: (diff - $(e.target).height() > 0 ? diff - $(e.target).height() : 2)});
                            $('#search-preview-' + r.id).css({
                                top: (diff > 0 ? (top - diff) + $(e.target).height() : top),
                                left: ResultOffset.left - $('#search-preview-' + r.id).width() - (Desktop.isWindowSkin ? 20 : 0)
                            }).show();


                            Tools.scrollBar($('#search-preview-' + r.id).find('div.scrollable'));
                        }
                        else {
                            self.pi = setTimeout(function () {
                                $.post('admin.php', {
                                    adm: 'search',
                                    action: 'preview',
                                    id: r.id
                                }, function (dat) {
                                    if (Tools.responseIsOk(dat)) {
                                        if (dat.id) {
                                            self.renderPreview(dat, e);
                                        }
                                    }
                                });
                            }, 1000);
                        }

                    }, function () {

                    });

                }, 500);
            });

            this.isVisible = true;

        } else {
            this.searchResultDiv.show().find('#doc-result').empty();
        }
    },
    renderPreview: function (data, e) {
        if (!$('#search-preview-' + data.id).length) {
            var container = $('<div class="search-result-preview">').attr('id', 'search-preview-' + data.id);

            container.append('<div class="search-result-preview-arrow"></div>');
            var inner = $('<div class="scrollable">');
            inner.append(data.content);
            var outer = $('<div>');
            outer.append(inner);

            container.append(outer);
            var offset = $(e.target).offset();
            var ResultOffset = this.searchResultDiv.offset();
            container.show();


            if ($('#fullscreenContainer').length) {
                $('#fullscreenContainer').append(container);
            }
            else {
                $('body').append(container);
            }
            
            var desktopHeight;
            if (Desktop.isWindowSkin) {
                desktopHeight = $('#desktop').height() - $('#Taskbar').outerHeight(true) - $('#dock').outerHeight(true) - 20;
            }
            else {
                desktopHeight = $('#main-content-inner').height() - 20;
            }

            var top = offset.top, diff = 0;
            if (container.outerHeight(true) + top > desktopHeight) {
                diff = container.outerHeight(true) + top - desktopHeight + $(e.target).height();
            }

            container.find('div.search-result-preview-arrow').css({top: (diff - $(e.target).height() > 0 ? diff - $(e.target).height() : 2)});
            container.css({
                top: (diff > 0 ? (top - diff) + $(e.target).height() : top),
                left: ResultOffset.left - container.width() - (Desktop.isWindowSkin ? 20 : 0)
            });

            container.height(container.outerHeight(true));
            outer.height(container.height() - (parseInt(container.css('paddingTop'), 0) * 2));
            container.find('a').each(function () {
                $(this).attr('target', '_blank');
            });


            Tools.scrollBar($('#search-preview-' + data.id).find('div.scrollable'));
        }
    },
    hide: function () {
        $('#indexer').removeClass('active');
        $('#searchPopup').hide();
        $('body .search-result-preview').hide();
        if ( this.searchResultDiv ) { this.searchResultDiv.hide(); }
        this.isVisible = false;
    },
    stopIndexing: function ()
    {
        if (this.progress !== null) {
            this.progress.stop();
        }
        $('body').css({cursor: ''});

        this.progress = null;
        this.indexingDiv.hide();
        this.searchDiv.show();
        $('#progess-container').show();
    },
    createIndex: function ()
    {
        var self = this;

        this.searchDiv.hide();
        this.indexingDiv.show();
        if (this.progress === null)
        {
            $('#progess-container').show();


            $.ajax({
                url: 'admin.php',
                type: 'POST',
                dataType: 'json',
                timeout: 30000,
                data: {adm: 'indexer', action: 'index', ts: new Date().getTime()},
                cache: false,
                success: function (data)
                {
                    if (Tools.responseIsOk(data))
                    {
                        var opt = $.extend({}, self.opts, data.progressbar);
                        opt.page = 1;
                        opt.reset = true;

                        self.progress = new IndexerProgress(opt);
                        self.progress.doRunProgress(opt);
                    }
                    else
                    {
                        self.stopIndexing();
                    }
                }
            });
        }
    },
    costToString: function (cost, fixed) {
        var triadSeparator = ' ';
        var decSeparator = ',';
        var minus = '&minus;';
        var num = '0';
        var numd = '';
        var fractNum = 2;
        fixed = (!fixed) ? fixed = 2 : fixed;
        var fixedTest = '00';
        if (fixed != 2) {
            fixedTest = '';
            for (var i = 0; i < fixed; i++) {
                fixedTest += String('0');
            }
        }
        if (!isNaN(parseFloat(cost))) {
            num = parseFloat(Math.abs(cost)).toFixed(fixed).toString();
            numd = num.substr(num.indexOf('.') + 1, fixed).toString();
            num = parseInt(num).toString();
            var regEx = /(\d+)(\d{3})/;
            while (regEx.test(num)) {
                num = num.replace(regEx, "$1" + triadSeparator + "$2");
            }
            if (numd != fixedTest) {
                var lastZeros = /[0]*$/g
                num += decSeparator + numd.replace(lastZeros, '');
            }
            if (cost < 0)
                num = '−' + num;
        }
        return num;
    },
    simpleCalculator: function ()
    {
        var self = this, input = this.inputField;
        var resultDiv = $('#calc-result div:last');
        var calc = $('#calc-result');
        var nchars = new RegExp(/[\!\@\#\№\$\%\^\&\=\[\]\\\'\;\{\}\|\"\:\<\>\?~\`\_A-ZА-Яa-zа-я]/);
        var achars = "1234567890+-/*,. ";
        var numchars = new RegExp(/^[\s0-9]/);

        var c, oldVal = 0;
        var newVal = 0;
        var regClean = new RegExp(' ', 'gi');
        var aripm = new RegExp(/[\+\-\*\/]/);
        var aripmSt = new RegExp(/^[\+\-\*\/]/);
        var options = jQuery.extend({
            error: false, // Элемент отображаемый при ошибке
            comment: false, // Элемент отображаемы при редактировании
            calculatewrapper: false, // Элемент будет отображен при расчетах
            calculate: false, // Элемент куда будет выводится результат вычисления
            oncalculate: false, // Функция вызывается если введены [+,-,*,/,(,)]
            onendcalculate: false, // Функция вызывается если удалены все [+,-,*,/,(,)]
            onready: false, // Функция вызывается при подготовке элемента
            onfocus: false, // Функция вызывается при установке фокуса
            onblur: false, // Функция вызывается при потере фокуса (в функцию передается конечный результаты ввода)
            onerror: false, // Функция вызывается при попытке ввода запрещенных символов
            onenter: false, // Функция вызывается если нажата клавиша enter (вызывается ДО onblur)
            onescape: false, // Функция вызывается если нажата клавиша escape (вызывается ДО onblur)
            oninput: false, // Функция вызывается при вводе любого символа (в функцию передается введенный символ)
            ifnul: '', // Символ вставляемый если введеные данные ошибочны или 0
            sign: false // Показывать знак минус при вводе отрицательного значения
        }, options);


        var error = false;

        input.unbind('keypress.calc').on('keypress.calc', function (e) {
            var k, i;
            var tAllow = false;

            if (!e.charCode) {
                k = String.fromCharCode(e.which);
                c = e.which;
            } else {
                k = String.fromCharCode(e.charCode);
                c = e.charCode;
            }

            if (c == 37 || c == 39) {
                return true;
            }


            if (!e.ctrlKey) {
                var res = nchars.test(k);
                var numtest = numchars.test(k);


                if (res && !numtest) {

                    calc.hide();
                    resultDiv.empty();
                    error = true;
                    return true;
                } else {
                    error = false;

                    if (e.keyCode == 13) {
                        calc.show();
                    }
                }
            }
        });

        input.unbind('keyup.calc').on('keyup.calc', function (e) {
            if (error) {
                return true;
            }
            var newVal = String(this.value).replace(/ /g, '').replace(/,/g, '.');
            if (e.keyCode == 27) {
                toOldVal = true;
                // self.searchResultDiv.hide();
                calc.hide();
                resultDiv.empty();
                return true;
            }


            var strValue = String(this.value).replace(/^\s*/g, '').replace(/\s*$/g, '').replace(/\./g, ',');

            strValue = strValue.replace(/\s*(\+)\s*/g, ' $1 ');
            strValue = strValue.replace(/\s*(-)\s*/g, ' $1 ');
            strValue = strValue.replace(/\s*(\/)\s*/g, ' $1 ');
            strValue = strValue.replace(/\s*(\*)\s*/g, ' $1 ');



            var res = aripm.test(newVal);
            var numtest = numchars.test(newVal);

            if (res && numtest)
            {
                res = aripmSt.test(newVal);

                if (res) {
                    var tStr = String(oldVal) + String(newVal);
                    try {
                        newVal = parseFloat(eval(tStr), 10);
                        newVal = isNaN(newVal) ? (0) : (newVal);
                        newVal = isFinite(newVal) ? (newVal) : (0);
                        resultDiv.html(strValue + ' = ' + self.costToString(parseFloat(newVal, 10), 8));
                        calc.show();
                    } catch (e) {
                        //calc.hide();
                        newVal = 0;
                        calc.hide();
                        return true;
                    }
                } else {
                    var tStr = String(newVal);
                    try {
                        newVal = parseFloat(eval(tStr), 10);
                        newVal = isNaN(newVal) ? (0) : (newVal);
                        newVal = isFinite(newVal) ? (newVal) : (0);
                        resultDiv.html(strValue + ' = ' + self.costToString(parseFloat(newVal, 10), 8));

                        calc.show();
                    } catch (e) {

                        newVal = 0;
                        calc.hide();
                        return true;
                    }
                }
            } else {

                if (isNaN(parseFloat(newVal, 10))) {
                    newVal = 0;
                    calc.hide();
                } else {
                    resultDiv.html(strValue + ' = ' + self.costToString(parseFloat(newVal, 10), 8));
                    calc.show();
                }


            }

        });



    }




};