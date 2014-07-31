var Worker = {
    
    pending: {},
    queue: {},
    
    
    doWork: function(callbackFunction)
    {
        var callbackFunction = false;

        if (typeof arguments[0] === 'function') 
        {
            callbackFunction = arguments[i];        
        }

        
        if (callbackFunction == false)
        {
            Debug.error('No function for Worker.');
            return;
        }

        this.queue.push(callbackFunction);
        this.run();
    },
    
    
    
    run: function()
    {
        var p, len;
        // If a previous load request of this type is currently in progress, we'll
        // wait our turn. Otherwise, grab the next item in the queue.
        if ( !this.pending.length || !this.queue.length ) {
            return false;
        }
 
        if ( this.queue.length > 0 )
        {
            p = this.queue.shift();
            this.pending.push( p );
        }
        else
        {
            clearTimeout(this.workTimer);
            return;
        }
        
        for (var i = 0, len = p.length; i < len; ++i)
        {
            this.work(p[i]);
        }
        
        this.finish();
    },
    
    
    workTimer: null,
    working: false,
    work: function(callback)
    {
        
        if ( this.working )
        {
            var self = this;
            
            this.workTimer = setTimeout(function(){
                self.working();
            },10);
        }
        else
        {
                
            this.working = true;
                
            callback();
            
            this.working = false;

            
        }
        
        
    },
    
    
    
    finish: function()
    {
        this.queue = {};
        this.pending = {};
    }
};















function watch_connection() {
    // do ajax get
    $.ajax({
        type: "GET",
        datatype: "xml",
        url: "http://my_ip_address:port/Services/ConnectServiceHandler",
        success: function(response){
            $('#post_results').html(response);
        },
        error:function (xhr, ajaxOptions, thrownError){
            $('#post_results').html("readyState: "+xhr.readyState+"\nstatus: "+xhr.status);
        }
    });
    setTimeout(function(){watch_connection;}, 100);
}

function disconnect_service_handler() {

    // step 1. create xml document of data to send
    var xml_string = '<data><disconnect_handler service="64"/></data>';

    // step 2. post this to the registration service
    $.ajax({
        type: "POST",
        datatype: "xml",
        url:"http://my_ip_address:port/Services/DisconnectServiceHandler",
        data: xml_string,
        beforeSend: function(xhr){
               xhr.withCredentials = true;
        },
        timeout: (2 * 1000),
        success: function(response){

            // parse the response
            $(response).find("status").each(function() {
                // get the status code
                var disconnect_status = $(this).attr('code');

                if (disconnect_status == 200) {
                    // change status bar message
                    $('#dis_status').html('Disconnecting: [200 Disconnected]');

                    // call connection using new guid
                    var my_guid = guid();
                    connect_service_handler(my_guid);
                }

                if (disconnect_status == 304) {
                    // change status bar message
                    $('#dis_status').html('Disconnecting: [304 No handler found]');
                }


                if (disconnect_status == 400) {
                    // change status bar message
                    $('#dis_status').html('Disconnecting: [400 Bad Request]');
                }

                if (disconnect_status == 401) {
                    // change status bar message
                    $('#dis_status').html('Disconnecting: [401 Not Found]');
                }

                if (disconnect_status == 500) {
                    // change status bar message
                    $('#dis_status').html('Disconnecting: [500 Internal Server Failure]');
                }

                if (disconnect_status == 501) {
                    // change status bar message
                    $('#dis_status').html('Disconnecting: [503 Service Unavailable]');
                }


            });


        },
        error:function (xhr, ajaxOptions, thrownError){
            $('#dis_status').html('Disconnecting: [Disconnect Failure]');
        }

    });
}
function S4() {
   return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
}
function guid() {
   return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
}
function connect_service_handler(my_guid) {

    // step 1. create xml document of data to send
    var xml_string = '<data><connect_handler service="64"><application id="'+my_guid+'" name="MikesBigEar" /></connect_handler></data>';

    // step 2. post this to the registration service
    $.ajax({
        type: "POST",
        datatype: "xml",
        url:"http://my_ip_address:port/Services/ConnectServiceHandler",
        data: xml_string,
        beforeSend: function(xhr){
               xhr.withCredentials = true;
        },
        timeout: (2 * 1000),
        success: function(response){

            // parse the response
            $(response).find("status").each(function() {

                // get the status code
                var connection_status = $(this).attr('code');

                if (connection_status == 200) {
                    // change status bar message
                    $('#csh_status').html('Service Handler: [200 Connected]');
                    // keep connection open keep socket alive
                    // sends http request to us via post
                    // sends the incoming request id and device address back to make sure it goes to the correct device
                    // ask for user id or user authentication
                    // for user authentication can either use built-in user authentication or ask a question
                    // http 1.1 keep alive header
                    $('#post_results').html('Attempting to check for next piece of data...');
                    watch_connection();
                }

                if (connection_status == 303) {
                    // change status bar message
                    $('#csh_status').html('Service Handler: [303 The handler is assigned to another application]');
                    var my_guid = guid();
                    connect_service_handler(my_guid);
                }

                if (connection_status == 400) {
                    // change status bar message
                    $('#csh_status').html('Service Handler: [400 Bad Request]');
                    disconnect_service_handler();
                }

                if (connection_status == 401) {
                    // change status bar message
                    $('#csh_status').html('Service Handler: [401 Not Found]');
                    disconnect_service_handler();
                }

                if (connection_status == 500) {
                    // change status bar message
                    $('#csh_status').html('Service Handler: [500 Internal Server Failure]');
                    disconnect_service_handler();
                }

                if (connection_status == 501) {
                    // change status bar message
                    $('#csh_status').html('Service Handler: [501 Service Unavailable]');
                    disconnect_service_handler();
                }


            });

            // pass the xml to the textarea
            // $('#process').val('ConnectServiceHandler');
            // $('#show_errors_here').val(response);

        },
        error:function (xhr, ajaxOptions, thrownError){
            $('#csh_status').html('Service Handler: [Connection Failure]');
            // alert("readyState: "+xhr.readyState+"\nstatus: "+xhr.status);
            // alert("responseText: "+xhr.responseText);
            // alert(xhr.status);
            // alert(thrownError);
        }

    });

    // set timed re-check and store it
    // setTimeout(function(){connect_service_handler(my_guid);}, 8000);


}

function get_device_count(my_guid) {
    // get the total number of devices

    // default receiver status
    var receiver_status = '';


    $('#device_count').html('Device Count: [Checking...]');
    $('#device_info').html('');

    // get the wireless receiver status via ajax xml
    $.ajax({
        type: "GET",
        url:"http://my_ip_address:port/Services/GetDevices",
        beforeSend: function(xhr){
               xhr.withCredentials = true;
        },
        timeout: (2 * 1000),
        success: function(response){

            $(response).find("status").each(function() {
                // get the status code
                var receiver_status = $(this).attr('code');

                if (receiver_status == 200) {
                    // change status bar message
                    $('#device_count').html('Device Count: [200 Connected]');
                }

                if (receiver_status == 400) {
                    // change status bar message
                    $('#device_count').html('Device Count: [400 Bad Request]');
                }

                if (receiver_status == 401) {
                    // change status bar message
                    $('#device_count').html('Device Count: [401 Not Found]');
                }

                if (receiver_status == 500) {
                    // change status bar message
                    $('#device_count').html('Device Count: [500 Internal Server Failure]');
                }

                if (receiver_status == 501) {
                    // change status bar message
                    $('#device_count').html('Device Count: [501 Service Unavailable]');
                }


            });

            var device_count = 0;

            // add to div
            $('#device_info').append('<ul style="font-size:10px;">');

            $(response).find("device").each(function() {

                // get each property
                var device_status = $(this).attr('status');
                var short_address = $(this).attr('short_address');
                var mac_address = $(this).attr('mac_address');
                var pan_id = $(this).attr('pan_id');
                var type = $(this).attr('type');

                device_count = device_count + 1;

                // get session data
                $(this).find("session").each(function() {

                    // get session data
                    var created_date = $(this).attr('date');
                    var created_time = $(this).attr('time');

                });

                $('#device_info').append('<li style="list-style:none;">Device #'+device_count+'<ul>');

                // add list item
                $('#device_info').append('<li> Mac Address: ['+mac_address+']</li>');
                $('#device_info').append('<li> Short Address: ['+short_address+']</li>');
                $('#device_info').append('<li> Pan ID: ['+pan_id+']</li>');

                $('#device_info').append('</ul></li><br/>');

                // send request to this device
                // post_live_activity(mac_address,my_guid);



            });

            // end list
            $('#device_info').append('</ul>');

            if (device_count === 0) {
                $('#device_count').html('Device Count: [0 Devices Found]');
            } else if (device_count > 0) {
                $('#device_count').html('Device Count: [' + device_count + ' Devices Found]');
            }


        },
        error:function (xhr, ajaxOptions, thrownError){
            $('#device_count').html('Device Count: [Connection Failure]');
            // alert(xhr.status);
            // alert(thrownError);
        }
    });

    // set timed re-check and store it
    setTimeout(function(){get_device_count(my_guid);}, 13000);
}
function get_server_status(my_guid) {

    // default receiver status
    var receiver_status = '';

    // get the Renaissance Wireless Server via ajax xml
    $.ajax({
        type: "GET",
        url:"http://my_ip_address:port/Services/GetAccessPoints",
        timeout: (2 * 1000),
        beforeSend: function(xhr){
               xhr.withCredentials = true;
        },
        success: function(response){

            $(response).find("status").each(function() {
                // get the status code
                var receiver_status = $(this).attr('code');

                if (receiver_status == 200) {

                    // change status bar message
                    $('#server_status').html('Renaissance Wireless Server: [200 Connected]');

                    // step 2. get device count
                    get_device_count(my_guid);

                    // step 3.part 1 get the guid to be used as the application id
                    // var my_guid = guid();

                    // step 3. part 2 connect to a service handler whatever that means
                    connect_service_handler(my_guid);

                }

                if (receiver_status == 400) {

                    // change status bar message
                    $('#server_status').html('Renaissance Wireless Server: [400 Bad Request]');

                    // set timed re-check and store it
                    setTimeout(function(){get_server_status(my_guid);}, 12300);

                }

                if (receiver_status == 401) {

                    // change status bar message
                    $('#server_status').html('Renaissance Wireless Server: [401 Not Found]');

                    // set timed re-check and store it
                    setTimeout(function(){get_server_status(my_guid);}, 12300);
                }

                if (receiver_status == 500) {

                    // change status bar message
                    $('#server_status').html('Renaissance Wireless Server: [500 Internal Server Failure]');

                    // set timed re-check and store it
                    setTimeout(function(){get_server_status(my_guid);}, 12300);

                }

                if (receiver_status == 501) {

                    // change status bar message
                    $('#server_status').html('Renaissance Wireless Server: [503 Service Unavailable]');

                    // set timed re-check and store it
                    setTimeout(function(){get_server_status(my_guid);}, 12300);

                }
                // pass the xml to the textarea
                // $('#process').val('GetAccessPoints');
                // $('#show_errors_here').val(response);

            });

        },
        error:function (xhr, ajaxOptions, thrownError){
            $('#server_status').html('Renaissance Wireless Server: [Connection Failure]');
            // alert(xhr.status);
            // alert(thrownError);
        }
    });

    // set timed re-check and store it
    // setTimeout(function(){get_server_status(my_guid);}, 12300);
}

$(document).ready(function() {

    // step 3.part 1 get the guid to be used as the application id
    var my_guid = guid();

    // step 1 validate
    get_server_status(my_guid);

    // step 2. get device count
    get_device_count();

    // step 3.part 1 get the guid to be used as the application id
    // var my_guid = guid();

    // step 3. part 2 connect to a service handler whatever that means
    // connect_service_handler(my_guid);


});