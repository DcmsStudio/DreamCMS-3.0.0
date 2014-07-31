/**
 * jQuery function to populate a jQuery collection with form fields.
 */
$.fn.addFields = function(fields) {
    postProcessors = [];
    for(var i in fields) {
        field = fields[i];
        formItem = getFormItem();
        switch (field.type) {
            case 'text' :
                formItem.append(getFormItemLabel(field.label, field.id));
                formItem.append(getFormImages(field.tip));
                itemWrapper = getFormItemWrapper();
                itemWrapper.append(getTextField(field));
                formItem.append(itemWrapper);
            break;
            case 'password' :
                formItem.append(getFormItemLabel(field.label, field.id));
                formItem.append(getFormImages(field.tip));
                itemWrapper = getFormItemWrapper();
                itemWrapper.append(getPasswordField(field));
                formItem.append(itemWrapper);
            break;
            case 'textarea' :
                formItem.append(getFormItemLabel(field.label, field.id));
                formItem.append(getFormImages(field.tip));
                itemWrapper = getFormItemWrapper();
                textarea = getTextareaField(field);
                itemWrapper.append(textarea);
                if(field.controls) {
                   // textarea.addToolbar();
                }
                formItem.append(itemWrapper);
            break;
            case 'richtext' :
                formItem.append(getFormItemLabel(field.label, field.id));
                formItem.append(getFormImages(field.tip));
                itemWrapper = getFormItemWrapper();
                textarea = getTextareaField(field);
                itemWrapper.append(textarea);
                if(field.controls) {
                    //textarea.addToolbar();
                }
                formItem.append(itemWrapper);
                postProcessors.push(function() { 
                    CKEDITOR.replace(field.id, {
                        toolbar : [
                                    ['Cut','Copy','Paste','PasteText','PasteFromWord','-'],
                                    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
                                    ['Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
                                    '/',
                                    ['Styles','Format'],
                                    ['Bold','Italic','Strike'],
                                    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
                                    ['Link','Unlink','Anchor'],
                                    ['Maximize','-','Source','-','About']
                                ], 
                        uiColor: '#ececec'
                    }); 
                });
            break;
			
            case 'mediaimage' :
                formItem.append(getFormItemLabel(field.label, field.id));
                formItem.append(getFormImages(field.tip));
                itemWrapper = getFormItemWrapper();
                itemWrapper.append(getTextField(field));

				var fieldID = field.id;
				var button = getFormButtonWrapper();
				button.append('Media Browser').click(function(e) {
					openImageBrowser('/mediacenter/images', $('#'+ fieldID) , true);
					e.preventDefault();
					return false;
				});
				
				itemWrapper.append( button );
                formItem.append(itemWrapper);
            break;
			
			
			
            case 'checkbox' :
                formItem.append(getFormItemSpanLabel(field.spanlabel));
                formItem.append(getFormImages(field.tip));
                itemWrapper = getFormItemCheckboxWrapper();
                itemWrapper.append(getCheckboxField(field));
                formItem.append(itemWrapper);
            break;
            case 'multiplecheckbox' :
                formItem.append(getFormItemSpanLabel(field.spanlabel));
                formItem.append(getFormImages(field.tip));
                itemWrapper = getFormItemCheckboxWrapper();
                for(var opt in field.parsed_options) {
                    opt_item = field.parsed_options[opt];
                    itemWrapper.append(getMultipleCheckboxField(field.name + '[' + opt_item.value + ']', field.id + '_' + opt_item.value, opt_item.value, opt_item.checked, opt_item.label));
                    itemWrapper.append($('<br>'));
                }
                formItem.append(itemWrapper);
            break;
            case 'radio' :
                formItem.append(getFormItemSpanLabel(field.spanlabel));
                formItem.append(getFormImages(field.tip));
                itemWrapper = getFormItemCheckboxWrapper();
                for(var opt in field.parsed_options) {
                    opt_item = field.parsed_options[opt];
                    itemWrapper.append(getRadioField(field.name, field.id + '_' + opt_item.value, opt_item.value, opt_item.checked, opt_item.label));
                    itemWrapper.append($('<br>'));
                }
                formItem.append(itemWrapper);
            break;
            case 'select' :
                formItem.append(getFormItemLabel(field.label, field.id));
                formItem.append(getFormImages(field.tip));
                itemWrapper = getFormItemWrapper();
                selectField = getSelectField(field.name, field.id);
                for(var opt in field.parsed_options) {
                    opt_item = field.parsed_options[opt];
                    selectField.append(getSelectFieldOption(opt_item.value, opt_item.checked, opt_item.label));
                }
                itemWrapper.append(selectField);
                formItem.append(itemWrapper);
            break;
            case 'datetime' :
                formItem.append(getFormItemSpanLabel(field.label));
                formItem.append(getFormImages(field.tip));
                itemWrapper = getFormItemWrapper();
                date_field = getDateTimeField(field);
                itemWrapper.append(date_field);
                formItem.append(itemWrapper);
            break;
            default :
                jAlert('Field of type `' + field.type + '` has not yet been implemented, field not rendered.');
        }
        $(this).append(formItem);
    }
    
    // (re-)bind tooltip handler
    $(this).find('.tooltip-icon').unbind('click').bind('click', function() {
        currentTooltip = $(this).attr('id');
        displayTooltip(currentTooltip);
    });
    
    for(i=0; i<postProcessors.length; i++) {
        postProcessors[i]();
    }
    
    return this;
};
/**
 * Create a top-level form item division.
 * @return jQuery collection containing the a form-item div
 */
function getFormItem() {
    return $('<fieldset>');
}

/**
 * Creates a form item label.
 * @param label The text to display as label.
 * @param field The field to associate with the label (using it's 'for' attribute).
 * @return jQuery collection containing a label element.
 */
function getFormItemLabel(label, field) {
    return $('<legend>').append(label);
}

/**
 * Create a 'fake' form item label (it's actually a span).
 * @param label Text to display in the label.
 * @return jQuery collection containing a span.
 */
function getFormItemSpanLabel(label) {
    label = typeof label != 'undefined' ? label : ' ' ;
    return $('<legend>').append(label);
}

/**
 * Create a division for the form-item images. Optionally contains a tooltip icon, if a tip is provided. 
 * @param tip ID for a tooltip.
 * @return jQuery collection containing a division with a tooltip image in it.
 */
function getFormImages(tip) {
    if(typeof tip != 'undefined') {
        tticon = $('<img>').attr({id: tip, width: 16, height: 16, src: 'html/style/default/img/info.png'}).addClass('infoicon');
    } else {
		return $('<span>');
        tticon = $('<img>').attr({width: 16, height: 16, src: settings.base_url + 'asset/img/blank.png'}).addClass('infoicon');
    }
    return $('<span>').append(tticon);
}

/**
 * Creates a wrapper for the actual form field elements.
 * @return jQuery collection containing a division.
 */
function getFormItemWrapper() {
    return $('<div>').addClass('form-wrapper');
}

/**
 * Creates a wrapper for the actual form field elements, specifically geared towards checkboxes and radio buttons.
 * @return jQuery collection containing a division.
 */
function getFormItemCheckboxWrapper() {
    return $('<div>').addClass('checkbox-set');
}




/**

 * @return jQuery collection containing a division.
 */
function getFormButtonWrapper() {
    return $('<button>').addClass('action-button');
}





/**
 * Creates a text field element.
 * @param field The field definition.
 * @return jQuery collection containing the field element.
 */
function getTextField(field) {
    field_value = typeof field.value!='undefined' ? field.value : '' ;
    el = $('<input>').addClass('textbox').attr({type: 'text', name: field.name, id: field.id}).val(field_value);
    if(field.size) {
        el.attr('size', field.size);
    }
    if(field.maxlength) {
        el.attr('maxlength', field.maxlength);
    }
    if(field.style) {
        el.css(parseFieldStyle(field.style));
    }
    if(field['class']) {
        el.addClass(field['class']);
    }
    return el;
}

/**
 * Creates a password field element.
 * @param field The field definition.
 * @return jQuery collection containing the field element.
 */
function getPasswordField(field) {
    field_value = typeof field.value!='undefined' ? field.value : '' ;
    el = $('<input>').addClass('textbox').attr({type: 'password', name: field.name, id: field.id}).val(field_value);
    if(field.size) {
        el.attr('size', field.size);
    }
    if(field.maxlength) {
        el.attr('maxlength', field.maxlength);
    }
    if(field.style) {
        el.css(parseFieldStyle(field.style));
    }
    if(field['class']) {
        el.addClass(field['class']);
    }
    return el;
}

/**
 * Creates a textarea field element.
 * @param field The field definition.
 * @return jQuery collection containing the new textarea
 */
function getTextareaField(field) {
    field_value = typeof field.value!='undefined' ? field.value : '' ;
    el = $('<textarea>').addClass('textbox').attr({cols: 40, rows: 10, name: field.name, id: field.id}).css({width: '100%', height: 150}).append(field_value);
    if(field.rows) {
        el.attr('cols', field.cols);
    }
    if(field.rows) {
        el.attr('rows', field.rows);
    }
    if(field.style) {
        el.css(parseFieldStyle(field.style));
    }
    if(field['class']) {
        el.addClass(field['class']);
    }
    return el;
}

/**
 * Create a checkbox field. 
 * @param field The field definition.
 * @return jQuery collection containing a checkbox in a label.
 */
function getCheckboxField(field) {
    checkbox = $('<input>').addClass('checkbox').attr({type: 'checkbox', name: field.name, id: field.id, checked: field.checked}).val(field_value);
    label = $('<label>').attr({'for': field.id}).append(checkbox).append(field.label);
    return label;
}

/**
 * Create a checkbox field that is one of a set of multiple checkboxes 
 * @param field_name The name of the checkbox.
 * @param field_id The id of the checkbox.
 * @param field_value The value of the checkbox.
 * @param field_checked Boolean to indicate if the checkbox is checked.
 * @param field_label The label to append the checkbox to.
 * @return jQuery collection containing a checkbox in a label.
 */
function getMultipleCheckboxField(field_name, field_id, field_value, field_checked, field_label) {
    checkbox = $('<input>').addClass('checkbox').attr({type: 'checkbox', name: field_name, id: field_id, checked: field_checked}).val(field_value);
    label = $('<label>').attr({'for': field_id}).append(checkbox).append(field_label);
    return label;
}

/**
 * Create a radio field. 
 * @param field_name The name of the field.
 * @param field_id The ID of the field
 * @param field_value The value of the field.
 * @param field_checked Boolean to indicate if the field should be checked.
 * @param field_label The text for the label to append the radio to.
 * @return jQuery collection containing a radio button in a label.
 */
function getRadioField(field_name, field_id, field_value, field_checked, field_label) {
    checkbox = $('<input>').addClass('checkbox').attr({type: 'radio', name: field_name, id: field_id, checked: field_checked}).val(field_value);
    label = $('<label>').attr({'for': field_id}).append(checkbox).append(field_label);
    return label;
}

/**
 * Creates a select field.
 * @param field_name The name of the select.
 * @param field_id The ID of the select.
 * @return jQuery collection containing a select element.
 */
function getSelectField(field_name, field_id) {
    return $('<select>').addClass('droplist').attr({name: field_name, id: field_id});
}

/**
 * Creates an option element to append to a select field.
 * @param option_value The value of the option.
 * @param option_selected Boolean to indicate if the option should be selected.
 * @param option_label Text to append to the option.
 * @return jQuery collection containing an option element.
 */
function getSelectFieldOption(option_value, option_selected, option_label) {
    return $('<option>').attr({value: option_value, selected: option_selected}).text(option_label);
}

/**
 * Creates a date time selector.
 * @param field Field definition for the date time field.
 * @return JQuery collection containing a date time element. 
 */
function getDateTimeField(field) {
    date = {};
    if(field.value && field.value != '') {
        date = parseDate(field.value);
    }
    container = $('<div>');
    
    now = new Date();
        
    day = $('<select>').addClass('droplist date-day').attr({id: field.id + '_day', name: field.id + '[day]'}).css({width: 50});
    day.append(getSelectFieldOption('-', false, ' '));
    for(i=1; i<32; i++) {
        selected = date.day==i ? true : false ;
        day.append(getSelectFieldOption(zeropad(i, 2), selected, zeropad(i, 2)));
    }
    container.append(day);
    container.append(' - ');
    
    month = $('<select>').addClass('droplist date-month').attr({id: field.id + '_month', name: field.id + '[month]'}).css({width: 50});
    month.append(getSelectFieldOption('-', selected, ' '));
    for(i=1; i<13; i++) {
        selected = date.month==i ? true : false ;
        month.append(getSelectFieldOption(zeropad(i, 2), selected, zeropad(i, 2)));
    }
    container.append(month);
    container.append(' - ');
    
    year = $('<select>').addClass('droplist date-year').attr({id: field.id + '_year', name: field.id + '[year]'}).css({width: 60});
    year.append(getSelectFieldOption('-', false, ' '));
    for(i=now.getFullYear()-10; i<now.getFullYear()+21; i++) {
        selected = date.year==i ? true : false ;
        year.append(getSelectFieldOption(i, selected, i));
    }
    container.append(year);
    container.append('  ');
    
    if(field.time) {
        hour = getTextField({id: field.id + '_hour', name: field.id + '[hour]', style: 'width: 24px; text-align: center;'}).addClass('date-hour');
        if(date.hour) hour.val(date.hour);
        container.append(hour);
        container.append(' : ');
        
        minute = getTextField({id: field.id + '_minute', name: field.id + '[minute]', style: 'width: 24px; text-align: center;'}).addClass('date-minute');
        if(date.minute) minute.val(date.minute);
        container.append(minute);
        container.append(' : ');
    
        second = getTextField({id: field.id + '_second', name: field.id + '[second]', style: 'width: 24px; text-align: center;'}).addClass('date-second');
        if(date.second) second.val(date.second);
        container.append(second);
        container.append('  ');
    }
    
    input = $('<input>').attr({type: 'hidden', name: field.id, id: field.id}).addClass('date-full').val(field.value);
    container.append(input);
    
    
    container.registerDateField();
    
    if(field.controls) {
        container.addDateControls();
    }
    
    return container;
}

/**
 * Parses a style string and returns the styles as an object.
 * @param style String containing style declarations
 * @return Object containing the style declarations.
 */
function parseFieldStyle(style) {
    styles = {};
    raw = style.split(';');
    for(var i in raw) {
        dec = raw[i].split(':');
        if(dec.length==2) {
            selector = dec[0].toString().replace(/^\s\s*/, '').replace(/\s\s*$/, '');
            value = dec[1].toString().replace(/^\s\s*/, '').replace(/\s\s*$/, '');
            styles[selector] = value;
        }
    }
    return styles;
}

/**
 * Parses a string representation of a string into an object. 
 * @param dateString A string representation of a date (YYYY:MM:DD HH:MM:SS).
 * @return Object containing the passed date.
 */
function parseDate(dateString) {
    try {
        dateStringParts = dateString.split(' ');
        dateParts = dateStringParts[0].split('-');
        timeParts = dateStringParts[1].split(':');
        
        date = {};
        date.year = dateParts[0];
        date.month = dateParts[1];
        date.day = dateParts[2];
        
        date.hour = timeParts[0];
        date.minute = timeParts[1];
        date.second = timeParts[2];

        return date;
    } catch(e) {
        return {};
    }
}

/**
 * Zero pad a string.
 * @param n Number to zeropad
 * @param digits Length of eventual string
 * @return Zero padded string.
 */
function zeropad(n, digits) {
    n = n.toString();
    while (n.length < digits) {
        n = '0' + n;
    }
    return n;
}
