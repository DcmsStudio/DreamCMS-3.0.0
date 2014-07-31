var Uploader = function () {



    function gethash (s) {
        var char, hash, i, len, test, _i;
        hash = 0;
        len = s.length;
        if (len === 0) {
            return hash;
        }
        for (i = _i = 0; 0 <= len ? _i <= len : _i >= len; i = 0 <= len ? ++_i : --_i) {
            char = s.charCodeAt(i);
            test = ((hash << 5) - hash) + char;
            if (!isNaN(test)) {
                hash = test & test;
            }
        }
        return 'file-' + Math.abs(hash);
    }



    var fileAddTemplate = '<div class="upload-file">'
            + '<div class="progressbar"><div class="bar" style="width:0%;"></div></div>'
            + '<div class="file-info">'
            + '<span class="filename">{filename}</span>'
            + '<span class="filesize">{filesize}</span>'
            + '<span class="filespeed">{filespeed}</span>'
            + '</div><span class="cancel"></span>'
            + '</div>';



    this.opts = {
        url: '',
        filePostParamName: 'Filedata',
        postParams: false,
        control: null, // is the Drop container with the "Browse" Button
        fileAddContainer: null, // is the added Files Container 
        file_mask: '*.*',
        file_queue_limit: 1,
        max_upload_files: 1,
        max_upload_filesize: 1024
    };

    this.fileMaskRegex = null;
    this.numFiles = 0;
    this.exts = [];





    this.init = function (opts) {
        this.opts.url = opts.url || this.opts.url;
        this.opts.postParams = opts.postParams || this.opts.postParams;
        this.opts.filePostParamName = opts.filePostParamName || this.opts.filePostParamName;
        this.opts.control = opts.control || this.opts.control;
        this.opts.file_mask = opts.file_mask || this.opts.file_mask;
        this.opts.file_queue_limit = opts.file_queue_limit || this.opts.file_queue_limit;
        this.opts.max_upload_files = opts.max_upload_files || this.opts.max_upload_files;
        this.opts.max_upload_filesize = opts.max_upload_filesize || this.opts.max_upload_filesize;

        this.numFiles = 0;

        this.prepareFileMaskExts();

        var postparams = {};
        if (this.opts.postParams)
        {
            postparams = $.extend({}, postparams, this.opts.postParams);
        }

        this.opts.control.filedrop({
            // The name of the $_FILES entry:
            fallbackInput: fallbackInput,
            autoUpload: false,
            queuewait: 10,
            refresh: 500,
            paramname: (this.opts.filePostParamName ? this.opts.filePostParamName : 'Filedata'),
            queuefiles: this.opts.file_queue_limit,
            maxfiles: this.opts.max_upload_files,
            maxfilesize: this.opts.max_upload_filesize,
            url: this.opts.url,
            data: postparams,
        });


    };

    this.destroyUpload = function () {
        this.opts = {
            url: '',
            control: null,
            file_type_mask: '*.*',
            file_queue_limit: 1,
            max_upload_files: 1,
            max_upload_filesize: 1024
        };
        this.fileMaskRegex = null;
        this.exts = [];
        this.numFiles = 0;
    };


    this.prepareFileMaskExts = function () {

        if (typeof opts.file_mask == 'string' && opts.file_mask !== '*.*')
        {
            var masks = opts.file_mask.split(','), fileMaskRegex = '';
            for (var i = 0; i < masks.length; ++i)
            {
                if (masks[i] !== '' && masks[i].match(/^\*\.([a-z0-9]+)$/gi) && masks[i] !== '*.*')
                {
                    this.exts.push(masks[i].replace('*.', ''));
                }
            }

            if (this.exts.length) {
                this.fileMaskRegex = new RegExp('.*\\.(' + this.exts.join('|') + ')$', 'i');
            }
            else
            {
                this.fileMaskRegex = new RegExp('.*$', 'i');
            }

        }
        else if (typeof opts.file_mask == 'string' && opts.file_mask === '*.*') {
            this.fileMaskRegex = new RegExp('.*$', 'i');
        }
    };


    this.isValid = function (file) {

        if (file.size > Tools.unformatSize(this.opts.max_upload_filesize))
        {
            Notifier.warn('Die Datei %f ist größer als %s.<br/>Die maximale Dateigröße beträgt %s!'.replace('%s', Tools.formatSize(Tools.unformatSize(this.opts.max_upload_filesize))).replace('%f', file.name));
            return false;
        }

        if (typeof opts.file_mask == 'string' && opts.file_mask === '*.*') {
            return true;
        }
        else {
            var currentMime = file.type;

            if (currentMime.match(/^image\//))
            {
                file.isImage = true;
            }
            else
            {
                file.isImage = false;
            }

            if (this.fileMaskRegex)
            {
                if (!file.name.match(this.fileMaskRegex))
                {
                    return false;
                }

                return true;
            }

            var regex = '';

            for (var i = 0; i < this.exts.length; ++i)
            {
                if (this.exts[i].length)
                {
                    if (types[i] != '*.*' && types[i])
                    {
                        var val = Tools.getMime(this.exts[i]);

                        if (val != false)
                        {
                            if (Tools.isObject(val))
                            {
                                regex += val.join('|');
                            }
                            else if (Tools.isString(val))
                            {
                                regex += (regex != '' ? '|' + val : val);
                            }
                        }
                    }
                }
            }

            if (regex !== '')
            {
                regex = regex.replace('/', '\/');
                regex = regex.replace('.', '\.');

                var reg = new RegExp('(' + regex + ')', 'i');
                if (!reg.test(currentMime))
                {
                    Notifier.warn('This Filetype is not allowed! Only Filetype: ' + this.exts.join(', '));
                    return false;
                }
            }

            return true;
        }
    };


    this.createPreview = function (file, len) {

        if (this.opts.max_upload_files === 1)
        {
            $('.dragAndDropUploadZone.preview', $('#' + Win.windowID)).remove();
        }

        var id = gethash(file.name);

        var uploadc = $.data(file, 'uploaddata');
        var preview = uploadc, image = $('img', preview);
        var reader = new FileReader();

        preview.find('.item-errormessage').empty().hide();

        if (file.isImage && this.opts.type != 'gal')
        {
            reader.onload = function (e) {
                // e.target.result holds the DataURL which
                // can be used as a source of the image:
                image.attr('src', e.target.result);
                image.attr('width', 100).height(100);
            };
            preview.find('img').hide();
        }
        else
        {
            preview.find('img').remove();
        }

        // Reading the file as a DataURL. When finished,
        // this will trigger the onload function above:
        reader.readAsDataURL(file);

        message.hide();

        var _after = message;

        if (message.parent().find('.upload-file').length)
        {
            _after = message.parent().find('.upload-file:last');
        }

        preview.insertAfter(_after);
    }

    this.onAdd = function (file) {
        var self = this;

        if (this.isValid(file))
        {
            this.numFiles++;
            var id = gethash(file.name);

            fileAddTemplate = fileAddTemplate.replace('{filename}', file.name);
            fileAddTemplate = fileAddTemplate.replace('{filesize}', Tools.formatSize(file.size));
            fileAddTemplate = fileAddTemplate.replace('{filespeed}', '');

            if (this.fileAddContainer.is('ul'))
            {
                var item = $(fileAddTemplate);
                var li = $('<li>').addClass('upload-item').append(item);
                li.attr('id', id);

                li.appendTo(this.fileAddContainer);
            }
            else {
                var item = $(fileAddTemplate);
                item.find('div:first').addClass('upload-item').attr('id', id);

                this.fileAddContainer.append(item);
            }


            item.find('span.cancel').click(function (e) {

                if (self.control.trigger('cancel', $(e.target).parents('.upload-item:first').data()) === true) {
                    var itm = $(this).parents('.upload-item:first');
                    var fname = itm.find('.filename').text();

                    itm.addClass('abort').find('.progressbar,.filespeed,.filesize').hide();
                    itm.find('.filename').text('Upload der Datei `%s` abgebrochen.'.replace('%s', fname));

                    self.numFiles--;

                    setTimeout(function () {
                        itm.fadeOut(400, function () {
                            $(this).remove();
                        });
                    }, 1500);
                }
            });


            if (typeof this.opts.onAdd === 'function')
            {
                this.opts.onAdd(file);
            }

        }
    }

    this.uploadStarted = function (file, hash) {

    };

    this.progressUpdated = function (index, file, currentProgress) {

    };

    this.speedUpdated = function (index, file, speed, loaded, diffTime) {

    }

    this.onUploadFinished = function (index, file, response, timeDiff, xhr) {

    };

    this.uploadAbort = function (e, xhr, file) {

    };

    this.onCancel = function (file) {

    };
    this.onError = function (err, file) {

    };

};