
Desktop.Trash = {
    backgroundHeight: 640,
    playSound: function ()
    {
        Tools.html5Audio('html/audio/trash');
    },
    /**
     * 
     * @param {type} element
     * @param {type} e
     * @returns {undefined}
     */
    empty: function (element, e) {
        $('<div>').attr('id', 'poof').appendTo('body');

        // set the absolute postion of the poof animation <div>
        // uses e.pageX and e.pagY to get the cursor position
        // and offsets the poof animation from this point based on the xOffset and yOffset values set above
        $('#poof').css({
            backgroundPosition: '0 640',
            left: $(element).position().left,
            top: $(element).position().top - 2
        }); // display the poof <div>


        this.__animatePoof(element); // run the sprite animation
        this.playSound();
    },
    /**
     * 
     * @param {type} element
     * @returns {undefined}
     */
    __animatePoof: function __animatePoof (element) {
        var i, bgTop = 640; // initial background-position for the poof sprit is '0 0'
        var frames = 5; // number of frames in the sprite animation
        var frameSize = 128; // size of poof <div> in pixels (32 x 32 px in this example)
        var frameRate = 150; // set length of time each frame in the animation will display (in milliseconds)

        $('#poof').show();
        $(element).fadeOut((frameRate * frames) - 2);

        var poofOffsetTop = $(element).offset().top - 2;

        /*
         
         
         var poofOffsetTop = $(element).offset().top - 2;
         for (i = 1; i <= frames; i++)
         {
         if (i < frames) {
         $('#poof').stop().animate({
         backgroundPosition: "0 " + parseInt(bgTop - frameSize)
         }, frameRate);
         }
         else {
         $('#poof').stop().animate({
         backgroundPosition: "0 " + parseInt(bgTop - frameSize)
         }, frameRate, function() {
         $('#poof').remove();
         });
         }
         bgTop -= frameSize; // update bgPosition to reflect the new background-position of our poof <div>
         }
         
         
         return;
         */
        $('#poof').css({
            height: 128,
            width: 128,
            backgroundPosition: "0 " + bgTop
        }).animate({
            backgroundPosition: "0 "+ parseInt(bgTop - frameSize)
        }, frameRate, function () {
            bgTop -= frameSize; // update bgPosition to reflect the new background-position of our poof <div>
           // $('#poof').css({backgroundPosition: "0 0"}).remove();

            $('#poof').animate({
                
                backgroundPosition: "0 " + parseInt(bgTop - frameSize)
            }, frameRate, function () {
                bgTop -= frameSize; // update bgPosition to reflect the new background-position of our poof <div>
                $('#poof').animate({
                    backgroundPosition: "0 " + parseInt(bgTop - frameSize)
                }, frameRate, function () {
                    bgTop -= frameSize; // update bgPosition to reflect the new background-position of our poof <div>
                    $('#poof').animate({
                        opacity: '0',
                        backgroundPosition: "0 " + parseInt(bgTop - frameSize)
                    }, frameRate, function () {
                        $('#poof').remove();
                        /*
                        bgTop -= frameSize; // update bgPosition to reflect the new background-position of our poof <div>
                        $('#poof').animate({
                            backgroundPosition: "0 " + parseInt(bgTop - frameSize)
                        }, frameRate, function () {
                            $('#poof').remove();
                        });
                        */
                    });
                });
            });



        });






        /*
         
         
         
         for (i = 1; i <= frames; i++)
         {
         $('#poof').animate({
         backgroundPosition: "0 " + parseInt(bgTop - frameSize)
         }, frameRate, function() {
         bgTop -= frameSize; // update bgPosition to reflect the new background-position of our poof <div>
         });
         
         setTimeout();
         }
         
         setTimeout(function() {
         $('#poof').remove();
         }, (frames * frameRate) + 2);
         */
    }





}