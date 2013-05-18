/**
 * LookingGlass - User friendly PHP Looking Glass
 *
 * @package     LookingGlass
 * @author      Nick Adams <nick89@zoho.com>
 * @copyright   2012 Nick Adams.
 * @link        http://iamtelephone.com
 * @license     http://opensource.org/licenses/MIT MIT License
 * @version     2.0.0
 */

/**
 * LookingGlass JS
 */
$(document).ready(function() {
  // align footer/info blocks
  equalize();
  stickyFooter();

  // MTR address
  $('#cmd').change(function() {
    if ($('#address4 option, #address6 option').length > 0) {
      ($(this).val() == 'mtr') ? $('#address4').css('display', 'inline') : $('#address4').hide();
      ($(this).val() == 'mtr6') ? $('#address6').css('display', 'inline') : $('#address6').hide();
    }
  });

  // form submit
  $('#networktest').submit(function() {
    // define vars
    var host = $('input[name=host]').val();
    var cmd  = $('select[name=cmd]').val();
    var data = 'cmd=' + cmd + '&host=' + host + '&location=' + $('input[name=location]').val()
        + '&id=' + $('input[name=id]').val();
    if (cmd == 'mtr' && $('#address4').val() !== null) {
      data = data + '&address=' + $('#address4').val();
    } else if (cmd == 'mtr6' && $('#address6').val() !== null) {
      data = data + '&address=' + $('#address6').val();
    }

    // quick validation & submit form
    if (host === '') {
      $('#host-error').addClass('error');
      $('#results, #results-title').hide();
      stickyFooter();
    } else {
      // disable submit button + show spinner + blank response
      $('#submit').attr('disabled', 'true').text('Loading...');
      $('.icon-spinner').addClass('icon-spin').css('display', 'inline-block');
      $('#response').empty();

      // scroll to bottom (for response)
      $('html, body').animate({
        scrollTop: $(document).height()
      }, 2000);

      // call async request
      var xhr = new XMLHttpRequest();
      xhr.open('GET', 'ajax.php?' + data, true);
      xhr.send(null);
      var counter = 0, currentPos = 0, text = '';
      var timer = window.setInterval(function() {
        // on completion
        if (xhr.readyState == XMLHttpRequest.DONE) {
            window.clearTimeout(timer);
            $('#submit').removeAttr('disabled').text('Run Test');
            $('.icon-spinner').removeClass('icon-spin').hide();
        }

        // output response
        if (xhr.responseText == 'Unauthorized request') {
          $('#results, #results-title').hide();
          $('#host-error').addClass('error');
          stickyFooter();
        } else if (xhr.responseText !== '') {
          if (counter < 1) {
            $('#host-error').removeClass('error');
            $('#results, #results-title').show();
          }

          // append new text
          text = xhr.responseText.replace(/<br>\s+/g, '<br>');
          $('#response').append(text.substring(currentPos));
          stickyFooter();
          currentPos = text.length;
          counter++;
        }
      }, 500);
    }

    // cancel default behavior
    return false;
  });
  stickyFooter();
});

/**
 * Resize footer & info block on browser/window resize
 */
$(window).resize(function() {
  equalize();
  stickyFooter();
});

/**
 * Sticky footer
 */
function stickyFooter() {
  var winHeight = ($('footer').hasClass('navbar') && $('#results').is(':visible'))
    ? $(window).height() - ($('footer').height() + 10)
    : $(window).height();

  (winHeight >= $(document).height())
    ? $('footer').addClass('navbar navbar-fixed-bottom')
    : $('footer').removeClass('navbar navbar-fixed-bottom');
}

/**
 * Align/Resize information blocks
 */
function equalize() {
  $('.block').height('auto');
  maxHeight = Math.max.apply(
    Math, $('.block').map(function() {
      return $(this).height();
    }).get());
  $('.block').height(maxHeight);
}

// ==================================================================
//
// External functions
//
// ------------------------------------------------------------------

/**
 * bootstrap-collapse.js v2.3.0
 * http://twitter.github.com/bootstrap/javascript.html#collapse
 */
!function ($) {
  "use strict"; // jshint ;_;

 /* COLLAPSE PUBLIC CLASS DEFINITION
  * ================================ */

  var Collapse = function (element, options) {
    this.$element = $(element)
    this.options = $.extend({}, $.fn.collapse.defaults, options)

    if (this.options.parent) {
      this.$parent = $(this.options.parent)
    }

    this.options.toggle && this.toggle()
  }

  Collapse.prototype = {
    constructor: Collapse

  , dimension: function () {
      var hasWidth = this.$element.hasClass('width')
      return hasWidth ? 'width' : 'height'
    }

  , show: function () {
      var dimension
        , scroll
        , actives
        , hasData

      if (this.transitioning || this.$element.hasClass('in')) return

      dimension = this.dimension()
      scroll = $.camelCase(['scroll', dimension].join('-'))
      actives = this.$parent && this.$parent.find('> .accordion-group > .in')

      if (actives && actives.length) {
        hasData = actives.data('collapse')
        if (hasData && hasData.transitioning) return
        actives.collapse('hide')
        hasData || actives.data('collapse', null)
      }

      this.$element[dimension](0)
      this.transition('addClass', $.Event('show'), 'shown')
      $.support.transition && this.$element[dimension](this.$element[0][scroll])
    }

  , hide: function () {
      var dimension
      if (this.transitioning || !this.$element.hasClass('in')) return
      dimension = this.dimension()
      this.reset(this.$element[dimension]())
      this.transition('removeClass', $.Event('hide'), 'hidden')
      this.$element[dimension](0)
    }

  , reset: function (size) {
      var dimension = this.dimension()

      this.$element
        .removeClass('collapse')
        [dimension](size || 'auto')
        [0].offsetWidth

      this.$element[size !== null ? 'addClass' : 'removeClass']('collapse')

      return this
    }

  , transition: function (method, startEvent, completeEvent) {
      var that = this
        , complete = function () {
            if (startEvent.type == 'show') that.reset()
            that.transitioning = 0
            that.$element.trigger(completeEvent)
          }

      this.$element.trigger(startEvent)

      if (startEvent.isDefaultPrevented()) return

      this.transitioning = 1

      this.$element[method]('in')

      $.support.transition && this.$element.hasClass('collapse') ?
        this.$element.one($.support.transition.end, complete) :
        complete()
    }

  , toggle: function () {
      this[this.$element.hasClass('in') ? 'hide' : 'show']()
    }

  }

 /* COLLAPSE PLUGIN DEFINITION
  * ========================== */

  var old = $.fn.collapse

  $.fn.collapse = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('collapse')
        , options = $.extend({}, $.fn.collapse.defaults, $this.data(), typeof option == 'object' && option)
      if (!data) $this.data('collapse', (data = new Collapse(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.collapse.defaults = {
    toggle: true
  }

  $.fn.collapse.Constructor = Collapse

 /* COLLAPSE NO CONFLICT
  * ==================== */

  $.fn.collapse.noConflict = function () {
    $.fn.collapse = old
    return this
  }

 /* COLLAPSE DATA-API
  * ================= */

  $(document).on('click.collapse.data-api', '[data-toggle=collapse]', function (e) {
    var $this = $(this), href
      , target = $this.attr('data-target')
        || e.preventDefault()
        || (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '') //strip for ie7
      , option = $(target).data('collapse') ? 'toggle' : $this.data()
    $this[$(target).hasClass('in') ? 'addClass' : 'removeClass']('collapsed')
    $(target).collapse(option)
  })
}(window.jQuery);

/**
* XMLHttpRequest.js Copyright (C) 2011 Sergey Ilinsky
* https://github.com/ilinsky/xmlhttprequest
*/
(function () {

  // Save reference to earlier defined object implementation (if any)
  var oXMLHttpRequest = window.XMLHttpRequest;

  // Define on browser type
  var bGecko  = !!window.controllers;
  var bIE     = window.document.all && !window.opera;
  var bIE7    = bIE && window.navigator.userAgent.match(/MSIE 7.0/);

  // Enables "XMLHttpRequest()" call next to "new XMLHttpRequest()"
  function fXMLHttpRequest() {
    this._object  = oXMLHttpRequest && !bIE7 ? new oXMLHttpRequest : new window.ActiveXObject("Microsoft.XMLHTTP");
    this._listeners = [];
  }

  // Constructor
  function cXMLHttpRequest() {
    return new fXMLHttpRequest;
  }
  cXMLHttpRequest.prototype = fXMLHttpRequest.prototype;

  // BUGFIX: Firefox with Firebug installed would break pages if not executed
  if (bGecko && oXMLHttpRequest.wrapped) {
    cXMLHttpRequest.wrapped = oXMLHttpRequest.wrapped;
  }

  // Constants
  cXMLHttpRequest.UNSENT            = 0;
  cXMLHttpRequest.OPENED            = 1;
  cXMLHttpRequest.HEADERS_RECEIVED  = 2;
  cXMLHttpRequest.LOADING           = 3;
  cXMLHttpRequest.DONE              = 4;

  // Interface level constants
  cXMLHttpRequest.prototype.UNSENT            = cXMLHttpRequest.UNSENT;
  cXMLHttpRequest.prototype.OPENED            = cXMLHttpRequest.OPENED;
  cXMLHttpRequest.prototype.HEADERS_RECEIVED  = cXMLHttpRequest.HEADERS_RECEIVED;
  cXMLHttpRequest.prototype.LOADING           = cXMLHttpRequest.LOADING;
  cXMLHttpRequest.prototype.DONE              = cXMLHttpRequest.DONE;

  // Public Properties
  cXMLHttpRequest.prototype.readyState    = cXMLHttpRequest.UNSENT;
  cXMLHttpRequest.prototype.responseText  = '';
  cXMLHttpRequest.prototype.responseXML   = null;
  cXMLHttpRequest.prototype.status        = 0;
  cXMLHttpRequest.prototype.statusText    = '';

  // Priority proposal
  cXMLHttpRequest.prototype.priority    = "NORMAL";

  // Instance-level Events Handlers
  cXMLHttpRequest.prototype.onreadystatechange  = null;

  // Class-level Events Handlers
  cXMLHttpRequest.onreadystatechange  = null;
  cXMLHttpRequest.onopen              = null;
  cXMLHttpRequest.onsend              = null;
  cXMLHttpRequest.onabort             = null;

  // Public Methods
  cXMLHttpRequest.prototype.open  = function(sMethod, sUrl, bAsync, sUser, sPassword) {
    // http://www.w3.org/TR/XMLHttpRequest/#the-open-method
    var backlist = sMethod.toLowerCase();
    if((backlist == "connect") || (backlist == "trace") || (backlist == "track")){
      // Using a generic error and an int - not too sure all browsers support correctly
      // http://dvcs.w3.org/hg/domcore/raw-file/tip/Overview.html#securityerror, so, this is safer
      // XXX should do better than that, but this is OT to XHR.
      throw new Error(18);
    }

    // Delete headers, required when object is reused
    delete this._headers;

    // When bAsync parameter value is omitted, use true as default
    if (arguments.length < 3) {
      bAsync  = true;
    }

    // Save async parameter for fixing Gecko bug with missing readystatechange in synchronous requests
    this._async   = bAsync;

    // Set the onreadystatechange handler
    var oRequest  = this;
    var nState    = this.readyState;
    var fOnUnload = null;

    // BUGFIX: IE - memory leak on page unload (inter-page leak)
    if (bIE && bAsync) {
      fOnUnload = function() {
        if (nState != cXMLHttpRequest.DONE) {
          fCleanTransport(oRequest);
          // Safe to abort here since onreadystatechange handler removed
          oRequest.abort();
        }
      };
      window.attachEvent("onunload", fOnUnload);
    }

    // Add method sniffer
    if (cXMLHttpRequest.onopen) {
      cXMLHttpRequest.onopen.apply(this, arguments);
    }

    if (arguments.length > 4) {
      this._object.open(sMethod, sUrl, bAsync, sUser, sPassword);
    } else if (arguments.length > 3) {
      this._object.open(sMethod, sUrl, bAsync, sUser);
    } else {
      this._object.open(sMethod, sUrl, bAsync);
    }

    this.readyState = cXMLHttpRequest.OPENED;
    fReadyStateChange(this);

    this._object.onreadystatechange = function() {
      if (bGecko && !bAsync) {
        return;
      }

      // Synchronize state
      oRequest.readyState   = oRequest._object.readyState;
      fSynchronizeValues(oRequest);

      // BUGFIX: Firefox fires unnecessary DONE when aborting
      if (oRequest._aborted) {
        // Reset readyState to UNSENT
        oRequest.readyState = cXMLHttpRequest.UNSENT;

        // Return now
        return;
      }

      if (oRequest.readyState == cXMLHttpRequest.DONE) {
        // Free up queue
        delete oRequest._data;

        fCleanTransport(oRequest);

        // BUGFIX: IE - memory leak in interrupted
        if (bIE && bAsync) {
          window.detachEvent("onunload", fOnUnload);
        }

        // BUGFIX: Some browsers (Internet Explorer, Gecko) fire OPEN readystate twice
        if (nState != oRequest.readyState) {
          fReadyStateChange(oRequest);
        }

        nState  = oRequest.readyState;
      }
    };
  };

  cXMLHttpRequest.prototype.send = function(vData) {
    // Add method sniffer
    if (cXMLHttpRequest.onsend) {
      cXMLHttpRequest.onsend.apply(this, arguments);
    }

    if (!arguments.length) {
      vData = null;
    }

    // BUGFIX: Safari - fails sending documents created/modified dynamically, so an explicit serialization required
    // BUGFIX: IE - rewrites any custom mime-type to "text/xml" in case an XMLNode is sent
    // BUGFIX: Gecko - fails sending Element (this is up to the implementation either to standard)
    if (vData && vData.nodeType) {
      vData = window.XMLSerializer ? new window.XMLSerializer().serializeToString(vData) : vData.xml;
      if (!this._headers["Content-Type"]) {
        this._object.setRequestHeader("Content-Type", "application/xml");
      }
    }

    this._data = vData;

    fXMLHttpRequest_send(this);
  };

  cXMLHttpRequest.prototype.abort = function() {
    // Add method sniffer
    if (cXMLHttpRequest.onabort) {
      cXMLHttpRequest.onabort.apply(this, arguments);
    }

    // BUGFIX: Gecko - unnecessary DONE when aborting
    if (this.readyState > cXMLHttpRequest.UNSENT) {
      this._aborted = true;
    }

    this._object.abort();

    // BUGFIX: IE - memory leak
    fCleanTransport(this);

    this.readyState = cXMLHttpRequest.UNSENT;

    delete this._data;

    /* if (this._async) {
    *   fQueue_remove(this);
    * }
    */
  };

  cXMLHttpRequest.prototype.getAllResponseHeaders = function() {
    return this._object.getAllResponseHeaders();
  };

  cXMLHttpRequest.prototype.getResponseHeader = function(sName) {
    return this._object.getResponseHeader(sName);
  };

  cXMLHttpRequest.prototype.setRequestHeader  = function(sName, sValue) {
    // BUGFIX: IE - cache issue
    if (!this._headers) {
      this._headers = {};
    }

    this._headers[sName]  = sValue;

    return this._object.setRequestHeader(sName, sValue);
  };

  // EventTarget interface implementation
  cXMLHttpRequest.prototype.addEventListener  = function(sName, fHandler, bUseCapture) {
    for (var nIndex = 0, oListener; oListener = this._listeners[nIndex]; nIndex++) {
      if (oListener[0] == sName && oListener[1] == fHandler && oListener[2] == bUseCapture) {
        return;
      }
    }

    // Add listener
    this._listeners.push([sName, fHandler, bUseCapture]);
  };

  cXMLHttpRequest.prototype.removeEventListener = function(sName, fHandler, bUseCapture) {
    for (var nIndex = 0, oListener; oListener = this._listeners[nIndex]; nIndex++) {
      if (oListener[0] == sName && oListener[1] == fHandler && oListener[2] == bUseCapture) {
        break;
      }
    }

    // Remove listener
    if (oListener) {
      this._listeners.splice(nIndex, 1);
    }
  };

  cXMLHttpRequest.prototype.dispatchEvent = function(oEvent) {
    var oEventPseudo  = {
      'type':             oEvent.type,
      'target':           this,
      'currentTarget':    this,
      'eventPhase':       2,
      'bubbles':          oEvent.bubbles,
      'cancelable':       oEvent.cancelable,
      'timeStamp':        oEvent.timeStamp,
      'stopPropagation':  function() {},  // There is no flow
      'preventDefault':   function() {},  // There is no default action
      'initEvent':        function() {}   // Original event object should be initialized
    };

    // Execute onreadystatechange
    if (oEventPseudo.type == "readystatechange" && this.onreadystatechange) {
      (this.onreadystatechange.handleEvent || this.onreadystatechange).apply(this, [oEventPseudo]);
    }


    // Execute listeners
    for (var nIndex = 0, oListener; oListener = this._listeners[nIndex]; nIndex++) {
      if (oListener[0] == oEventPseudo.type && !oListener[2]) {
        (oListener[1].handleEvent || oListener[1]).apply(this, [oEventPseudo]);
      }
    }

  };

  //
  cXMLHttpRequest.prototype.toString  = function() {
    return '[' + "object" + ' ' + "XMLHttpRequest" + ']';
  };

  cXMLHttpRequest.toString  = function() {
    return '[' + "XMLHttpRequest" + ']';
  };

  // Helper function
  function fXMLHttpRequest_send(oRequest) {
    oRequest._object.send(oRequest._data);

    // BUGFIX: Gecko - missing readystatechange calls in synchronous requests
    if (bGecko && !oRequest._async) {
      oRequest.readyState = cXMLHttpRequest.OPENED;

      // Synchronize state
      fSynchronizeValues(oRequest);

      // Simulate missing states
      while (oRequest.readyState < cXMLHttpRequest.DONE) {
        oRequest.readyState++;
        fReadyStateChange(oRequest);
        // Check if we are aborted
        if (oRequest._aborted) {
          return;
        }
      }
    }
  }

  function fReadyStateChange(oRequest) {
    // Sniffing code
    if (cXMLHttpRequest.onreadystatechange){
      cXMLHttpRequest.onreadystatechange.apply(oRequest);
    }


    // Fake event
    oRequest.dispatchEvent({
      'type':       "readystatechange",
      'bubbles':    false,
      'cancelable': false,
      'timeStamp':  new Date + 0
    });
  }

  function fGetDocument(oRequest) {
    var oDocument = oRequest.responseXML;
    var sResponse = oRequest.responseText;
    // Try parsing responseText
    if (bIE && sResponse && oDocument && !oDocument.documentElement && oRequest.getResponseHeader("Content-Type").match(/[^\/]+\/[^\+]+\+xml/)) {
      oDocument = new window.ActiveXObject("Microsoft.XMLDOM");
      oDocument.async       = false;
      oDocument.validateOnParse = false;
      oDocument.loadXML(sResponse);
    }

    // Check if there is no error in document
    if (oDocument){
      if ((bIE && oDocument.parseError !== 0) || !oDocument.documentElement || (oDocument.documentElement && oDocument.documentElement.tagName == "parsererror")) {
        return null;
      }
    }
    return oDocument;
  }

  function fSynchronizeValues(oRequest) {
    try { oRequest.responseText = oRequest._object.responseText;  } catch (e) {}
    try { oRequest.responseXML  = fGetDocument(oRequest._object); } catch (e) {}
    try { oRequest.status       = oRequest._object.status;        } catch (e) {}
    try { oRequest.statusText   = oRequest._object.statusText;    } catch (e) {}
  }

  function fCleanTransport(oRequest) {
    // BUGFIX: IE - memory leak (on-page leak)
    oRequest._object.onreadystatechange = new window.Function;
  }

  // Internet Explorer 5.0 (missing apply)
  if (!window.Function.prototype.apply) {
    window.Function.prototype.apply = function(oRequest, oArguments) {
      if (!oArguments) {
        oArguments  = [];
      }
      oRequest.__func = this;
      oRequest.__func(oArguments[0], oArguments[1], oArguments[2], oArguments[3], oArguments[4]);
      delete oRequest.__func;
    };
  }

  // Register new object with window
  window.XMLHttpRequest = cXMLHttpRequest;
})();