    var text_max = 300;
    $('#textarea_feedback').html(text_max + ' {qrcode.characters}');


	function getWin() {
		return(!window.frameElement && window.dialogArguments) || opener || parent || top;
	}

	var w = getWin();
	var editor = parent.tinymce.EditorManager.activeEditor;
	var params = editor.windowManager.getParams();
	var trans = w.tinymce.util.I18n;



	var html = document.getElementsByTagName('html')[0].innerHTML;
	var m = html.match(/\{#?([a-z0-9_\.]+)\}/gi);
	for ( var i = 0; i < m.length; i++ ) {
		html = html.replace(m[i] , trans.translate(m[i].replace(/([\{\}#]+?)/g, '')) );
	}
	document.getElementsByTagName('html')[0].innerHTML = html;





	if (params) {

		if (params.controlType) {
			$('#formatOption' ).find('option' ).eq(params.controlType ).attr('selected', 'selected' ).prop('selected', true);
			$('#formatOption' ).trigger('change');
			//$('#formatOption' ).val(params.controlType);
		}


		if ( params.size ) {
			$("#codeSize").val(params.size);
			$('#codeSize' ).trigger('change');
		}
		if ( params.data ) {
			$("#codeData").val( decodeURIComponent(params.data) );
		}
		if ( params.qzone ) {
			$("#q_zone").val(params.qzone);
		}
		if ( params.bgcolor ) {
			$("#bgColor").val('#'+params.bgcolor);
		}

		if ( params.color ) {
			$("#codeColor").val('#'+params.color);
		}


		if ( params.ecc ) {
			$("#ecc").val(params.ecc);
			$('#ecc' ).trigger('change');
		}
		if ( params.format ) {
			$("#format").val(params.format);
		}
		if ( params.margin ) {


			$("#margin").val(params.margin);
		}


		if (params["charset-target"]) {
			$("#SelectCodding").val(params["charset-target"]);
			$('#SelectCodding' ).trigger('change');
		}



	}





    $('#codeData').keyup(function() {
        var text_length = $('#codeData').val().length;
        var text_remaining = text_max - text_length;

        $('#textarea_feedback').html(text_remaining + ' '+ trans.translate('qrcode.characters') );
    });

	$("#generate").click(function() {

	    var datainput = $("#codeData").val().replace(/\s/g, '+');
	    var size = $("#codeSize").val();
	    var encoding = $("#SelectCodding").val();
        var codecolor = $("#codeColor").val(function(i, v) {return v.replace("#","");}).val();
        var bgcolor = $("#bgColor").val(function(i, v) {return v.replace("#","");}).val();
        var ecc = $("#ecc").val();
        var format = $("#format").val();
        var margin = $("#margin").val(); //range 0-50 default: 1
        var q_zone = $("#q_zone").val(); //range: 0-100 default: 0
		var controlType = $('#formatOption' ).find(':selected' ).index();

		if (!controlType) {
			controlType = 0;
		}

	    if(datainput == "") {
	        showalert('Please enter a url or text or look at the Help data formats !','alert-danger');
	        $("#generate").prop("disabled", true);
	        return false;

	    } else {
	        var imgdata = "<img data-mce-placeholder=\"qrcode\" src='http://api.qrserver.com/v1/create-qr-code/?data=" + encodeURIComponent(datainput) + "&qzone=" + q_zone + "&margin=" + margin + "&color=" + codecolor + "&bgcolor=" + bgcolor + "&size=" + size + "&charset-target=" + encoding + "&ecc=" + ecc + "&format=" + format +"&controlType="+ controlType+"' data-name='" + datainput + "' />";
	        if( $("#image").is(':empty') ) {
	            $("#image").append(imgdata);
	            $("#arrow").show();
	            return false;
	        } else {
	            $("#image").html("");
	            $("#image").append(imgdata);
	            $("#arrow").show();
	            return false;
	        }
	    }

	});

	$("#image").click(function() {

        //alert($("#image").html());
        //return false;
	    I_InsertHTML($("#image").html());
	    I_Close();
	});

	function I_InsertHTML(sHTML) {
	    parent.tinymce.activeEditor.insertContent(sHTML);
	};

	function I_Close() {
	    parent.tinymce.activeEditor.windowManager.close();
	};

    /**
      Bootstrap Alerts -
      Function Name - showalert()
      Inputs - message,alerttype
      Example - showalert("Invalid Login","alert-error")
      Types of alerts -- "alert-error","alert-success","alert-info"
      Required - You only need to add a alert_placeholder div in your html page wherever you want to display these alerts "<div id="alert_placeholder"></div>"
    **/

    function showalert(message,alerttype) {

      $('#alert_placeholder').append('<div id="alertdiv" class="alert ' +  alerttype + '"><a class="close" data-dismiss="alert">x</a><span>'+message+'</span></div>');

	  $('#alert_placeholder').bind('closed.bs.alert', function () {
	  	$("#generate").prop("disabled", false);
	  });

		setTimeout(function() { // this will automatically close the alert and remove this if the users doesn't close it in 5 secs

			$("#alertdiv").remove();
			$("#generate").prop("disabled", false);

		}, 3000);

	};

	$('#formatOption').change(function(){
	    var selected_item = $(this).val();
	    $('#codeData').val(selected_item);
	});

    //colour pickers
    var _createColorpickers = function() {
        $('#codeColor').colorpicker({
            format: 'hex'
        });
        $('#bgColor').colorpicker({
            format: 'hex'
        });
    };
    _createColorpickers();

    $('.bscp-destroy').click(function(e) {
        e.preventDefault();
        $('.bscp').colorpicker('destroy');
    });

    $('.bscp-create').click(function(e) {
        e.preventDefault();
        _createColorpickers();
    });
    //hide the advanced/extra options:
    $(".hideparent").each( function() {
        var hidedata = $(this).data();
        if (hidedata.hide_initial === true) {
            $(hidedata.hide_ref).hide();
            }
    });
    $(".hideparent").click( function(e) {
        $($(this).data("hide_ref")).toggle();
    });
    //Sliders
    $('#margin').slider
    ({
      formater: function(value) {
        return value;
      }
    });
    $('#q_zone').slider
    ({
      formater: function(value) {
        return value;
      }
    });