/**
 * @package Jms Drop Megamenu
 * @version 1.0
 * @Copyright (C) 2009 - 2013 Joommasters.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @Website: http://www.joommasters.com
**/
(function() {
  var $;

  $ = typeof jQuery !== "undefined" && jQuery !== null ? jQuery : Zepto;

  $.offCanvasMenu = function(options) {
    var actions, backfaceCss, baseCSS, body, container, cssSupport, head, inner, innerWrapper, menu, menuLeft, outer, outerWrapper, settings, transEndEventName, transformPosition, transformPrefix, trigger;
    settings = {
      direction: "left",
      coverage: "70%",
      menu: "#jmsmenuwrap",
      trigger: "#jmsmenuwrap-trigger",
      duration: 250,
      use3D: (typeof Modernizr !== "undefined" && Modernizr !== null) && Modernizr.csstransforms3d,
      container: 'body',
      classes: {
        inner: 'inner-wrapper',
        outer: 'outer-wrapper',
        container: 'off-canvas-menu',
        open: 'menu-open'
      },
      transEndEventNames: {
        'WebkitTransition': 'webkitTransitionEnd',
        'MozTransition': 'transitionend',
        'OTransition': 'oTransitionEnd otransitionend',
        'msTransition': 'MSTransitionEnd',
        'transition': 'transitionend'
      }
    };
    settings = $.extend(settings, options);
    cssSupport = (typeof Zepto === "undefined" || Zepto === null) && (typeof Modernizr !== "undefined" && Modernizr !== null) && Modernizr.csstransforms && Modernizr.csstransitions;
    if (cssSupport) {
      transformPrefix = Modernizr.prefixed('transform').replace(/([A-Z])/g, function(str, m1) {
        return '-' + m1.toLowerCase();
      }).replace(/^ms-/, '-ms-');
      transEndEventName = settings.transEndEventNames[Modernizr.prefixed('transition')];
    }
    head = $('head');
    body = $(settings.container);
    trigger = $(settings.trigger);
    menu = $(settings.menu);
    transformPosition = settings.direction === "left" ? settings.coverage : "-" + settings.coverage;
    menuLeft = settings.direction === "left" ? "-" + settings.coverage : "100%";
    container = settings.container + "." + settings.classes.container;
    inner = container + " ." + settings.classes.inner;
    outer = container + " ." + settings.classes.outer;
    outerWrapper = $({});
    innerWrapper = $({});
    backfaceCss = "";
    if (settings.use3D) {
      backfaceCss = "-webkit-backface-visibility: hidden;";
    }
    baseCSS = "<style>  " + outer + " {      left: 0;      overflow-x: hidden;      position: absolute;      top: 0;      width: 100%;    }    " + inner + " {      position: relative; " + backfaceCss + "}" + container + " " + settings.menu + " {      display : block;      height: 0;      left    : " + menuLeft + ";      margin  : 0;      overflow: hidden;      position: absolute;      top     : 0;      width   : " + settings.coverage + ";    }  </style>";
    head.append(baseCSS);
    actions = {
      on: function() {
        body.children(':not(script)').wrapAll('<div class="' + settings.classes.outer + '"/>');
        outerWrapper = $("." + settings.classes.outer);
        outerWrapper.wrapInner('<div class="' + settings.classes.inner + '"/>');
        innerWrapper = $("." + settings.classes.inner);
        if (window.location.hash === settings.menu) {
          window.location.hash = '';
        }
        trigger.find("a").add(trigger).each(function() {
          $(this).data("href", $(this).attr("href"));
          return $(this).attr("href", "");
        });
        body.addClass(settings.classes.container);
        trigger.addClass('jms-' + settings.direction);
        $('#jmsresmenu').addClass('jms-menu-' + settings.direction);
        return trigger.on("click", function(e) {
          e.preventDefault();
          if (cssSupport || (typeof Zepto !== "undefined" && Zepto !== null)) {
            actions.pauseClicks();
          }
          return actions.toggle();
        });
      },
      off: function() {
        trigger.find("a").add(trigger).each(function() {
          $(this).attr("href", $(this).data("href"));
          return $(this).data("href", "");
        });
        actions.hide();
        body.removeClass(settings.classes.container);
        trigger.off("click");
        if (cssSupport) {
          innerWrapper.off(transEndEventName);
        }
        actions.clearHeights();
        innerWrapper.unwrap();
        return innerWrapper.children().unwrap();
      },      
      jmson: function() {
    	  body.addClass(settings.classes.container);
      },
      jmsoff: function() {
    	  body.removeClass(settings.classes.container);    	  
      },
      toggle: function() {
        if (!$(container).length) {
          return false;
        }
        if (body.hasClass(settings.classes.open) === true) {
          return actions.hide();
        } else {
          return actions.show();
        }
      },
      show: function() {
        if (!$(container).length) {
          return false;
        }
        actions.setHeights();
        actions.animate(transformPosition);
        $(window).on("resize", actions.setHeights);
        return body.addClass(settings.classes.open);
      },
      hide: function() {
        if (!$(container).length) {
          return false;
        }
        actions.animate(0);
        $(window).off("resize", actions.setHeights);
        return body.removeClass(settings.classes.open);
      },
      animate: function(position) {
        var animationCallback, innerWrapperCSS;
        if (!position) {
          animationCallback = actions.clearHeights;
        }
        if (typeof Zepto !== "undefined" && Zepto !== null) {
          return innerWrapper.animate({
            "translateX": position
          }, settings.duration, "ease", animationCallback);
        } else if (cssSupport) {
          innerWrapperCSS = {
            transition: transformPrefix + " " + settings.duration + "ms ease"
          };
          innerWrapperCSS[transformPrefix] = "translateX(" + position + ")";
          innerWrapper.css(innerWrapperCSS);
          if (!position) {
            return innerWrapper.on(transEndEventName, function() {
              actions.clearHeights();
              return innerWrapper.off(transEndEventName);
            });
          }
        } else {
          return innerWrapper.animate({
            left: position
          }, settings.duration, animationCallback);
        }
      },
      setHeights: function() {
        var height;
        actions.clearHeights();
        height = Math.max($(window).height(), $(document).height(), body.prop('scrollHeight'));
        outerWrapper.css("height", height);
        if (height > innerWrapper.height()) {
          innerWrapper.css("height", height);
        }
        if (height > menu.height()) {
          return menu.css("height", height);
        }
      },
      clearHeights: function() {
        return outerWrapper.add(innerWrapper).add(menu).css("height", "");
      },
      pauseClicks: function() {
        body.on("click", function(e) {
          e.preventDefault();
          return e.stopPropagation();
        });
        return setTimeout((function() {
          return body.off("click");
        }), settings.duration * 2);
      }
    };
    return {
      on: actions.on,
      off: actions.off,
      jmson: actions.jmson,
      jmsoff: actions.jmsoff,
      toggle: actions.toggle,
      show: actions.show,
      hide: actions.hide
    };
  };

}).call(this);

function window_resize_handler(desktop_on,maxwidth) {
	var length = $(window).width();
	if (length >= maxwidth) {
		
		$('#jmsmenuwrap').addClass('desktop-off');
		$('#jmsresmenu-trigger').hide();		
	} else {
		$('#jmsmenuwrap').removeClass('desktop-off');				
		$('#jmsresmenu-trigger').show();
	}
}