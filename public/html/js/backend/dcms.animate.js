/**
 * Animation timeline, with a callback.
 */
function AnimationTimeline(params, onstep)
{
    // Copy values.

    // Required:
    this.from = params.from || 0;         // e.g. 0%
    this.to = params.to || 1;         // e.g. 100%
    this.onstep = onstep || params.onstep;  // pass the callback.

    // Optional
    this.steps = params.steps || 10;
    this.duration = params.duration || 300;
    this.easing = params.easing || "linear";

    // Internal
    this._diff = 0;
    this._step = 1;
    this._timer = 0;
}

jQuery.extend(AnimationTimeline.prototype, {
    start: function()
    {
        if (this.from == this.to)
            return;

        if (this._timer > 0)
        {
            self.console && console.error("DOUBLE START!");
            return;
        }

        var myself = this;
        this._diff = (this.to - this.from);
        this._timer = setInterval(function() {
            myself.doStep()
        }, this.duration / this.steps);
    }

    , stop: function()
    {
        clearInterval(this._timer);
        this._timer = -1;
        this._queue = [];
    }

    , doStep: function()
    {
        // jQuery version of: stepValue = from + diff * percentage;
        var percentage = (this._step / this.steps);
        var stepValue = jQuery.easing[ this.easing ](percentage, 0, this.from, this._diff);

        // Next step
        var props = {animationId: this._timer + 10
                    , percentage: percentage
                    , from: this.from, to: this.to
                    , step: this._step, steps: this.steps
        };
        if (++this._step > this.steps)
        {
            stepValue = this.to;  // avoid rounding errors.
            this.stop();
        }

        // Callback
        if (this.onstep(stepValue, props) === false) {
            this.stop();
        }
    }
});
/*
 * 
 var el1 = $("#element1");
 var el2 = $("#element2");
 
 var animation = new AnimationTimeline({
 easing: "swing", onstep: function(stepValue, animprops)
 {
 // This is called for every animation frame. Set the elements:
 el1.css({ left: '...' , top: '...' });
 el2.css({ left: '...' , top: '...' });
 }
 });
 
 // And start it.
 animation.start();
 
 */


var Anim = {
    prepareAnimationOptions: function(options, duration, reverse, complete)
    {
        if (typeof options === 'string') {
            // options is the list of effect names separated by space e.g. animate(element, "fadeIn slideDown")

            // only callback is provided e.g. animate(element, options, function() {});
            if ($.isFunction(duration)) {
                complete = duration;
                duration = 400;
                reverse = false;
            }

            if ($.isFunction(reverse)) {
                complete = reverse;
                reverse = false;
            }

            if (typeof duration === 'boolean') {
                reverse = duration;
                duration = 400;
            }

            options = {
                effects: options,
                duration: duration,
                reverse: reverse,
                complete: complete
            };
        }


        return extend({
            //default options
            effects: {},
            duration: 400, //jQuery default duration
            reverse: false,
            hide: false
        }, options, {complete: options.complete}); // Move external complete callback, so deferred.resolve can be always executed.

    },
    dequeue: function(element, options)
    {
        if ($.isFunction(options.completeCallback)) {
            options.completeCallback(element); // call the external complete callback with the element
        }

        element.dequeue();
        element.stop(true, true);
    },
    animate: function(element, cssSettings, options)
    {



        var instance = $(element);
        var cssOpts = $.extend({}, cssSettings);

       // options.queue = false;
        options.easing = options.easing || "swing";
        options.duration = options.duration || 300;
        instance.animate(cssOpts, options);
        return element;
        

        instance.queue(function() {

            options.queue = false;

            if ($.isFunction(options.complete))
            {
                options.completeCallback = options.complete;
                options.complete = function() {
                    Anim.dequeue(instance, options);
                }
            }
            else
            {
                options.complete = function() {
                    Anim.dequeue(instance, options);
                }
            }
            
            instance.animate(cssOpts, options);
        });


        return element;
    }



};