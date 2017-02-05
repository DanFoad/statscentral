<?php

require_once("template/header.php");

// Import data
$championinfo = json_decode(file_get_contents("championinfo.json"), true);

$champion = $championinfo[array_rand($championinfo)];

?>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<h1>Contact Us</h1>

<div class="contact__area row">
    <form id="contact__form" action="/sendmessage.php" method="post">
        <div class="contact__input">
            <input id="contact__name" name="name" type="text" required>
            <span class="contact__input--title">Your Name</span>
            <span class="contact__input--valid" id="contact__name--valid"><i class="fa fa-check"></i></span>
        </div>
        <div class="contact__input">
            <input id="contact__email" name="email" type="email" required>
            <span class="contact__input--title">Email Address</span>
            <span class="contact__input--valid" id="contact__email--valid"><i class="fa fa-check"></i></span>
        </div>
        <div id="contact__message--container">
            <textarea name="message" id="contact__message" cols="30" rows="5" required></textarea>
            <span class="contact__input--title">Your Message</span>
        </div>
        
        <div class="g-recaptcha" data-sitekey="6Ld47R8TAAAAAFSp3aLCa9r5pVa7BK7Np8gZzlyU"></div>

        <button class="button__contact" name="contact" type="submit">
            <script type="text/template" id="circular-loading">
                <svg width="120px" height="120px">
                    <path class="outer-path" stroke="#fff" d="M 60 60 m 0 -50 a 50 50 0 1 1 0 100 a 50 50 0 1 1 0 -100"></path>
                    <path class="inner-path" stroke="rgba(255, 255, 255, 0.5)" d="M 60 60 m 0 -30 a 30 30 0 1 1 0 60 a 30 30 0 1 1 0 -60"></path>
                    <path class="success-path" stroke="#fff" d="M 60 10 A 50 50 0 0 1 91 21 L 75 45 L 55 75 L 45 65"></path>
                    <path class="error-path" stroke="#fff" d="M 60 10 A 50 50 0 0 1 95 25 L 45 75"></path>
                    <path class="error-path2" stroke="#fff" d="M 60 30 A 30 30 0 0 1 81 81 L 45 45"></path>
                </svg>
            </script>
            <span>Send</span>
        </button>
    </form>
    <div class="contact__splash" style="background-image:url('/img/champSplash/<?php echo $champion["key"]; ?>_Splash_Centered_0.jpg');"></div>
</div>

<script>
    $(".contact__input input, #contact__message").blur(function() {
        if ($(this).val().length > 0) {
            $(this).next().addClass("contact__input--title-entered");
        } else {
            $(this).next().removeClass("contact__input--title-entered");
        }
    });

    // Contact Form validation
    $("#contact__name").on("keydown cut paste input", function() {
        if ($("#contact__name").val().length > 0) {
            $("#contact__name--valid").show().html("<i class='fa fa-check'></i>").addClass("valid");
            if ($("#contact__name--valid").hasClass("invalid")) $("#contact__name--valid").removeClass("invalid");
        } else {
            if ($("#contact__name--valid").hasClass("valid")) $("#contact__name--valid").html("<i class='fa fa-times'></i>").removeClass("valid").addClass("invalid");
        }
    });

    $("#contact__email").on("keydown cut paste input", function() {
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        if (re.test($("#contact__email").val())) {
            $("#contact__email--valid").show().html("<i class='fa fa-check'></i>").addClass("valid");
            if ($("#contact__email--valid").hasClass("invalid")) $("#contact__email--valid").removeClass("invalid");
        } else {
            $("#contact__email--valid").show().html("<i class='fa fa-times'></i>").removeClass("valid").addClass("invalid");
        }
    });

    $("#contact__form").on("submit", function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr("action"),
            type: 'POST',
            data: $(this).serialize(),
            success: function(data) {
                $("#contact__response").html(data);
                if (data.substring(0, 5) == "Error") {
                    loading.triggerFail();
                } else {
                    loading.triggerSuccess();
                }
            },
            done: function(data) {
                //console.log(data);
            }
        });
    });

    /**** CONTACT BUTTON ANIMATION ****/
    /**
     * Developed by http://lmgonzalves.github.io/
     */

    function LoadingButton(el, options){
        this.el = el;
        this.options = options;
        this.init();
    }

    LoadingButton.prototype = {
        // Initialize everything
        init: function(){
            this.infinite = true;
            this.succeed = false;
            this.initDOM();
            this.initSegments();
            this.initEvents();
        },

        // Create an span element with inner text of the button and insert the corresponding SVG beside it
        initDOM: function(){
            this.el.innerHTML = '' + this.el.innerHTML + '';
            this.span = this.el.querySelector('span');
            var div = document.createElement('div');
            div.innerHTML = document.querySelector(this.options.svg).innerHTML;
            this.svg = div.querySelector('svg');
            this.el.appendChild(this.svg);
        },

        // Initialize the segments for all the paths of the loader itself, and for the success and error animations
        initSegments: function(){
            for(var i = 0, paths = this.options.paths, len = paths.length; i < len; i++){
                paths[i].el = this.svg.querySelector(paths[i].selector);
                paths[i].begin = paths[i].begin ? paths[i].begin : 0;
                paths[i].end = paths[i].end ? paths[i].end : 0.1;
                paths[i].segment = new Segment(paths[i].el, paths[i].begin, paths[i].end);
            }
            this.success = this.el.querySelector('.success-path');
            this.error = this.el.querySelector('.error-path');
            this.error2 = this.el.querySelector('.error-path2');
            this.successSegment = new Segment(this.success, 0, 0.1);
            this.errorSegment = new Segment(this.error, 0, 0.1);
            this.errorSegment2 = new Segment(this.error2, 0, 0.1);
        },

        // Initialize the click event in loading buttons, that trigger the animation
        initEvents: function(){
            var self = this;
            self.el.addEventListener('click', function(){
                $("#contact__form").submit();
                self.el.disabled = 'disabled';
                classie.add(self.el, 'open-loading');
                self.span.innerHTML = 'Sending';
                for(var i = 0, paths = self.options.paths, len = paths.length; i < len; i++){
                    paths[i].animation.call(self, paths[i].segment);
                }
            }, false);
        },

        // Make it fail
        triggerFail: function(){
            this.infinite = false;
            this.succeed = false;
        },

        // Make it succeed
        triggerSuccess: function(){
            this.infinite = false;
            this.succeed = true;
        },

        // When each animation cycle is completed, check whether any feedback has triggered and call the feedback
        // handler, otherwise it restarts again
        completed: function(reset){
            if(this.infinite){
                for(var i = 0, paths = this.options.paths, len = paths.length; i < len; i++){
                    if(reset){
                        paths[i].segment.draw(0, 0.1);
                    }
                    paths[i].animation.call(this, paths[i].segment);
                }
            }else{
                this.handleResponse();
            }
        },

        // Handle the feedback request, and perform the success or error animation
        handleResponse: function(){
            for(var i = 0, paths = this.options.paths, len = paths.length; i < len; i++){
                paths[i].el.style.visibility = 'hidden';
            }
            if(this.succeed){
                this.success.style.visibility = 'visible';
                this.successAnimation();
            }else{
                this.error.style.visibility = 'visible';
                this.error2.style.visibility = 'visible';
                this.errorAnimation();
            }
        },

        // Success animation
        successAnimation: function(){
            var self = this;
            self.successSegment.draw('100% - 50', '100%', 0.4, {callback: function(){
                self.span.innerHTML = 'Sent';
                classie.add(self.el, 'succeed');
                //setTimeout(function(){ self.reset(); }, 2000);
            }});
        },

        // Error animation
        errorAnimation: function(){
            var self = this;
            self.errorSegment.draw('100% - 42.5', '100%', 0.4);
            self.errorSegment2.draw('100% - 42.5', '100%', 0.4, {callback: function(){
                self.span.innerHTML = 'Failed';
                classie.add(self.el, 'failed');
                setTimeout(function(){ self.reset(); }, 2000);
            }});
        },

        // Reset the entire loading button to the initial state
        reset: function(){
            this.el.removeAttribute('disabled');
            classie.remove(this.el, 'open-loading');
            this.span.innerHTML = 'Send';
            classie.remove(this.el, 'succeed');
            classie.remove(this.el, 'failed');
            this.resetSegments();
            this.infinite = true;
            for(var i = 0, paths = this.options.paths, len = paths.length; i < len; i++){
                paths[i].el.style.visibility = 'visible';
            }
            this.success.style.visibility = 'hidden';
            this.error.style.visibility = 'hidden';
            this.error2.style.visibility = 'hidden';
        },

        // Reset the segments to the initial state
        resetSegments: function(){
            for(var i = 0, paths = this.options.paths, len = paths.length; i < len; i++){
                paths[i].segment.draw(paths[i].begin, paths[i].end);
            }
            this.successSegment.draw(0, 0.1);
            this.errorSegment.draw(0, 0.1);
            this.errorSegment2.draw(0, 0.1);
        }
    };

    function circularLoading(){
        var button = document.querySelector('.button__contact'),
            options = {
                svg: '#circular-loading',
                paths: [
                    {selector: '.outer-path', animation: outerAnimation},
                    {selector: '.inner-path', animation: innerAnimation}
                ]
            },
            loading = new LoadingButton(button, options);

        function outerAnimation(segment){
            var self = this;
            segment.draw('15%', '25%', 0.2, {callback: function(){
                segment.draw('75%', '150%', 0.3, {circular:true, callback: function(){
                    segment.draw('70%', '75%', 0.3, {circular:true, callback: function(){
                        segment.draw('100%', '100% + 0.1', 0.4, {circular:true, callback: function(){
                            self.completed(true);
                        }});
                    }});
                }});
            }});
        }

        function innerAnimation(segment){
            segment.draw('20%', '80%', 0.6, {callback: function(){
                segment.draw('100%', '100% + 0.1', 0.6, {circular:true});
            }});
        }

        return loading;
    }

    /**
     * segment - A little JavaScript class (without dependencies) to draw and animate SVG path strokes
     * @version v1.0
     * @link https://github.com/lmgonzalves/segment
     * @license MIT
     */

    (function(){
        var lastTime = 0;
        var vendors = ['ms', 'moz', 'webkit', 'o'];
        for(var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x){
            window.requestAnimationFrame = window[vendors[x]+'RequestAnimationFrame'];
            window.cancelAnimationFrame = window[vendors[x]+'CancelAnimationFrame']
                                       || window[vendors[x]+'CancelRequestAnimationFrame'];
        }

        if(!window.requestAnimationFrame)
            window.requestAnimationFrame = function(callback, element){
                var currTime = new Date().getTime();
                var timeToCall = Math.max(0, 16 - (currTime - lastTime));
                var id = window.setTimeout(function(){ callback(currTime + timeToCall); },
                  timeToCall);
                lastTime = currTime + timeToCall;
                return id;
            };

        if(!window.cancelAnimationFrame)
            window.cancelAnimationFrame = function(id){
                clearTimeout(id);
            };
    }());

    function Segment(path, begin, end){
        this.path = path;
        this.length = path.getTotalLength();
        this.path.style.strokeDashoffset = this.length * 2;
        this.begin = typeof begin !== 'undefined' ? this.valueOf(begin) : 0;
        this.end = typeof end !== 'undefined' ? this.valueOf(end) : this.length;
        this.circular = false;
        this.timer = null;
        this.draw(this.begin, this.end);
    }

    Segment.prototype = {
        draw : function(begin, end, duration, options){
            if(duration){
                var delay = options && options.hasOwnProperty('delay') ? parseFloat(options.delay) * 1000 : 0,
                    easing = options && options.hasOwnProperty('easing') ? options.easing : null,
                    callback = options && options.hasOwnProperty('callback') ? options.callback : null,
                    that = this;

                this.circular = options && options.hasOwnProperty('circular') ? options.circular : false;

                this.stop();
                if(delay){
                    delete options.delay;
                    this.timer = setTimeout(function(){
                        that.draw(begin, end, duration, options);
                    }, delay);
                    return this.timer;
                }

                var startTime = new Date(),
                    initBegin = this.begin,
                    initEnd = this.end,
                    finalBegin = this.valueOf(begin),
                    finalEnd = this.valueOf(end);

                (function calc(){
                    var now = new Date(),
                        elapsed = (now-startTime)/1000,
                        time = (elapsed/parseFloat(duration)),
                        t = time;

                    if(typeof easing === 'function'){
                        t = easing(t);
                    }

                    if(time > 1){
                        t = 1;
                    }else{
                        that.timer = window.requestAnimationFrame(calc);
                    }

                    that.begin = initBegin + (finalBegin - initBegin) * t;
                    that.end = initEnd + (finalEnd - initEnd) * t;

                    that.begin = that.begin < 0 && !that.circular ? 0 : that.begin;
                    that.begin = that.begin > that.length && !that.circular ? that.length : that.begin;
                    that.end = that.end < 0 && !that.circular ? 0 : that.end;
                    that.end = that.end > that.length && !that.circular ? that.length : that.end;

                    if(that.end - that.begin < that.length && that.end - that.begin > 0){
                        that.draw(that.begin, that.end);
                    }else{
                        if(that.circular && that.end - that.begin > that.length){
                            that.draw(0, that.length);
                        }else{
                            that.draw(that.begin + (that.end - that.begin), that.end - (that.end - that.begin));
                        }
                    }

                    if(time > 1 && typeof callback === 'function'){
                        return callback.call(that);
                    }
                })();
            }else{
                this.path.style.strokeDasharray = this.strokeDasharray(begin, end);
            }
        },

        strokeDasharray : function(begin, end){
            this.begin = this.valueOf(begin);
            this.end = this.valueOf(end);
            if(this.circular){
                var division = this.begin > this.end || (this.begin < 0 && this.begin < this.length * -1)
                    ? parseInt(this.begin / parseInt(this.length)) : parseInt(this.end / parseInt(this.length));
                if(division !== 0){
                    this.begin = this.begin - this.length * division;
                    this.end = this.end - this.length * division;
                }
            }
            if(this.end > this.length){
                var plus = this.end - this.length;
                return [this.length, this.length, plus, this.begin - plus, this.end - this.begin].join(' ');
            }
            if(this.begin < 0){
                var minus = this.length + this.begin;
                if(this.end < 0){
                    return [this.length, this.length + this.begin, this.end - this.begin, minus - this.end, this.end - this.begin, this.length].join(' ');
                }else{
                    return [this.length, this.length + this.begin, this.end - this.begin, minus - this.end, this.length].join(' ');
                }
            }
            return [this.length, this.length + this.begin, this.end - this.begin].join(' ');
        },

        valueOf: function(input){
            var val = parseFloat(input);
            if(typeof input === 'string' || input instanceof String){
                if(~input.indexOf('%')){
                    var arr;
                    if(~input.indexOf('+')){
                        arr = input.split('+');
                        val = this.percent(arr[0]) + parseFloat(arr[1]);
                    }else if(~input.indexOf('-')){
                        arr = input.split('-');
                        val = arr[0] ? this.percent(arr[0]) - parseFloat(arr[1]) : -this.percent(arr[1]);
                    }else{
                        val = this.percent(input);
                    }
                }
            }
            return val;
        },

        stop : function(){
            window.cancelAnimationFrame(this.timer);
            this.timer = null;
        },

        percent : function(value){
            return parseFloat(value) / 100 * this.length;
        }
    };

    /*!
     * classie - class helper functions
     * from bonzo https://github.com/ded/bonzo
     *
     * classie.has( elem, 'my-class' ) -> true/false
     * classie.add( elem, 'my-new-class' )
     * classie.remove( elem, 'my-unwanted-class' )
     * classie.toggle( elem, 'my-class' )
     */

    /*jshint browser: true, strict: true, undef: true */
    /*global define: false */

    ( function( window ) {

    'use strict';

    // class helper functions from bonzo https://github.com/ded/bonzo

    function classReg( className ) {
      return new RegExp("(^|\\s+)" + className + "(\\s+|$)");
    }

    // classList support for class management
    // altho to be fair, the api sucks because it won't accept multiple classes at once
    var hasClass, addClass, removeClass;

    if ( 'classList' in document.documentElement ) {
      hasClass = function( elem, c ) {
        return elem.classList.contains( c );
      };
      addClass = function( elem, c ) {
        elem.classList.add( c );
      };
      removeClass = function( elem, c ) {
        elem.classList.remove( c );
      };
    }
    else {
      hasClass = function( elem, c ) {
        return classReg( c ).test( elem.className );
      };
      addClass = function( elem, c ) {
        if ( !hasClass( elem, c ) ) {
          elem.className = elem.className + ' ' + c;
        }
      };
      removeClass = function( elem, c ) {
        elem.className = elem.className.replace( classReg( c ), ' ' );
      };
    }

    function toggleClass( elem, c ) {
      var fn = hasClass( elem, c ) ? removeClass : addClass;
      fn( elem, c );
    }

    var classie = {
      // full names
      hasClass: hasClass,
      addClass: addClass,
      removeClass: removeClass,
      toggleClass: toggleClass,
      // short names
      has: hasClass,
      add: addClass,
      remove: removeClass,
      toggle: toggleClass
    };

    // transport
    if ( typeof define === 'function' && define.amd ) {
      // AMD
      define( classie );
    } else {
      // browser global
      window.classie = classie;
    }

    })( window );

    var loading = circularLoading();
</script>

<?php require_once("template/footer.php");