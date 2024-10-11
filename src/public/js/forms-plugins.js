/** @preserve FlashCanvas, ${buildDate} ${commitID}
 * Copyright 2012 Willow Systems Corp
 * Copyright (c) 2009      Tim Cameron Ryan
 * Copyright (c) 2009-2011 FlashCanvas Project
 * Released under the MIT/X License
 */

// Reference:
//   http://www.whatwg.org/specs/web-apps/current-work/multipage/the-canvas-element.html
//   http://dev.w3.org/html5/spec/the-canvas-element.html

// If the browser is IE and does not support HTML5 Canvas
if (window["ActiveXObject"] && !window["CanvasRenderingContext2D"]) {

(function() {
'use strict'

var window = this
, document = window.document
, undefined

/*
 * Constant
 */

var NULL                        = null;
var CANVAS                      = "canvas";
var CANVAS_RENDERING_CONTEXT_2D = "CanvasRenderingContext2D";
var CANVAS_GRADIENT             = "CanvasGradient";
var CANVAS_PATTERN              = "CanvasPattern";
var FLASH_CANVAS                = "FlashCanvas";
var OBJECT_ID_PREFIX            = "external";
var ON_FOCUS                    = "onfocus";
var ON_PROPERTY_CHANGE          = "onpropertychange";
var ON_READY_STATE_CHANGE       = "onreadystatechange";
var ON_UNLOAD                   = "onunload";

var BASE_URL = (function(){
    var scripts = this.document.getElementsByTagName("script")

    // async script tag injections lead to our script NOT being the last. so
    // var script  = scripts[scripts.length - 1];
    // will not work

    // so we just loop over scripts and look for "flashcanvas"
    // and go for "last script tag's src" only if path is not matched 
    // (may happen when flashcanvas script is loaded with name not containing 'flashcanvas')

    // backwardCompatibilityUrl: original script was looking at last script tag's src.
    // we simulate that for cases when proper URL is not found elsewhere.

    var backwardCompatibilityUrl = ''
    var i = scripts.length
    if (i) {
        backwardCompatibilityUrl = scripts[i - 1].src || ''
        while (i){
            script = scripts[i - 1] // yes, we look from the back of the queue
            if (script.src && script.src.match('flashcanvas')) {
                // we are trying to return absolute path:
                // @see http://msdn.microsoft.com/en-us/library/ms536429(VS.85).aspx
                // @see http://stackoverflow.com/questions/984510/what-is-my-script-src-url
                if (document.documentMode >= 8) {
                    return script.src;
                } else {
                    return script.getAttribute("src", 4);
                }
            }
            ;i--;
        }
    }
    return backwardCompatibilityUrl
}).call(window).replace(/[^\/]+$/, "") // last part trims all chars following last '/'

// DOMException code
var INDEX_SIZE_ERR              =  1;
var NOT_SUPPORTED_ERR           =  9;
var INVALID_STATE_ERR           = 11;
var SYNTAX_ERR                  = 12;
var TYPE_MISMATCH_ERR           = 17;
var SECURITY_ERR                = 18;

/**
 * @constructor
 */
function Lookup(array) {
    for (var i = 0, n = array.length; i < n; i++)
        this[array[i]] = i;
}

var properties = new Lookup([
    // Canvas element
    "toDataURL",

    // CanvasRenderingContext2D
    "save",
    "restore",
    "scale",
    "rotate",
    "translate",
    "transform",
    "setTransform",
    "globalAlpha",
    "globalCompositeOperation",
    "strokeStyle",
    "fillStyle",
    "createLinearGradient",
    "createRadialGradient",
    "createPattern",
    "lineWidth",
    "lineCap",
    "lineJoin",
    "miterLimit",
    "shadowOffsetX",
    "shadowOffsetY",
    "shadowBlur",
    "shadowColor",
    "clearRect",
    "fillRect",
    "strokeRect",
    "beginPath",
    "closePath",
    "moveTo",
    "lineTo",
    "quadraticCurveTo",
    "bezierCurveTo",
    "arcTo",
    "rect",
    "arc",
    "fill",
    "stroke",
    "clip",
    "isPointInPath",
//  "drawFocusRing",
    "font",
    "textAlign",
    "textBaseline",
    "fillText",
    "strokeText",
    "measureText",
    "drawImage",
    "createImageData",
    "getImageData",
    "putImageData",

    // CanvasGradient
    "addColorStop",

    // Internal use
    "direction",
    "resize"
]);

// Whether swf is ready for use
var isReady = {};

// Cache of images loaded by createPattern() or drawImage()
var images = {};

// Monitor the number of loading files
var lock = {};

// Callback functions passed to loadImage()
var callbacks = {};

// SPAN element embedded in the canvas
var spans = {};

var elementIsOrphan = function(e){
    var topOfDOM = false
    e = e.parentNode
    while (e && !topOfDOM){
        topOfDOM = e.body
        e = e.parentNode
    }
    return !topOfDOM
}

/**
 * 2D context
 * @constructor
 */
var CanvasRenderingContext2D = function(canvas, swf) {

    // back-reference to the canvas
    this.canvas = canvas;

    // back-reference to the swf
    this._swf = swf;

    // unique ID of canvas
    this._canvasId = swf.id.slice(8);

    // initialize drawing states
    this._initialize();

    // Count CanvasGradient and CanvasPattern objects
    this._gradientPatternId = 0;

    // Directionality of the canvas element
    this._direction = "";

    // This ensures that font properties of the canvas element is
    // transmitted to Flash.
    this._font = "";

    // frame update interval
    var self = this
    this._executeCommandIntervalID = setInterval(function() {
        if (elementIsOrphan(self.canvas)) {
            clearInterval(self._executeCommandIntervalID)
        } else {
            if (lock[self._canvasId] === 0) {
                self._executeCommand();
            }
        }
    }, 30)
};

CanvasRenderingContext2D.prototype = {
    /*
     * state
     */

    save: function() {
        // write all properties
        this._setCompositing();
        this._setShadows();
        this._setStrokeStyle();
        this._setFillStyle();
        this._setLineStyles();
        this._setFontStyles();

        // push state
        this._stateStack.push([
            this._globalAlpha,
            this._globalCompositeOperation,
            this._strokeStyle,
            this._fillStyle,
            this._lineWidth,
            this._lineCap,
            this._lineJoin,
            this._miterLimit,
            this._shadowOffsetX,
            this._shadowOffsetY,
            this._shadowBlur,
            this._shadowColor,
            this._font,
            this._textAlign,
            this._textBaseline
        ]);

        this._queue.push(properties.save);
    },

    restore: function() {
        // pop state
        var stateStack = this._stateStack;
        if (stateStack.length) {
            var state = stateStack.pop();
            this.globalAlpha              = state[0];
            this.globalCompositeOperation = state[1];
            this.strokeStyle              = state[2];
            this.fillStyle                = state[3];
            this.lineWidth                = state[4];
            this.lineCap                  = state[5];
            this.lineJoin                 = state[6];
            this.miterLimit               = state[7];
            this.shadowOffsetX            = state[8];
            this.shadowOffsetY            = state[9];
            this.shadowBlur               = state[10];
            this.shadowColor              = state[11];
            this.font                     = state[12];
            this.textAlign                = state[13];
            this.textBaseline             = state[14];
        }

        this._queue.push(properties.restore);
    },

    /*
     * transformations
     */

    scale: function(x, y) {
        this._queue.push(properties.scale, x, y);
    },

    rotate: function(angle) {
        this._queue.push(properties.rotate, angle);
    },

    translate: function(x, y) {
        this._queue.push(properties.translate, x, y);
    },

    transform: function(m11, m12, m21, m22, dx, dy) {
        this._queue.push(properties.transform, m11, m12, m21, m22, dx, dy);
    },

    setTransform: function(m11, m12, m21, m22, dx, dy) {
        this._queue.push(properties.setTransform, m11, m12, m21, m22, dx, dy);
    },

    /*
     * compositing
     */

    _setCompositing: function() {
        var queue = this._queue;
        if (this._globalAlpha !== this.globalAlpha) {
            this._globalAlpha = this.globalAlpha;
            queue.push(properties.globalAlpha, this._globalAlpha);
        }
        if (this._globalCompositeOperation !== this.globalCompositeOperation) {
            this._globalCompositeOperation = this.globalCompositeOperation;
            queue.push(properties.globalCompositeOperation, this._globalCompositeOperation);
        }
    },

    /*
     * colors and styles
     */

    _setStrokeStyle: function() {
        if (this._strokeStyle !== this.strokeStyle) {
            var style = this._strokeStyle = this.strokeStyle;
            if (typeof style === "string") {
                // OK
            } else if (style instanceof CanvasGradient ||
                       style instanceof CanvasPattern) {
                style = style.id;
            } else {
                return;
            }
            this._queue.push(properties.strokeStyle, style);
        }
    },

    _setFillStyle: function() {
        if (this._fillStyle !== this.fillStyle) {
            var style = this._fillStyle = this.fillStyle;
            if (typeof style === "string") {
                // OK
            } else if (style instanceof CanvasGradient ||
                       style instanceof CanvasPattern) {
                style = style.id;
            } else {
                return;
            }
            this._queue.push(properties.fillStyle, style);
        }
    },

    createLinearGradient: function(x0, y0, x1, y1) {
        // If any of the arguments are not finite numbers, throws a
        // NOT_SUPPORTED_ERR exception.
        if (!(isFinite(x0) && isFinite(y0) && isFinite(x1) && isFinite(y1))) {
            throwException(NOT_SUPPORTED_ERR);
        }

        this._queue.push(properties.createLinearGradient, x0, y0, x1, y1);
        return new CanvasGradient(this);
    },

    createRadialGradient: function(x0, y0, r0, x1, y1, r1) {
        // If any of the arguments are not finite numbers, throws a
        // NOT_SUPPORTED_ERR exception.
        if (!(isFinite(x0) && isFinite(y0) && isFinite(r0) &&
              isFinite(x1) && isFinite(y1) && isFinite(r1))) {
            throwException(NOT_SUPPORTED_ERR);
        }

        // If either of the radii are negative, throws an INDEX_SIZE_ERR
        // exception.
        if (r0 < 0 || r1 < 0) {
            throwException(INDEX_SIZE_ERR);
        }

        this._queue.push(properties.createRadialGradient, x0, y0, r0, x1, y1, r1);
        return new CanvasGradient(this);
    },

    createPattern: function(image, repetition) {
        // If the image is null, the implementation must raise a
        // TYPE_MISMATCH_ERR exception.
        if (!image) {
            throwException(TYPE_MISMATCH_ERR);
        }

        var tagName = image.tagName, src;
        var canvasId = this._canvasId;

        // If the first argument isn't an img, canvas, or video element,
        // throws a TYPE_MISMATCH_ERR exception.
        if (tagName) {
            tagName = tagName.toLowerCase();
            if (tagName === "img") {
                src = image.getAttribute("src", 2);
            } else if (tagName === CANVAS || tagName === "video") {
                // For now, only HTMLImageElement is supported.
                return;
            } else {
                throwException(TYPE_MISMATCH_ERR);
            }
        }

        // Additionally, we accept any object that has a src property.
        // This is useful when you'd like to specify a long data URI.
        else if (image.src) {
            src = image.src;
        } else {
            throwException(TYPE_MISMATCH_ERR);
        }

        // If the second argument isn't one of the allowed values, throws a
        // SYNTAX_ERR exception.
        if (!(repetition === "repeat"   || repetition === "no-repeat" ||
              repetition === "repeat-x" || repetition === "repeat-y"  ||
              repetition === ""         || repetition === NULL)) {
            throwException(SYNTAX_ERR);
        }

        // Special characters in the filename need escaping.
        this._queue.push(properties.createPattern, encodeXML(src), repetition);

        // If this is the first time to access the URL, the canvas should be
        // locked while the image is being loaded asynchronously.
        if (!images[canvasId][src] && isReady[canvasId]) {
            this._executeCommand();
            ++lock[canvasId];
            images[canvasId][src] = true;
        }

        return new CanvasPattern(this);
    },

    /*
     * line caps/joins
     */

    _setLineStyles: function() {
        var queue = this._queue;
        if (this._lineWidth !== this.lineWidth) {
            this._lineWidth = this.lineWidth;
            queue.push(properties.lineWidth, this._lineWidth);
        }
        if (this._lineCap !== this.lineCap) {
            this._lineCap = this.lineCap;
            queue.push(properties.lineCap, this._lineCap);
        }
        if (this._lineJoin !== this.lineJoin) {
            this._lineJoin = this.lineJoin;
            queue.push(properties.lineJoin, this._lineJoin);
        }
        if (this._miterLimit !== this.miterLimit) {
            this._miterLimit = this.miterLimit;
            queue.push(properties.miterLimit, this._miterLimit);
        }
    },

    /*
     * shadows
     */

    _setShadows: function() {
        var queue = this._queue;
        if (this._shadowOffsetX !== this.shadowOffsetX) {
            this._shadowOffsetX = this.shadowOffsetX;
            queue.push(properties.shadowOffsetX, this._shadowOffsetX);
        }
        if (this._shadowOffsetY !== this.shadowOffsetY) {
            this._shadowOffsetY = this.shadowOffsetY;
            queue.push(properties.shadowOffsetY, this._shadowOffsetY);
        }
        if (this._shadowBlur !== this.shadowBlur) {
            this._shadowBlur = this.shadowBlur;
            queue.push(properties.shadowBlur, this._shadowBlur);
        }
        if (this._shadowColor !== this.shadowColor) {
            this._shadowColor = this.shadowColor;
            queue.push(properties.shadowColor, this._shadowColor);
        }
    },

    /*
     * rects
     */

    clearRect: function(x, y, w, h) {
        this._queue.push(properties.clearRect, x, y, w, h);
    },

    fillRect: function(x, y, w, h) {
        this._setCompositing();
        this._setShadows();
        this._setFillStyle();
        this._queue.push(properties.fillRect, x, y, w, h);
    },

    strokeRect: function(x, y, w, h) {
        this._setCompositing();
        this._setShadows();
        this._setStrokeStyle();
        this._setLineStyles();
        this._queue.push(properties.strokeRect, x, y, w, h);
    },

    /*
     * path API
     */

    beginPath: function() {
        this._queue.push(properties.beginPath);
    },

    closePath: function() {
        this._queue.push(properties.closePath);
    },

    moveTo: function(x, y) {
        this._queue.push(properties.moveTo, x, y);
    },

    lineTo: function(x, y) {
        this._queue.push(properties.lineTo, x, y);
    },

    quadraticCurveTo: function(cpx, cpy, x, y) {
        this._queue.push(properties.quadraticCurveTo, cpx, cpy, x, y);
    },

    bezierCurveTo: function(cp1x, cp1y, cp2x, cp2y, x, y) {
        this._queue.push(properties.bezierCurveTo, cp1x, cp1y, cp2x, cp2y, x, y);
    },

    arcTo: function(x1, y1, x2, y2, radius) {
        // Throws an INDEX_SIZE_ERR exception if the given radius is negative.
        if (radius < 0 && isFinite(radius)) {
            throwException(INDEX_SIZE_ERR);
        }

        this._queue.push(properties.arcTo, x1, y1, x2, y2, radius);
    },

    rect: function(x, y, w, h) {
        this._queue.push(properties.rect, x, y, w, h);
    },

    arc: function(x, y, radius, startAngle, endAngle, anticlockwise) {
        // Throws an INDEX_SIZE_ERR exception if the given radius is negative.
        if (radius < 0 && isFinite(radius)) {
            throwException(INDEX_SIZE_ERR);
        }

        this._queue.push(properties.arc, x, y, radius, startAngle, endAngle, anticlockwise ? 1 : 0);
    },

    fill: function() {
        this._setCompositing();
        this._setShadows();
        this._setFillStyle();
        this._queue.push(properties.fill);
    },

    stroke: function() {
        this._setCompositing();
        this._setShadows();
        this._setStrokeStyle();
        this._setLineStyles();
        this._queue.push(properties.stroke);
    },

    clip: function() {
        this._queue.push(properties.clip);
    },

    isPointInPath: function(x, y) {
        // TODO: Implement
    },

    /*
     * text
     */

    _setFontStyles: function() {
        var queue = this._queue;
        if (this._font !== this.font) {
            try {
                var span = spans[this._canvasId];
                span.style.font = this._font = this.font;

                var style = span.currentStyle;
                var fontSize = span.offsetHeight;
                var font = [style.fontStyle, style.fontWeight, fontSize, style.fontFamily].join(" ");
                queue.push(properties.font, font);
            } catch(e) {
                // If this.font cannot be parsed as a CSS font value, then it
                // must be ignored.
            }
        }
        if (this._textAlign !== this.textAlign) {
            this._textAlign = this.textAlign;
            queue.push(properties.textAlign, this._textAlign);
        }
        if (this._textBaseline !== this.textBaseline) {
            this._textBaseline = this.textBaseline;
            queue.push(properties.textBaseline, this._textBaseline);
        }
        if (this._direction !== this.canvas.currentStyle.direction) {
            this._direction = this.canvas.currentStyle.direction;
            queue.push(properties.direction, this._direction);
        }
    },

    fillText: function(text, x, y, maxWidth) {
        this._setCompositing();
        this._setFillStyle();
        this._setShadows();
        this._setFontStyles();
        this._queue.push(properties.fillText, encodeXML(text), x, y,
                         maxWidth === undefined ? Infinity : maxWidth);
    },

    strokeText: function(text, x, y, maxWidth) {
        this._setCompositing();
        this._setStrokeStyle();
        this._setShadows();
        this._setFontStyles();
        this._queue.push(properties.strokeText, encodeXML(text), x, y,
                         maxWidth === undefined ? Infinity : maxWidth);
    },

    measureText: function(text) {
        var span = spans[this._canvasId];
        try {
            span.style.font = this.font;
        } catch(e) {
            // If this.font cannot be parsed as a CSS font value, then it must
            // be ignored.
        }

        // Replace space characters with tab characters because innerText
        // removes trailing white spaces.
        span.innerText = text.replace(/[ \n\f\r]/g, "\t");

        return new TextMetrics(span.offsetWidth);
    },

    /*
     * drawing images
     */

    drawImage: function(image, x1, y1, w1, h1, x2, y2, w2, h2) {
        // If the image is null, the implementation must raise a
        // TYPE_MISMATCH_ERR exception.
        if (!image) {
            throwException(TYPE_MISMATCH_ERR);
        }

        var tagName = image.tagName, src, argc = arguments.length;
        var canvasId = this._canvasId;

        // If the first argument isn't an img, canvas, or video element,
        // throws a TYPE_MISMATCH_ERR exception.
        if (tagName) {
            tagName = tagName.toLowerCase();
            if (tagName === "img") {
                src = image.getAttribute("src", 2);
            } else if (tagName === CANVAS || tagName === "video") {
                // For now, only HTMLImageElement is supported.
                return;
            } else {
                throwException(TYPE_MISMATCH_ERR);
            }
        }

        // Additionally, we accept any object that has a src property.
        // This is useful when you'd like to specify a long data URI.
        else if (image.src) {
            src = image.src;
        } else {
            throwException(TYPE_MISMATCH_ERR);
        }

        this._setCompositing();
        this._setShadows();

        // Special characters in the filename need escaping.
        src = encodeXML(src);

        if (argc === 3) {
            this._queue.push(properties.drawImage, argc, src, x1, y1);
        } else if (argc === 5) {
            this._queue.push(properties.drawImage, argc, src, x1, y1, w1, h1);
        } else if (argc === 9) {
            // If one of the sw or sh arguments is zero, the implementation
            // must raise an INDEX_SIZE_ERR exception.
            if (w1 === 0 || h1 === 0) {
                throwException(INDEX_SIZE_ERR);
            }

            this._queue.push(properties.drawImage, argc, src, x1, y1, w1, h1, x2, y2, w2, h2);
        } else {
            return;
        }

        // If this is the first time to access the URL, the canvas should be
        // locked while the image is being loaded asynchronously.
        if (!images[canvasId][src] && isReady[canvasId]) {
            this._executeCommand();
            ++lock[canvasId];
            images[canvasId][src] = true;
        }
    },

    /*
     * pixel manipulation
     */

    // ImageData createImageData(in float sw, in float sh);
    // ImageData createImageData(in ImageData imagedata);
    createImageData: function() {
        // TODO: Implement
    },

    // ImageData getImageData(in float sx, in float sy, in float sw, in float sh);
    getImageData: function(sx, sy, sw, sh) {
        // TODO: Implement
    },

    // void putImageData(in ImageData imagedata, in float dx, in float dy, [Optional] in float dirtyX, in float dirtyY, in float dirtyWidth, in float dirtyHeight);
    putImageData: function(imagedata, dx, dy, dirtyX, dirtyY, dirtyWidth, dirtyHeight) {
        // TODO: Implement
    },

    /*
     * extended functions
     */

    loadImage: function(image, onload, onerror) {
        var tagName = image.tagName, src;
        var canvasId = this._canvasId;

        // Get the URL of the image.
        if (tagName) {
            if (tagName.toLowerCase() === "img") {
                src = image.getAttribute("src", 2);
            }
        } else if (image.src) {
            src = image.src;
        }

        // Do nothing in the following cases:
        //  - The first argument is neither an img element nor an object
        //    with a src property,
        //  - The image has been already cached.
        if (!src || images[canvasId][src]) {
            return;
        }

        // Store the objects.
        if (onload || onerror) {
            callbacks[canvasId][src] = [image, onload, onerror];
        }

        // Load the image without drawing.
        this._queue.push(properties.drawImage, 1, encodeXML(src));

        // Execute the command immediately if possible.
        if (isReady[canvasId]) {
            this._executeCommand();
            ++lock[canvasId];
            images[canvasId][src] = true;
        }
     },

    /*
     * private methods
     */

    _initialize: function() {

        // compositing
        this.globalAlpha = this._globalAlpha = 1.0;
        this.globalCompositeOperation = this._globalCompositeOperation = "source-over";

        // colors and styles
        this.strokeStyle = this._strokeStyle = "#000000";
        this.fillStyle   = this._fillStyle   = "#000000";

        // line caps/joins
        this.lineWidth  = this._lineWidth  = 1.0;
        this.lineCap    = this._lineCap    = "butt";
        this.lineJoin   = this._lineJoin   = "miter";
        this.miterLimit = this._miterLimit = 10.0;

        // shadows
        this.shadowOffsetX = this._shadowOffsetX = 0;
        this.shadowOffsetY = this._shadowOffsetY = 0;
        this.shadowBlur    = this._shadowBlur    = 0;
        this.shadowColor   = this._shadowColor   = "rgba(0, 0, 0, 0.0)";

        // text
        this.font         = this._font         = "10px sans-serif";
        this.textAlign    = this._textAlign    = "start";
        this.textBaseline = this._textBaseline = "alphabetic";

        // command queue
        this._queue = [];

        // stack of drawing states
        this._stateStack = [];
    },

    _flush: function() {
        var queue = this._queue;
        this._queue = [];
        return queue;
    },

    _executeCommand: function() {
        // execute commands
        var commands = this._flush();
        if (commands.length > 0) {
            try {
                return eval( this._swf.CallFunction(
                    '<invoke name="executeCommand" returntype="javascript"><arguments><string>'
                    + commands.join("&#0;") + "</string></arguments></invoke>"
                ))
            } catch (ex) {
            }
        }
    },

    _resize: function(width, height) {
        // Flush commands in the queue
        this._executeCommand();

        // Clear back to the initial state
        this._initialize();

        // Adjust the size of Flash to that of the canvas
        if (width > 0) {
            this._swf.width = width;
        }
        if (height > 0) {
            this._swf.height = height;
        }

        // Execute a resize command at the start of the next frame
        this._queue.push(properties.resize, width, height);
    }
};

/**
 * CanvasGradient stub
 * @constructor
 */
var CanvasGradient = function(ctx) {
    this._ctx = ctx;
    this.id   = ctx._gradientPatternId++;
};

CanvasGradient.prototype = {
    addColorStop: function(offset, color) {
        // Throws an INDEX_SIZE_ERR exception if the offset is out of range.
        if (isNaN(offset) || offset < 0 || offset > 1) {
            throwException(INDEX_SIZE_ERR);
        }

        this._ctx._queue.push(properties.addColorStop, this.id, offset, color);
    }
};

/**
 * CanvasPattern stub
 * @constructor
 */
var CanvasPattern = function(ctx) {
    this.id = ctx._gradientPatternId++;
};

/**
 * TextMetrics stub
 * @constructor
 */
var TextMetrics = function(width) {
    this.width = width;
};

/**
 * DOMException
 * @constructor
 */
var DOMException = function(code) {
    var DOMExceptionNames = {
        1:  "INDEX_SIZE_ERR",
        9:  "NOT_SUPPORTED_ERR",
        11: "INVALID_STATE_ERR",
        12: "SYNTAX_ERR",
        17: "TYPE_MISMATCH_ERR",
        18: "SECURITY_ERR"
    }

    this.code    = code;
    this.message = DOMExceptionNames[code];
};

DOMException.prototype = new Error;

/*
 * Event handlers
 */


/*
 * FlashCanvas global object API (not the Canvas API, just initializer etc.)
 */

/**
Generates a URL pointing to fashcanvas.swf file by inspecing constants and Window-specific
settings and deriving the path appropirate for that Window.
@public
@function
@param window {Object} Pointer to Window (top, child frames) object instance into which we will dig.
@returns {String} relative or absolute path to the swf file.
*/
function getSwfUrl(window) {
    return ( (window[FLASH_CANVAS + "Options"] || {})["swfPath"] || BASE_URL ) + "flashcanvas.swf"
}

var registeredEvents = 'registeredEvents'
, canvasesProp = 'canvases'
, initWindow = 'initWindow'
, initElement = 'initElement'
, saveImage = 'saveImage'
, unlock = 'unlock'
, trigger = 'trigger'

var FlashCanvas = {}

FlashCanvas[registeredEvents] = {} // 'canvasID':[[eventName, handler],...]
FlashCanvas[canvasesProp] = {}

FlashCanvas[initWindow] = function(window){

    var document = window.document

    // IE HTML5 shiv
    document.createElement(CANVAS);

    // setup default CSS
    document.createStyleSheet().cssText =
        CANVAS + "{display:inline-block;overflow:hidden;width:300px;height:150px}";

    var canvases = this[canvasesProp]

    var registeredEvents = this.registeredEvents

    var onUnload = function() {
        window.detachEvent(ON_UNLOAD, onUnload);

        var canvas
        , swf
        , prop
        , NULL = null
        , parentWindow
        , i, l, e

        for (var canvasId in canvases) {
            canvas = canvases[canvasId]
            swf = canvas.firstChild
            parentWindow = canvas.ownerDocument.defaultView ? canvas.ownerDocument.defaultView : canvas.ownerDocument.parentWindow

            // parent frame may be handling canvas elemns in self and in children frames. We only kill
            // the canvases in "windows" that "unloaded"
            if (window === parentWindow) {
                // clean up the references of swf.executeCommand and swf.resize
                for (prop in swf) {
                    if (typeof swf[prop] === "function") {
                        swf[prop] = NULL;
                    }
                }

                // clean up the references of canvas.getContext and canvas.toDataURL
                for (prop in canvas) {
                    if (typeof canvas[prop] === "function") {
                        canvas[prop] = NULL;
                    }
                }

                i = 0
                l = registeredEvents[canvasId].length
                for (; i !== l; i++) {
                    e = registeredEvents[canvasId][i] // it's an array: [eventName, eventHandler]
                    swf.detachEvent(e[0], e[1]);
                    canvas.detachEvent(e[0], e[1]);
                }
            }
        }

        // delete exported symbols
        window[CANVAS_RENDERING_CONTEXT_2D] = NULL;
        window[CANVAS_GRADIENT]             = NULL;
        window[CANVAS_PATTERN]              = NULL;
        window[FLASH_CANVAS]                = NULL;
    }

    // prevent IE6 memory leaks
    window.attachEvent(ON_UNLOAD, onUnload);

    window[CANVAS_RENDERING_CONTEXT_2D] = CanvasRenderingContext2D;
    window[CANVAS_GRADIENT]             = CanvasGradient;
    window[CANVAS_PATTERN]              = CanvasPattern;
    window[FLASH_CANVAS]                = FlashCanvas;

    // preload SWF file if it's in the same domain
    var swfUrl = getSwfUrl(window)
    if (swfUrl.indexOf(window.location.protocol + "//" + window.location.host + "/") === 0) {
        window.setTimeout(function(){
            var req = new ActiveXObject("Microsoft.XMLHTTP");
            req.open("GET", swfUrl, false);
            req.send(NULL);
        }, 0)
    }

    function onReadyStateChange() {
        if (window.document.readyState === "complete") {
            window.document.detachEvent(ON_READY_STATE_CHANGE, onReadyStateChange);
            var canvases = window.document.getElementsByTagName(CANVAS);
            for (var i = 0, n = canvases.length; i < n; ++i) {
                FlashCanvas[initElement](canvases[i]);
            }
        }
    }

    // initialize canvas elements
    if (window.document.readyState === "complete") {
        onReadyStateChange();
    } else {
        window.document.attachEvent(ON_READY_STATE_CHANGE, onReadyStateChange);
    }

}

FlashCanvas[initElement] = function(canvas) {
    // Check whether the initialization is required or not.
    if (canvas.getContext) {
        return canvas;
    }

    // when init is called from parent frame over canvas sitting in child frame,
    // FlashCanvas does not pick up the right "window" or "document" - the one from child frame.
    // to avoid making the users specify window, document, we sniff them out from canvas element.
    var document = canvas.ownerDocument
    , window = document.defaultView ? document.defaultView : document.parentWindow

    if (!window[CANVAS_RENDERING_CONTEXT_2D]) {
        // this may happen when FlashCanvas.initElement is called from parent fram on a canvas in child frame
        // child frame's `window` will not have the canvas methods
        this[initWindow](window)
    }

    // initialize lock
    var canvasId        = getUniqueId();
    var objectId        = OBJECT_ID_PREFIX + canvasId;
    isReady[canvasId]   = false;
    images[canvasId]    = {};
    lock[canvasId]      = 1;
    callbacks[canvasId] = {};

    this.registeredEvents[canvasId] = []

    // Set the width and height attributes.
    setCanvasSize(canvas);

    var swfUrl = getSwfUrl(window)

    // on iframes with src = 'about:blank' location.protocol is "about:"
    // so, let's not go crafty nuts about this:
    var protocol = window.location.protocol === 'https:' ? 'https:' : 'http:'
    // embed swf and SPAN element
    canvas.innerHTML =
        '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"' +
        ' codebase="' + protocol + '//fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0"' +
        ' width="100%" height="100%" id="' + objectId + '">' +
        '<param name="allowScriptAccess" value="always">' +
        '<param name="flashvars" value="id=' + objectId + '">' +
        '<param name="wmode" value="transparent">' +
        // '<param name="movie" value="'+swfUrl+'">'
        '</object>' +
        '<span style="margin:0;padding:0;border:0;display:inline-block;position:static;height:1em;overflow:visible;white-space:nowrap">' +
        '</span>';

    this[canvasesProp][canvasId] = canvas;
    var swf = canvas.firstChild;
    spans[canvasId] = canvas.lastChild;

    // Check whether the canvas element is in the DOM tree
    var documentContains = document.body.contains;
    if (documentContains(canvas)) {
        // Load swf file immediately
        swf["movie"] = swfUrl;
    } else {
        // Wait until the element is added to the DOM tree
        var intervalId = window.setInterval(function() {
            if (documentContains(canvas)) {
                window.clearInterval(intervalId);
                swf["movie"] = swfUrl;
            }
        }, 2);
    }

    // If the browser is IE6 or in quirks mode
    if (document.compatMode === "BackCompat" || !window.XMLHttpRequest) {
        spans[canvasId].style.overflow = "hidden";
    }

    // initialize context
    var ctx = new CanvasRenderingContext2D(canvas, swf);

    // canvas API
    canvas.getContext = function(contextId) {
        return contextId === "2d" ? ctx : NULL;
    };

    canvas.toDataURL = function(type, quality) {
        if (("" + type).toLowerCase() === "image/jpeg") {
            ctx._queue.push(
                properties.toDataURL
                , type
                , typeof quality === "number" ? quality : ""
            )
        } else {
            ctx._queue.push(properties.toDataURL, type);
        }
        return ctx._executeCommand();
    };

    // the events handler functions are declared within initElement because
    // when it is inited against an iframe, the "window" object points
    // elswhere. Thus, we create new set of event handlers for each "window" 
    // In other words, "window" below is preset.

    // forward the event to the parent
    var onFocus = function(e) {
        var swf = e ? e.srcElement : window.event.srcElement
        , canvas = swf.parentNode
        swf.blur();
        canvas.focus();
    }

    this.registeredEvents[canvasId].push(
        [ON_FOCUS, onFocus]
    )

    // add event listener
    swf.attachEvent(ON_FOCUS, onFocus);

    return canvas;
}

FlashCanvas[saveImage] = function(canvas) {
    var swf = canvas.firstChild;
    swf[saveImage]();
}

FlashCanvas.setOptions = function(options) {
    // TODO: Implement
}

FlashCanvas[trigger] = function(canvasId, type) {
    var canvas = this[canvasesProp][canvasId];
    canvas.fireEvent("on" + type);
}

FlashCanvas[unlock] = function(canvasId, url, error) {

    try {
        
    var canvas, swf, width, height;
    var _callback, image, callback;
    var document, window

    // If Flash becomes ready
    if (url === undefined) {
        canvas = this[canvasesProp][canvasId];
        swf    = canvas.firstChild;

        // when init is called from parent frame over canvas sitting in child frame,
        // FlashCanvas does not pick up the right "window" or "document" - the one from child frame.
        // to avoid making the users specify window, document, we sniff them out from canvas element.
        document = canvas.ownerDocument
        window = document.defaultView ? document.defaultView : document.parentWindow

        // Set the width and height attributes of the canvas element.
        setCanvasSize(canvas);
        width  = canvas.width;
        height = canvas.height;

        canvas.style.width  = width  + "px";
        canvas.style.height = height + "px";

        // Adjust the size of Flash to that of the canvas
        if (width > 0) {
            swf.width = width;
        }
        if (height > 0) {
            swf.height = height;
        }
        swf.resize(width, height);

        // the events handler functions are declared within initElement because
        // when it is inited against an iframe, the "window" object points
        // elswhere. Thus, we create new set of event handlers for each "window" 
        // In other words, "window" below is NOT resolved runtime. It's preset.

        var onPropertyChange = function(e) {
            var e = e ? e : window.event
            , prop = e.propertyName

            if (prop === "width" || prop === "height") {
                var canvas = e.srcElement;
                var value  = canvas[prop];
                var number = parseInt(value, 10);

                if (isNaN(number) || number < 0) {
                    number = (prop === "width") ? 300 : 150;
                }

                if (value === number) {
                    canvas.style[prop] = number + "px";
                    canvas.getContext("2d")._resize(canvas.width, canvas.height);
                } else {
                    canvas[prop] = number;
                }
            }
        }

        this.registeredEvents[canvasId].push(
            [ON_PROPERTY_CHANGE, onPropertyChange]
        )

        // Add event listener
        canvas.attachEvent(ON_PROPERTY_CHANGE, onPropertyChange);

        // ExternalInterface is now ready for use
        isReady[canvasId] = true;

        // Call the onload event handler
        if (typeof canvas.onload === "function") {
            window.setTimeout(function() {
                canvas.onload();
            }, 0);
        }
    }

    // If callback functions were defined
    else if (_callback = callbacks[canvasId][url]) {
        image    = _callback[0];
        callback = _callback[1 + error];
        delete callbacks[canvasId][url];

        // Call the onload or onerror callback function.
        if (typeof callback === "function") {
            callback.call(image);
        }
    }

    if (lock[canvasId]) {
        --lock[canvasId];
    }

    } catch (ex) {
        // .unlock is called from within try catch inside flash. We never see errors if we don't
        // capture and display them.
        console.log("Call to FlashCanvas.unlock had thrown an error: ", ex.message)
        throw ex
    }

}


/*
 * Utility methods
 */

// Get a unique ID composed of alphanumeric characters.
function getUniqueId() {
    return Math.random().toString(36).slice(2) || "0";
}

// Escape characters not permitted in XML.
function encodeXML(str) {
    return ("" + str).replace(/&/g, "&amp;").replace(/</g, "&lt;");
}

function throwException(code) {
    throw new DOMException(code);
}

// The width and height attributes of a canvas element must have values that
// are valid non-negative integers.
function setCanvasSize(canvas) {
    var width  = parseInt(canvas.width, 10);
    var height = parseInt(canvas.height, 10);

    if (isNaN(width) || width < 0) {
        width = 300;
    }
    if (isNaN(height) || height < 0) {
        height = 150;
    }

    canvas.width  = width;
    canvas.height = height;
}

/*
 * initialization
 */

FlashCanvas.initWindow(window, document)

// Prevent Closure Compiler from removing the function.
keep = [
    CanvasRenderingContext2D.measureText,
    CanvasRenderingContext2D.loadImage
];

}).call(window);

}

/*

jSignature v2 "2018-11-06T13:56" "commit ID 89c22b348ab2e1d92a928d8fd992f175e8bc5cbd"
Copyright (c) 2012 Willow Systems Corp http://willow-systems.com
Copyright (c) 2010 Brinley Ang http://www.unbolt.net
MIT License <http://www.opensource.org/licenses/mit-license.php>


Simplify.js BSD 
(c) 2012, Vladimir Agafonkin
mourner.github.com/simplify-js


base64 encoder
MIT, GPL
http://phpjs.org/functions/base64_encode
+   original by: Tyler Akins (http://rumkin.com)
+   improved by: Bayron Guevara
+   improved by: Thunder.m
+   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
+   bugfixed by: Pellentesque Malesuada
+   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
+   improved by: Rafal Kukawski (http://kukawski.pl)


jSignature v2 jSignature's Undo Button and undo functionality plugin


jSignature v2 jSignature's custom "base30" format export and import plugins.


jSignature v2 SVG export plugin.

*/
(function(){function q(a){var b=a.css("color"),c;a=a[0];for(var g=!1;a&&!c&&!g;){try{var d=$(a).css("background-color")}catch(l){d="transparent"}"transparent"!==d&&"rgba(0, 0, 0, 0)"!==d&&(c=d);g=a.body;a=a.parentNode}a=/rgb[a]*\((\d+),\s*(\d+),\s*(\d+)/;g=/#([AaBbCcDdEeFf\d]{2})([AaBbCcDdEeFf\d]{2})([AaBbCcDdEeFf\d]{2})/;d=void 0;if(d=b.match(a))var m={r:parseInt(d[1],10),g:parseInt(d[2],10),b:parseInt(d[3],10)};else(d=b.match(g))&&(m={r:parseInt(d[1],16),g:parseInt(d[2],16),b:parseInt(d[3],16)});
if(c)if(d=void 0,d=c.match(a))var e={r:parseInt(d[1],10),g:parseInt(d[2],10),b:parseInt(d[3],10)};else(d=c.match(g))&&(e={r:parseInt(d[1],16),g:parseInt(d[2],16),b:parseInt(d[3],16)});else e=m?127<Math.max.apply(null,[m.r,m.g,m.b])?{r:0,g:0,b:0}:{r:255,g:255,b:255}:{r:255,g:255,b:255};d=function(a){return"rgb("+[a.r,a.g,a.b].join(", ")+")"};m&&e?(a=Math.max.apply(null,[m.r,m.g,m.b]),m=Math.max.apply(null,[e.r,e.g,e.b]),m=Math.round(m+-.75*(m-a)),m={r:m,g:m,b:m}):m?(m=Math.max.apply(null,[m.r,m.g,
m.b]),a=1,127<m&&(a=-1),m=Math.round(m+96*a),m={r:m,g:m,b:m}):m={r:191,g:191,b:191};return{color:b,"background-color":e?d(e):c,"decor-color":d(m)}}function k(a,b){this.x=a;this.y=b;this.reverse=function(){return new this.constructor(-1*this.x,-1*this.y)};this._length=null;this.getLength=function(){this._length||(this._length=Math.sqrt(Math.pow(this.x,2)+Math.pow(this.y,2)));return this._length};var c=function(a){return Math.round(a/Math.abs(a))};this.resizeTo=function(a){if(0===this.x&&0===this.y)this._length=
0;else if(0===this.x)this._length=a,this.y=a*c(this.y);else if(0===this.y)this._length=a,this.x=a*c(this.x);else{var b=Math.abs(this.y/this.x),g=Math.sqrt(Math.pow(a,2)/(1+Math.pow(b,2)));b*=g;this._length=a;this.x=g*c(this.x);this.y=b*c(this.y)}return this};this.angleTo=function(a){var b=this.getLength()*a.getLength();return 0===b?0:Math.acos(Math.min(Math.max((this.x*a.x+this.y*a.y)/b,-1),1))/Math.PI}}function h(a,b){this.x=a;this.y=b;this.getVectorToCoordinates=function(a,b){return new k(a-this.x,
b-this.y)};this.getVectorFromCoordinates=function(a,b){return this.getVectorToCoordinates(a,b).reverse()};this.getVectorToPoint=function(a){return new k(a.x-this.x,a.y-this.y)};this.getVectorFromPoint=function(a){return this.getVectorToPoint(a).reverse()}}function p(a,b,c,g,d){this.data=a;this.context=b;if(a.length)for(var m=a.length,e,l,f=0;f<m;f++){e=a[f];l=e.x.length;c.call(b,e);for(var t=1;t<l;t++)g.call(b,e,t);d.call(b,e)}this.changed=function(){};this.startStrokeFn=c;this.addToStrokeFn=g;this.endStrokeFn=
d;this.inStroke=!1;this._stroke=this._lastPoint=null;this.startStroke=function(a){if(a&&"number"==typeof a.x&&"number"==typeof a.y){this._stroke={x:[a.x],y:[a.y]};this.data.push(this._stroke);this._lastPoint=a;this.inStroke=!0;var b=this._stroke,c=this.startStrokeFn,d=this.context;setTimeout(function(){c.call(d,b)},3);return a}return null};this.addToStroke=function(a){if(this.inStroke&&"number"===typeof a.x&&"number"===typeof a.y&&4<Math.abs(a.x-this._lastPoint.x)+Math.abs(a.y-this._lastPoint.y)){var b=
this._stroke.x.length;this._stroke.x.push(a.x);this._stroke.y.push(a.y);this._lastPoint=a;var c=this._stroke,d=this.addToStrokeFn,g=this.context;setTimeout(function(){d.call(g,c,b)},3);return a}return null};this.endStroke=function(){var a=this.inStroke;this.inStroke=!1;this._lastPoint=null;if(a){var b=this._stroke,c=this.endStrokeFn,d=this.context,g=this.changed;setTimeout(function(){c.call(d,b);g.call(d)},3);return!0}return null}}function n(a,b,c,g){if("ratio"===b||"%"===b.split("")[b.length-1])this.eventTokens[c+
".parentresized"]=g.subscribe(c+".parentresized",function(b,m,e,l){return function(){var d=m.width();if(d!==e){for(var l in b)b.hasOwnProperty(l)&&(g.unsubscribe(b[l]),delete b[l]);var f=a.settings;a.$parent.children().remove();for(l in a)a.hasOwnProperty(l)&&delete a[l];l=f.data;d=1*d/e;var r=[],D,E;var h=0;for(D=l.length;h<D;h++){var k=l[h];var n={x:[],y:[]};var p=0;for(E=k.x.length;p<E;p++)n.x.push(k.x[p]*d),n.y.push(k.y[p]*d);r.push(n)}f.data=r;m[c](f)}}}(this.eventTokens,this.$parent,this.$parent.width(),
1*this.canvas.width/this.canvas.height))}function w(a,b,c){var g=this.$parent=$(a);a=this.eventTokens={};this.events=new u(this);var d=$.fn.jSignature("globalEvents"),e={width:"ratio",height:"ratio",sizeRatio:4,color:"#000","background-color":"#fff","decor-color":"#eee",lineWidth:0,minFatFingerCompensation:-10,showUndoButton:!1,readOnly:!1,data:[],signatureLine:!1};$.extend(e,q(g));b&&$.extend(e,b);this.settings=e;for(var f in c)c.hasOwnProperty(f)&&c[f].call(this,f);this.events.publish("jSignature.initializing");
this.$controlbarUpper=$('<div style="padding:0 !important; margin:0 !important;width: 100% !important; height: 0 !important; -ms-touch-action: none; touch-action: none;margin-top:-1em !important; margin-bottom:1em !important;"></div>').appendTo(g);this.isCanvasEmulator=!1;b=this.canvas=this.initializeCanvas(e);c=$(b);this.$controlbarLower=$('<div style="padding:0 !important; margin:0 !important;width: 100% !important; height: 0 !important; -ms-touch-action: none; touch-action: none;margin-top:-1.5em !important; margin-bottom:1.5em !important; position: relative;"></div>').appendTo(g);
this.canvasContext=b.getContext("2d");c.data("jSignature.this",this);e.lineWidth=function(a,b){return a?a:Math.max(Math.round(b/400),2)}(e.lineWidth,b.width);this.lineCurveThreshold=3*e.lineWidth;e.cssclass&&""!=$.trim(e.cssclass)&&c.addClass(e.cssclass);this.fatFingerCompensation=0;g=function(a){var b,c,d=function(d){d=d.changedTouches&&0<d.changedTouches.length?d.changedTouches[0]:d;return new h(Math.round(d.pageX+b),Math.round(d.pageY+c)+a.fatFingerCompensation)},g=new v(750,function(){a.dataEngine.endStroke()});
this.drawEndHandler=function(b){if(!a.settings.readOnly){try{b.preventDefault()}catch(A){}g.clear();a.dataEngine.endStroke()}};this.drawStartHandler=function(e){if(!a.settings.readOnly){e.preventDefault();var m=$(a.canvas).offset();b=-1*m.left;c=-1*m.top;a.dataEngine.startStroke(d(e));g.kick()}};this.drawMoveHandler=function(b){a.settings.readOnly||(b.preventDefault(),a.dataEngine.inStroke&&(a.dataEngine.addToStroke(d(b)),g.kick()))};return this}.call({},this);(function(a,b,c){var d=this.canvas,g=
$(d);if(this.isCanvasEmulator)g.bind("mousemove.jSignature",c),g.bind("mouseup.jSignature",a),g.bind("mousedown.jSignature",b);else{var m="function"===typeof d.addEventListener;this.ontouchstart=function(g){d.onmousedown=d.onmouseup=d.onmousemove=void 0;this.fatFingerCompensation=e.minFatFingerCompensation&&-3*e.lineWidth>e.minFatFingerCompensation?-3*e.lineWidth:e.minFatFingerCompensation;b(g);m?(d.addEventListener("touchend",a),d.addEventListener("touchstart",b),d.addEventListener("touchmove",c)):
(d.ontouchend=a,d.ontouchstart=b,d.ontouchmove=c)};m?d.addEventListener("touchstart",this.ontouchstart):d.ontouchstart=ontouchstart;d.onmousedown=function(g){m?d.removeEventListener("touchstart",this.ontouchstart):d.ontouchstart=d.ontouchend=d.ontouchmove=void 0;b(g);d.onmousedown=b;d.onmouseup=a;d.onmousemove=c};window.navigator.msPointerEnabled&&(d.onmspointerdown=b,d.onmspointerup=a,d.onmspointermove=c)}}).call(this,g.drawEndHandler,g.drawStartHandler,g.drawMoveHandler);a["jSignature.windowmouseup"]=
d.subscribe("jSignature.windowmouseup",g.drawEndHandler);this.events.publish("jSignature.attachingEventHandlers");n.call(this,this,e.width.toString(10),"jSignature",d);this.resetCanvas(e.data);this.events.publish("jSignature.initialized");return this}function x(a){if(a.getContext)return!1;var b=a.ownerDocument.parentWindow,c=b.FlashCanvas?a.ownerDocument.parentWindow.FlashCanvas:"undefined"===typeof FlashCanvas?void 0:FlashCanvas;if(c){a=c.initElement(a);c=1;b&&b.screen&&b.screen.deviceXDPI&&b.screen.logicalXDPI&&
(c=1*b.screen.deviceXDPI/b.screen.logicalXDPI);if(1!==c)try{$(a).children("object").get(0).resize(Math.ceil(a.width*c),Math.ceil(a.height*c)),a.getContext("2d").scale(c,c)}catch(g){}return!0}throw Error("Canvas element does not support 2d context. jSignature cannot proceed.");}var v=function(a,b){var c;this.kick=function(){clearTimeout(c);c=setTimeout(b,a)};this.clear=function(){clearTimeout(c)};return this},u=function(a){this.topics={};this.context=a?a:this;this.publish=function(a,c,g,d){if(this.topics[a]){var b=
this.topics[a],e=Array.prototype.slice.call(arguments,1),f=[],h=[],t;var k=0;for(t=b.length;k<t;k++){var r=b[k];var D=r[0];r[1]&&(r[0]=function(){},f.push(k));h.push(D)}k=0;for(t=f.length;k<t;k++)b.splice(f[k],1);k=0;for(t=h.length;k<t;k++)h[k].apply(this.context,e)}};this.subscribe=function(a,c,g){this.topics[a]?this.topics[a].push([c,g]):this.topics[a]=[[c,g]];return{topic:a,callback:c}};this.unsubscribe=function(a){if(this.topics[a.topic])for(var b=this.topics[a.topic],g=0,d=b.length;g<d;g++)b[g]&&
b[g][0]===a.callback&&b.splice(g,1)}},y=function(a,b,c,g,d){a.beginPath();a.moveTo(b,c);a.lineTo(g,d);a.closePath();a.stroke()},C=function(a){var b=this.canvasContext,c=a.x[0];a=a.y[0];var g=this.settings.lineWidth,d=b.fillStyle;b.fillStyle=b.strokeStyle;b.fillRect(c+g/-2,a+g/-2,g,g);b.fillStyle=d},f=function(a,b){var c=new h(a.x[b-1],a.y[b-1]),g=new h(a.x[b],a.y[b]),d=c.getVectorToPoint(g);if(1<b){var e=new h(a.x[b-2],a.y[b-2]),f=e.getVectorToPoint(c);if(f.getLength()>this.lineCurveThreshold){var l=
2<b?(new h(a.x[b-3],a.y[b-3])).getVectorToPoint(e):new k(0,0);var n=.35*f.getLength(),t=f.angleTo(l.reverse()),p=d.angleTo(f.reverse());l=(new k(l.x+f.x,l.y+f.y)).resizeTo(Math.max(.05,t)*n);var r=(new k(f.x+d.x,f.y+d.y)).reverse().resizeTo(Math.max(.05,p)*n);f=this.canvasContext;n=e.x;p=e.y;t=c.x;var D=c.y,A=e.x+l.x;e=e.y+l.y;l=c.x+r.x;r=c.y+r.y;f.beginPath();f.moveTo(n,p);f.bezierCurveTo(A,e,l,r,t,D);f.closePath();f.stroke()}}d.getLength()<=this.lineCurveThreshold&&y(this.canvasContext,c.x,c.y,
g.x,g.y)},e=function(a){var b=a.x.length-1;if(0<b){var c=new h(a.x[b],a.y[b]),e=new h(a.x[b-1],a.y[b-1]),d=e.getVectorToPoint(c);if(d.getLength()>this.lineCurveThreshold)if(1<b){a=(new h(a.x[b-2],a.y[b-2])).getVectorToPoint(e);var f=(new k(a.x+d.x,a.y+d.y)).resizeTo(d.getLength()/2);d=this.canvasContext;a=e.x;b=e.y;var E=c.x,l=c.y,n=e.x+f.x;e=e.y+f.y;f=c.x;c=c.y;d.beginPath();d.moveTo(a,b);d.bezierCurveTo(n,e,f,c,E,l);d.closePath();d.stroke()}else y(this.canvasContext,e.x,e.y,c.x,c.y)}};w.prototype.resetCanvas=
function(a,b){var c=this.canvas,g=this.settings,d=this.canvasContext,m=this.isCanvasEmulator,h=c.width,l=c.height;b||d.clearRect(0,0,h+30,l+30);d.shadowColor=d.fillStyle=g["background-color"];m&&d.fillRect(0,0,h+30,l+30);d.lineWidth=Math.ceil(parseInt(g.lineWidth,10));d.lineCap=d.lineJoin="round";if(g.signatureLine){if(null!=g["decor-color"]){d.strokeStyle=g["decor-color"];d.shadowOffsetX=0;d.shadowOffsetY=0;var k=Math.round(l/5);y(d,1.5*k,l-k,h-1.5*k,l-k)}m||(d.shadowColor=d.strokeStyle,d.shadowOffsetX=
.5*d.lineWidth,d.shadowOffsetY=-.6*d.lineWidth,d.shadowBlur=0)}d.strokeStyle=g.color;a||(a=[]);d=this.dataEngine=new p(a,this,C,f,e);g.data=a;$(c).data("jSignature.data",a).data("jSignature.settings",g);d.changed=function(a,b,d){return function(){b.publish(d+".change");a.trigger("change")}}(this.$parent,this.events,"jSignature");d.changed();return!0};w.prototype.initializeCanvas=function(a){var b=document.createElement("canvas"),c=$(b);a.width===a.height&&"ratio"===a.height&&(a.width="100%");c.css({margin:0,
padding:0,border:"none",height:"ratio"!==a.height&&a.height?a.height.toString(10):1,width:"ratio"!==a.width&&a.width?a.width.toString(10):1,"-ms-touch-action":"none","touch-action":"none","background-color":a["background-color"]});c.appendTo(this.$parent);"ratio"===a.height?c.css("height",Math.round(c.width()/a.sizeRatio)):"ratio"===a.width&&c.css("width",Math.round(c.height()*a.sizeRatio));c.addClass("jSignature");b.width=c.width();b.height=c.height();this.isCanvasEmulator=x(b);b.onselectstart=function(a){a&&
a.preventDefault&&a.preventDefault();a&&a.stopPropagation&&a.stopPropagation();return!1};return b};(function(a){function b(a,b,d){var c=new Image,e=this;c.onload=function(){var a=e.getContext("2d"),b=a.shadowColor;a.shadowColor="transparent";a.drawImage(c,0,0,c.width<e.width?c.width:e.width,c.height<e.height?c.height:e.height);a.shadowColor=b};c.src="data:"+b+","+a}function c(a,b){this.find("canvas.jSignature").add(this.filter("canvas.jSignature")).data("jSignature.this").resetCanvas(a,b);return this}
function e(a,b){if(void 0===b&&"string"===typeof a&&"data:"===a.substr(0,5)&&(b=a.slice(5).split(",")[0],a=a.slice(6+b.length),b===a))return;var c=this.find("canvas.jSignature").add(this.filter("canvas.jSignature"));if(l.hasOwnProperty(b))0!==c.length&&l[b].call(c[0],a,b,function(a){return function(){return a.resetCanvas.apply(a,arguments)}}(c.data("jSignature.this")));else throw Error("jSignature is unable to find import plugin with for format '"+String(b)+"'");return this}var d=new u;(function(a,
b,c,d){var e,g=function(){a.publish(b+".parentresized")};c(d).bind("resize."+b,function(){e&&clearTimeout(e);e=setTimeout(g,500)}).bind("mouseup."+b,function(c){a.publish(b+".windowmouseup")})})(d,"jSignature",$,a);var f={},h={"default":function(a){return this.toDataURL()},"native":function(a){return a},image:function(a){a=this.toDataURL();if("string"===typeof a&&4<a.length&&"data:"===a.slice(0,5)&&-1!==a.indexOf(",")){var b=a.indexOf(",");return[a.slice(5,b),a.substr(b+1)]}return[]}},l={"native":function(a,
b,c){c(a)},image:b,"image/png;base64":b,"image/jpeg;base64":b,"image/jpg;base64":b},k=function(a){var b=!1;for(a=a.parentNode;a&&!b;)b=a.body,a=a.parentNode;return!b},n={"export":h,"import":l,instance:f},p={init:function(a){return this.each(function(){k(this)||new w(this,a,f)})},destroy:function(){return this.each(function(){if(!k(this)){var a=$(this).find("canvas").data("jSignature.this");if(a){a.$controlbarLower.remove();a.$controlbarUpper.remove();$(a.canvas).remove();for(var b in a.eventTokens)a.eventTokens.hasOwnProperty(b)&&
d.unsubscribe(a.eventTokens[b])}}})},getSettings:function(){return this.find("canvas.jSignature").add(this.filter("canvas.jSignature")).data("jSignature.this").settings},isModified:function(){return null!==this.find("canvas.jSignature").add(this.filter("canvas.jSignature")).data("jSignature.this").dataEngine._stroke},updateSetting:function(a,b,c){var d=this.find("canvas.jSignature").add(this.filter("canvas.jSignature")).data("jSignature.this");d.settings[a]=b;d.resetCanvas(c?null:d.settings.data,
!0);return d.settings[a]},clear:c,reset:c,addPlugin:function(a,b,c){n.hasOwnProperty(a)&&(n[a][b]=c);return this},listPlugins:function(a){var b=[];if(n.hasOwnProperty(a)){a=n[a];for(var c in a)a.hasOwnProperty(c)&&b.push(c)}return b},getData:function(a){var b=this.find("canvas.jSignature").add(this.filter("canvas.jSignature"));void 0===a&&(a="default");if(0!==b.length&&h.hasOwnProperty(a))return h[a].call(b.get(0),b.data("jSignature.data"),b.data("jSignature.settings"))},importData:e,setData:e,globalEvents:function(){return d},
disable:function(){this.find("input").attr("disabled",1);this.find("canvas.jSignature").addClass("disabled").data("jSignature.this").settings.readOnly=!0},enable:function(){this.find("input").removeAttr("disabled");this.find("canvas.jSignature").removeClass("disabled").data("jSignature.this").settings.readOnly=!1},events:function(){return this.find("canvas.jSignature").add(this.filter("canvas.jSignature")).data("jSignature.this").events}};$.fn.jSignature=function(a){if(a&&"object"!==typeof a){if("string"===
typeof a&&p[a])return p[a].apply(this,Array.prototype.slice.call(arguments,1));$.error("Method "+String(a)+" does not exist on jQuery.jSignature")}else return p.init.apply(this,arguments)}})(window)})();
(function(){function q(k,h,p){k=k.call(this);(function(h,k,p){h.events.subscribe(p+".change",function(){h.dataEngine.data.length?k.show():k.hide()})})(this,k,h);(function(h,k,p){var n=p+".undo";k.bind("click",function(){h.events.publish(n)});h.events.subscribe(n,function(){var k=h.dataEngine.data;k.length&&(k.pop(),h.resetCanvas(k))})})(this,k,this.events.topics.hasOwnProperty(h+".undo")?p:h)}$.fn.jSignature("addPlugin","instance","UndoButton",function(k){this.events.subscribe("jSignature.attachingEventHandlers",
function(){if(this.settings[k]){var h=this.settings[k];"function"!==typeof h&&(h=function(){var h=$('<input type="button" value="Undo last stroke" style="position:absolute;display:none;margin:0 !important;top:auto" />').appendTo(this.$controlbarLower),k=h.width();h.css("left",Math.round((this.canvas.width-k)/2));k!==h.width()&&h.width(k);return h});q.call(this,h,"jSignature",k)}})})})();
(function(){for(var q={},k={},h="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWX".split(""),p=h.length/2,n=p-1;-1<n;n--)q[h[n]]=h[n+p],k[h[n+p]]=h[n];var w=function(e){e=e.split("");for(var a=e.length,b=1;b<a;b++)e[b]=q[e[b]];return e.join("")},x=function(e){for(var a=[],b=0,c=1,g=e.length,d,f,h=0;h<g;h++)d=Math.round(e[h]),f=d-b,b=d,0>f&&0<c?(c=-1,a.push("Z")):0<f&&0>c&&(c=1,a.push("Y")),d=Math.abs(f),d>=p?a.push(w(d.toString(p))):a.push(d.toString(p));return a.join("")},v=function(e){var a=
[];e=e.split("");for(var b=e.length,c,g=1,d=[],f=0,h=0;h<b;h++)c=e[h],c in q||"Z"===c||"Y"===c?(0!==d.length&&(d=parseInt(d.join(""),p)*g+f,a.push(d),f=d),"Z"===c?(g=-1,d=[]):"Y"===c?(g=1,d=[]):d=[c]):d.push(k[c]);a.push(parseInt(d.join(""),p)*g+f);return a},u=function(e){for(var a=[],b=e.length,c,g=0;g<b;g++)c=e[g],a.push(x(c.x)),a.push(x(c.y));return a.join("_")},y=function(e){var a=[];e=e.split("_");for(var b=e.length/2,c=0;c<b;c++)a.push({x:v(e[2*c]),y:v(e[2*c+1])});return a},C=function(e){return["image/jsignature;base30",
u(e)]},f=function(e,a,b){"string"===typeof e&&("image/jsignature;base30"===e.substring(0,23).toLowerCase()&&(e=e.substring(24)),b(y(e)))};if(null==this.jQuery)throw Error("We need jQuery for some of the functionality. jQuery is not detected. Failing to initialize...");(function(e){e=e.fn.jSignature;e("addPlugin","export","base30",C);e("addPlugin","export","image/jsignature;base30",C);e("addPlugin","import","base30",f);e("addPlugin","import","image/jsignature;base30",f)})(this.jQuery);this.jSignatureDebug&&
(this.jSignatureDebug.base30={remapTailChars:w,compressstrokeleg:x,uncompressstrokeleg:v,compressstrokes:u,uncompressstrokes:y,charmap:q})}).call("undefined"!==typeof window?window:this);
(function(){function q(f,e){this.x=f;this.y=e;this.reverse=function(){return new this.constructor(-1*this.x,-1*this.y)};this._length=null;this.getLength=function(){this._length||(this._length=Math.sqrt(Math.pow(this.x,2)+Math.pow(this.y,2)));return this._length};var a=function(a){return Math.round(a/Math.abs(a))};this.resizeTo=function(b){if(0===this.x&&0===this.y)this._length=0;else if(0===this.x)this._length=b,this.y=b*a(this.y);else if(0===this.y)this._length=b,this.x=b*a(this.x);else{var c=Math.abs(this.y/
this.x),e=Math.sqrt(Math.pow(b,2)/(1+Math.pow(c,2)));c*=e;this._length=b;this.x=e*a(this.x);this.y=c*a(this.y)}return this};this.angleTo=function(a){var b=this.getLength()*a.getLength();return 0===b?0:Math.acos(Math.min(Math.max((this.x*a.x+this.y*a.y)/b,-1),1))/Math.PI}}function k(f,e){this.x=f;this.y=e;this.getVectorToCoordinates=function(a,b){return new q(a-this.x,b-this.y)};this.getVectorFromCoordinates=function(a,b){return this.getVectorToCoordinates(a,b).reverse()};this.getVectorToPoint=function(a){return new q(a.x-
this.x,a.y-this.y)};this.getVectorFromPoint=function(a){return this.getVectorToPoint(a).reverse()}}function h(f,e){var a=Math.pow(10,e);return Math.round(f*a)/a}function p(f,e,a){e+=1;var b=new k(f.x[e-1],f.y[e-1]),c=new k(f.x[e],f.y[e]);c=b.getVectorToPoint(c);var g=new k(f.x[e-2],f.y[e-2]);b=g.getVectorToPoint(b);return b.getLength()>a?(a=2<e?(new k(f.x[e-3],f.y[e-3])).getVectorToPoint(g):new q(0,0),f=.35*b.getLength(),g=b.angleTo(a.reverse()),e=c.angleTo(b.reverse()),a=(new q(a.x+b.x,a.y+b.y)).resizeTo(Math.max(.05,
g)*f),c=(new q(b.x+c.x,b.y+c.y)).reverse().resizeTo(Math.max(.05,e)*f),c=new q(b.x+c.x,b.y+c.y),["c",h(a.x,2),h(a.y,2),h(c.x,2),h(c.y,2),h(b.x,2),h(b.y,2)]):["l",h(b.x,2),h(b.y,2)]}function n(f,e){var a=f.x.length-1,b=new k(f.x[a],f.y[a]),c=new k(f.x[a-1],f.y[a-1]);b=c.getVectorToPoint(b);if(1<a&&b.getLength()>e){a=(new k(f.x[a-2],f.y[a-2])).getVectorToPoint(c);c=b.angleTo(a.reverse());var g=.35*b.getLength();a=(new q(a.x+b.x,a.y+b.y)).resizeTo(Math.max(.05,c)*g);return["c",h(a.x,2),h(a.y,2),h(b.x,
2),h(b.y,2),h(b.x,2),h(b.y,2)]}return["l",h(b.x,2),h(b.y,2)]}function w(f,e,a){e=["M",h(f.x[0]-e,2),h(f.y[0]-a,2)];a=1;for(var b=f.x.length-1;a<b;a++)e.push.apply(e,p(f,a,1));0<b?e.push.apply(e,n(f,a,1)):0===b&&e.push.apply(e,["l",1,1]);return e.join(" ")}function x(f){for(var e=[],a=[["fill",void 0,"none"],["stroke","color","#000000"],["stroke-width","lineWidth",2],["stroke-linecap",void 0,"round"],["stroke-linejoin",void 0,"round"]],b=a.length-1;0<=b;b--){var c=a[b][1],g=a[b][2];e.push(a[b][0]+
'="'+(c in f&&f[c]?f[c]:g)+'"')}return e.join(" ")}function v(f,e){var a=['<?xml version="1.0" encoding="UTF-8" standalone="no"?>','<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'],b,c=f.length,g,d=[],h=[],k=g=b=0,l=0,p=[];if(0!==c){for(b=0;b<c;b++){g=f[b];var n=[],q={x:[],y:[]};l=0;for(k=g.x.length;l<k;l++)n.push({x:g.x[l],y:g.y[l]});n=simplify(n,.7,!0);l=0;for(k=n.length;l<k;l++)q.x.push(n[l].x),q.y.push(n[l].y);g=q;p.push(g);d=d.concat(g.x);h=
h.concat(g.y)}c=Math.min.apply(null,d)-1;b=Math.max.apply(null,d)+1;d=Math.min.apply(null,h)-1;h=Math.max.apply(null,h)+1;k=0>c?0:c;l=0>d?0:d;b-=c;g=h-d}a.push('<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="'+b.toString()+'" height="'+g.toString()+'">');b=0;for(c=p.length;b<c;b++)g=p[b],a.push("<path "+x(e)+' d="'+w(g,k,l)+'"/>');a.push("</svg>");return a.join("")}function u(f,e){return["image/svg+xml",v(f,e)]}function y(f,e){return["image/svg+xml;base64",C(v(f,e))]}(function(f,e){"use strict";
f.simplify=function(a,b,c){b=b!==e?b*b:1;if(!c){var g=a.length,d=a[0],f=[d];for(c=1;c<g;c++){var h=a[c];var k=h.x-d.x,n=h.y-d.y;k*k+n*n>b&&(f.push(h),d=h)}a=(d!==h&&f.push(h),f)}h=a;c=h.length;g=new (typeof Uint8Array!=e+""?Uint8Array:Array)(c);d=0;f=c-1;var p,q=[],r=[],y=[];for(g[d]=g[f]=1;f;){n=0;for(k=d+1;k<f;k++){var A=h[k];var z=h[d],w=h[f],u=z.x,v=z.y;z=w.x-u;var B=w.y-v;if(0!==z||0!==B){var x=((A.x-u)*z+(A.y-v)*B)/(z*z+B*B);1<x?(u=w.x,v=w.y):0<x&&(u+=z*x,v+=B*x)}A=(z=A.x-u,B=A.y-v,z*z+B*B);
A>n&&(p=k,n=A)}n>b&&(g[p]=1,q.push(d),r.push(p),q.push(p),r.push(f));d=q.pop();f=r.pop()}for(k=0;k<c;k++)g[k]&&y.push(h[k]);return a=y,a}})(window);if("function"!==typeof C)var C=function(f){var e="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=".split(""),a=0,b=0,c=[];do{var g=f.charCodeAt(a++);var d=f.charCodeAt(a++);var h=f.charCodeAt(a++);var k=g<<16|d<<8|h;g=k>>18&63;d=k>>12&63;h=k>>6&63;k&=63;c[b++]=e[g]+e[d]+e[h]+e[k]}while(a<f.length);e=c.join("");f=f.length%3;return(f?e.slice(0,
f-3):e)+"===".slice(f||3)};if("undefined"===typeof $)throw Error("We need jQuery for some of the functionality. jQuery is not detected. Failing to initialize...");(function(f){f=f.fn.jSignature;f("addPlugin","export","svg",u);f("addPlugin","export","image/svg+xml",u);f("addPlugin","export","svgbase64",y);f("addPlugin","export","image/svg+xml;base64",y)})($)})();
!function(){"use strict";function e(e){return JSON.parse(JSON.stringify(e))}function t(e){for(var t=y(e);"ÿà"<=t[1].slice(0,2)&&t[1].slice(0,2)<="ÿï";)t=[t[0]].concat(t.slice(2));return t.join("")}function a(e){return s(">"+p("B",e.length),e)}function i(e){return s(">"+p("H",e.length),e)}function n(e){return s(">"+p("L",e.length),e)}function r(e,t,r){var o,l,m,y,c="",S="";if("Byte"==t)o=e.length,4>=o?S=a(e)+p("\x00",4-o):(S=s(">L",[r]),c=a(e));else if("Short"==t)o=e.length,2>=o?S=i(e)+p("\x00\x00",2-o):(S=s(">L",[r]),c=i(e));else if("Long"==t)o=e.length,1>=o?S=n(e):(S=s(">L",[r]),c=n(e));else if("Ascii"==t)l=e+"\x00",o=l.length,o>4?(S=s(">L",[r]),c=l):S=l+p("\x00",4-o);else if("Rational"==t){if("number"==typeof e[0])o=1,m=e[0],y=e[1],l=s(">L",[m])+s(">L",[y]);else{o=e.length,l="";for(var f=0;o>f;f++)m=e[f][0],y=e[f][1],l+=s(">L",[m])+s(">L",[y])}S=s(">L",[r]),c=l}else if("SRational"==t){if("number"==typeof e[0])o=1,m=e[0],y=e[1],l=s(">l",[m])+s(">l",[y]);else{o=e.length,l="";for(var f=0;o>f;f++)m=e[f][0],y=e[f][1],l+=s(">l",[m])+s(">l",[y])}S=s(">L",[r]),c=l}else"Undefined"==t&&(o=e.length,o>4?(S=s(">L",[r]),c=e):S=e+p("\x00",4-o));var h=s(">L",[o]);return[h,S,c]}function o(e,t,a){var i,n=8,o=Object.keys(e).length,l=s(">H",[o]);i=["0th","1st"].indexOf(t)>-1?2+12*o+4:2+12*o;var m,p="",y="";for(var m in e)if("string"==typeof m&&(m=parseInt(m)),!("0th"==t&&[34665,34853].indexOf(m)>-1||"Exif"==t&&40965==m||"1st"==t&&[513,514].indexOf(m)>-1)){var c=e[m],S=s(">H",[m]),f=u[t][m].type,h=s(">H",[g[f]]);"number"==typeof c&&(c=[c]);var d=n+i+a+y.length,P=r(c,f,d),C=P[0],R=P[1],L=P[2];p+=S+h+C+R,y+=L}return[l+p,y]}function l(e){var t,a;if("ÿØ"==e.slice(0,2))t=y(e),a=c(t),a?this.tiftag=a.slice(10):this.tiftag=null;else if(["II","MM"].indexOf(e.slice(0,2))>-1)this.tiftag=e;else{if("Exif"!=e.slice(0,4))throw"Given file is neither JPEG nor TIFF.";this.tiftag=e.slice(6)}}function s(e,t){if(!(t instanceof Array))throw"'pack' error. Got invalid type argument.";if(e.length-1!=t.length)throw"'pack' error. "+(e.length-1)+" marks, "+t.length+" elements.";var a;if("<"==e[0])a=!0;else{if(">"!=e[0])throw"";a=!1}for(var i="",n=1,r=null,o=null,l=null;o=e[n];){if("b"==o.toLowerCase()){if(r=t[n-1],"b"==o&&0>r&&(r+=256),r>255||0>r)throw"'pack' error.";l=String.fromCharCode(r)}else if("H"==o){if(r=t[n-1],r>65535||0>r)throw"'pack' error.";l=String.fromCharCode(Math.floor(r%65536/256))+String.fromCharCode(r%256),a&&(l=l.split("").reverse().join(""))}else{if("l"!=o.toLowerCase())throw"'pack' error.";if(r=t[n-1],"l"==o&&0>r&&(r+=4294967296),r>4294967295||0>r)throw"'pack' error.";l=String.fromCharCode(Math.floor(r/16777216))+String.fromCharCode(Math.floor(r%16777216/65536))+String.fromCharCode(Math.floor(r%65536/256))+String.fromCharCode(r%256),a&&(l=l.split("").reverse().join(""))}i+=l,n+=1}return i}function m(e,t){if("string"!=typeof t)throw"'unpack' error. Got invalid type argument.";for(var a=0,i=1;i<e.length;i++)if("b"==e[i].toLowerCase())a+=1;else if("h"==e[i].toLowerCase())a+=2;else{if("l"!=e[i].toLowerCase())throw"'unpack' error. Got invalid mark.";a+=4}if(a!=t.length)throw"'unpack' error. Mismatch between symbol and string length. "+a+":"+t.length;var n;if("<"==e[0])n=!0;else{if(">"!=e[0])throw"'unpack' error.";n=!1}for(var r=[],o=0,l=1,s=null,m=null,p=null,y="";m=e[l];){if("b"==m.toLowerCase())p=1,y=t.slice(o,o+p),s=y.charCodeAt(0),"b"==m&&s>=128&&(s-=256);else if("H"==m)p=2,y=t.slice(o,o+p),n&&(y=y.split("").reverse().join("")),s=256*y.charCodeAt(0)+y.charCodeAt(1);else{if("l"!=m.toLowerCase())throw"'unpack' error. "+m;p=4,y=t.slice(o,o+p),n&&(y=y.split("").reverse().join("")),s=16777216*y.charCodeAt(0)+65536*y.charCodeAt(1)+256*y.charCodeAt(2)+y.charCodeAt(3),"l"==m&&s>=2147483648&&(s-=4294967296)}r.push(s),o+=p,l+=1}return r}function p(e,t){for(var a="",i=0;t>i;i++)a+=e;return a}function y(e){if("ÿØ"!=e.slice(0,2))throw"Given data isn't JPEG.";for(var t=2,a=["ÿØ"];;){if("ÿÚ"==e.slice(t,t+2)){a.push(e.slice(t));break}var i=m(">H",e.slice(t+2,t+4))[0],n=t+i+2;if(a.push(e.slice(t,n)),t=n,t>=e.length)throw"Wrong JPEG data."}return a}function c(e){for(var t,a=0;a<e.length;a++)if(t=e[a],"ÿá"==t.slice(0,2)&&"Exif\x00\x00"==t.slice(4,10))return t;return null}function S(e,t){return"ÿà"==e[1].slice(0,2)&&"ÿá"==e[2].slice(0,2)&&"Exif\x00\x00"==e[2].slice(4,10)?t?(e[2]=t,e=["ÿØ"].concat(e.slice(2))):e=null==t?e.slice(0,2).concat(e.slice(3)):e.slice(0).concat(e.slice(2)):"ÿà"==e[1].slice(0,2)?t&&(e[1]=t):"ÿá"==e[1].slice(0,2)&&"Exif\x00\x00"==e[1].slice(4,10)?t?e[1]=t:null==t&&(e=e.slice(0).concat(e.slice(2))):t&&(e=[e[0],t].concat(e.slice(1))),e.join("")}var f={};if(f.version="1.03",f.remove=function(e){var t=!1;if("ÿØ"==e.slice(0,2));else{if("data:image/jpeg;base64,"!=e.slice(0,23)&&"data:image/jpg;base64,"!=e.slice(0,22))throw"Given data is not jpeg.";e=d(e.split(",")[1]),t=!0}var a=y(e);if("ÿá"==a[1].slice(0,2)&&"Exif\x00\x00"==a[1].slice(4,10))a=[a[0]].concat(a.slice(2));else{if("ÿá"!=a[2].slice(0,2)||"Exif\x00\x00"!=a[2].slice(4,10))throw"Exif not found.";a=a.slice(0,2).concat(a.slice(3))}var i=a.join("");return t&&(i="data:image/jpeg;base64,"+h(i)),i},f.insert=function(e,t){var a=!1;if("Exif\x00\x00"!=e.slice(0,6))throw"Given data is not exif.";if("ÿØ"==t.slice(0,2));else{if("data:image/jpeg;base64,"!=t.slice(0,23)&&"data:image/jpg;base64,"!=t.slice(0,22))throw"Given data is not jpeg.";t=d(t.split(",")[1]),a=!0}var i="ÿá"+s(">H",[e.length+2])+e,n=y(t),r=S(n,i);return a&&(r="data:image/jpeg;base64,"+h(r)),r},f.load=function(e){var t;if("string"!=typeof e)throw"'load' gots invalid type argument.";if("ÿØ"==e.slice(0,2))t=e;else if("data:image/jpeg;base64,"==e.slice(0,23)||"data:image/jpg;base64,"==e.slice(0,22))t=d(e.split(",")[1]);else{if("Exif"!=e.slice(0,4))throw"'load' gots invalid file data.";t=e.slice(6)}var a={"0th":{},Exif:{},GPS:{},Interop:{},"1st":{},thumbnail:null},i=new l(t);if(null===i.tiftag)return a;"II"==i.tiftag.slice(0,2)?i.endian_mark="<":i.endian_mark=">";var n=m(i.endian_mark+"L",i.tiftag.slice(4,8))[0];a["0th"]=i.get_ifd(n,"0th");var r=a["0th"].first_ifd_pointer;if(delete a["0th"].first_ifd_pointer,34665 in a["0th"]&&(n=a["0th"][34665],a.Exif=i.get_ifd(n,"Exif")),34853 in a["0th"]&&(n=a["0th"][34853],a.GPS=i.get_ifd(n,"GPS")),40965 in a.Exif&&(n=a.Exif[40965],a.Interop=i.get_ifd(n,"Interop")),"\x00\x00\x00\x00"!=r&&(n=m(i.endian_mark+"L",r)[0],a["1st"]=i.get_ifd(n,"1st"),513 in a["1st"]&&514 in a["1st"])){var o=a["1st"][513]+a["1st"][514],s=i.tiftag.slice(a["1st"][513],o);a.thumbnail=s}return a},f.dump=function(a){var i,n,r,l,m,p=8,y=e(a),c="Exif\x00\x00MM\x00*\x00\x00\x00\b",S=!1,h=!1,d=!1,u=!1;i="0th"in y?y["0th"]:{},"Exif"in y&&Object.keys(y.Exif).length||"Interop"in y&&Object.keys(y.Interop).length?(i[34665]=1,S=!0,n=y.Exif,"Interop"in y&&Object.keys(y.Interop).length?(n[40965]=1,d=!0,r=y.Interop):Object.keys(n).indexOf(f.ExifIFD.InteroperabilityTag.toString())>-1&&delete n[40965]):Object.keys(i).indexOf(f.ImageIFD.ExifTag.toString())>-1&&delete i[34665],"GPS"in y&&Object.keys(y.GPS).length?(i[f.ImageIFD.GPSTag]=1,h=!0,l=y.GPS):Object.keys(i).indexOf(f.ImageIFD.GPSTag.toString())>-1&&delete i[f.ImageIFD.GPSTag],"1st"in y&&"thumbnail"in y&&null!=y.thumbnail&&(u=!0,y["1st"][513]=1,y["1st"][514]=1,m=y["1st"]);var P,C,R,L,x,I=o(i,"0th",0),D=I[0].length+12*S+12*h+4+I[1].length,G="",A=0,v="",b=0,T="",k=0,w="";if(S&&(P=o(n,"Exif",D),A=P[0].length+12*d+P[1].length),h&&(C=o(l,"GPS",D+A),v=C.join(""),b=v.length),d){var F=D+A+b;R=o(r,"Interop",F),T=R.join(""),k=T.length}if(u){var F=D+A+b+k;if(L=o(m,"1st",F),x=t(y.thumbnail),x.length>64e3)throw"Given thumbnail is too large. max 64kB"}var B="",E="",M="",O="\x00\x00\x00\x00";if(S){var N=p+D,U=s(">L",[N]),_=34665,H=s(">H",[_]),j=s(">H",[g.Long]),V=s(">L",[1]);B=H+j+V+U}if(h){var N=p+D+A,U=s(">L",[N]),_=34853,H=s(">H",[_]),j=s(">H",[g.Long]),V=s(">L",[1]);E=H+j+V+U}if(d){var N=p+D+A+b,U=s(">L",[N]),_=40965,H=s(">H",[_]),j=s(">H",[g.Long]),V=s(">L",[1]);M=H+j+V+U}if(u){var N=p+D+A+b+k;O=s(">L",[N]);var J=N+L[0].length+24+4+L[1].length,X="\x00\x00\x00\x00"+s(">L",[J]),z="\x00\x00\x00\x00"+s(">L",[x.length]);w=L[0]+X+z+"\x00\x00\x00\x00"+L[1]+x}var Y=I[0]+B+E+O+I[1];return S&&(G=P[0]+M+P[1]),c+Y+G+v+T+w},l.prototype={get_ifd:function(e,t){var a,i={},n=m(this.endian_mark+"H",this.tiftag.slice(e,e+2))[0],r=e+2;a=["0th","1st"].indexOf(t)>-1?"Image":t;for(var o=0;n>o;o++){e=r+12*o;var l=m(this.endian_mark+"H",this.tiftag.slice(e,e+2))[0],s=m(this.endian_mark+"H",this.tiftag.slice(e+2,e+4))[0],p=m(this.endian_mark+"L",this.tiftag.slice(e+4,e+8))[0],y=this.tiftag.slice(e+8,e+12),c=[s,p,y];l in u[a]&&(i[l]=this.convert_value(c))}return"0th"==t&&(e=r+12*n,i.first_ifd_pointer=this.tiftag.slice(e,e+4)),i},convert_value:function(e){var t,a=null,i=e[0],n=e[1],r=e[2];if(1==i)n>4?(t=m(this.endian_mark+"L",r)[0],a=m(this.endian_mark+p("B",n),this.tiftag.slice(t,t+n))):a=m(this.endian_mark+p("B",n),r.slice(0,n));else if(2==i)n>4?(t=m(this.endian_mark+"L",r)[0],a=this.tiftag.slice(t,t+n-1)):a=r.slice(0,n-1);else if(3==i)n>2?(t=m(this.endian_mark+"L",r)[0],a=m(this.endian_mark+p("H",n),this.tiftag.slice(t,t+2*n))):a=m(this.endian_mark+p("H",n),r.slice(0,2*n));else if(4==i)n>1?(t=m(this.endian_mark+"L",r)[0],a=m(this.endian_mark+p("L",n),this.tiftag.slice(t,t+4*n))):a=m(this.endian_mark+p("L",n),r);else if(5==i)if(t=m(this.endian_mark+"L",r)[0],n>1){a=[];for(var o=0;n>o;o++)a.push([m(this.endian_mark+"L",this.tiftag.slice(t+8*o,t+4+8*o))[0],m(this.endian_mark+"L",this.tiftag.slice(t+4+8*o,t+8+8*o))[0]])}else a=[m(this.endian_mark+"L",this.tiftag.slice(t,t+4))[0],m(this.endian_mark+"L",this.tiftag.slice(t+4,t+8))[0]];else if(7==i)n>4?(t=m(this.endian_mark+"L",r)[0],a=this.tiftag.slice(t,t+n)):a=r.slice(0,n);else{if(10!=i)throw"Exif might be wrong. Got incorrect value type to decode. type:"+i;if(t=m(this.endian_mark+"L",r)[0],n>1){a=[];for(var o=0;n>o;o++)a.push([m(this.endian_mark+"l",this.tiftag.slice(t+8*o,t+4+8*o))[0],m(this.endian_mark+"l",this.tiftag.slice(t+4+8*o,t+8+8*o))[0]])}else a=[m(this.endian_mark+"l",this.tiftag.slice(t,t+4))[0],m(this.endian_mark+"l",this.tiftag.slice(t+4,t+8))[0]]}return a instanceof Array&&1==a.length?a[0]:a}},"undefined"!=typeof window&&"function"==typeof window.btoa)var h=window.btoa;if("undefined"==typeof h)var h=function(e){for(var t,a,i,n,r,o,l,s="",m=0,p="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";m<e.length;)t=e.charCodeAt(m++),a=e.charCodeAt(m++),i=e.charCodeAt(m++),n=t>>2,r=(3&t)<<4|a>>4,o=(15&a)<<2|i>>6,l=63&i,isNaN(a)?o=l=64:isNaN(i)&&(l=64),s=s+p.charAt(n)+p.charAt(r)+p.charAt(o)+p.charAt(l);return s};if("undefined"!=typeof window&&"function"==typeof window.atob)var d=window.atob;if("undefined"==typeof d)var d=function(e){var t,a,i,n,r,o,l,s="",m=0,p="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";for(e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");m<e.length;)n=p.indexOf(e.charAt(m++)),r=p.indexOf(e.charAt(m++)),o=p.indexOf(e.charAt(m++)),l=p.indexOf(e.charAt(m++)),t=n<<2|r>>4,a=(15&r)<<4|o>>2,i=(3&o)<<6|l,s+=String.fromCharCode(t),64!=o&&(s+=String.fromCharCode(a)),64!=l&&(s+=String.fromCharCode(i));return s};var g={Byte:1,Ascii:2,Short:3,Long:4,Rational:5,Undefined:7,SLong:9,SRational:10},u={Image:{11:{name:"ProcessingSoftware",type:"Ascii"},254:{name:"NewSubfileType",type:"Long"},255:{name:"SubfileType",type:"Short"},256:{name:"ImageWidth",type:"Long"},257:{name:"ImageLength",type:"Long"},258:{name:"BitsPerSample",type:"Short"},259:{name:"Compression",type:"Short"},262:{name:"PhotometricInterpretation",type:"Short"},263:{name:"Threshholding",type:"Short"},264:{name:"CellWidth",type:"Short"},265:{name:"CellLength",type:"Short"},266:{name:"FillOrder",type:"Short"},269:{name:"DocumentName",type:"Ascii"},270:{name:"ImageDescription",type:"Ascii"},271:{name:"Make",type:"Ascii"},272:{name:"Model",type:"Ascii"},273:{name:"StripOffsets",type:"Long"},274:{name:"Orientation",type:"Short"},277:{name:"SamplesPerPixel",type:"Short"},278:{name:"RowsPerStrip",type:"Long"},279:{name:"StripByteCounts",type:"Long"},282:{name:"XResolution",type:"Rational"},283:{name:"YResolution",type:"Rational"},284:{name:"PlanarConfiguration",type:"Short"},290:{name:"GrayResponseUnit",type:"Short"},291:{name:"GrayResponseCurve",type:"Short"},292:{name:"T4Options",type:"Long"},293:{name:"T6Options",type:"Long"},296:{name:"ResolutionUnit",type:"Short"},301:{name:"TransferFunction",type:"Short"},305:{name:"Software",type:"Ascii"},306:{name:"DateTime",type:"Ascii"},315:{name:"Artist",type:"Ascii"},316:{name:"HostComputer",type:"Ascii"},317:{name:"Predictor",type:"Short"},318:{name:"WhitePoint",type:"Rational"},319:{name:"PrimaryChromaticities",type:"Rational"},320:{name:"ColorMap",type:"Short"},321:{name:"HalftoneHints",type:"Short"},322:{name:"TileWidth",type:"Short"},323:{name:"TileLength",type:"Short"},324:{name:"TileOffsets",type:"Short"},325:{name:"TileByteCounts",type:"Short"},330:{name:"SubIFDs",type:"Long"},332:{name:"InkSet",type:"Short"},333:{name:"InkNames",type:"Ascii"},334:{name:"NumberOfInks",type:"Short"},336:{name:"DotRange",type:"Byte"},337:{name:"TargetPrinter",type:"Ascii"},338:{name:"ExtraSamples",type:"Short"},339:{name:"SampleFormat",type:"Short"},340:{name:"SMinSampleValue",type:"Short"},341:{name:"SMaxSampleValue",type:"Short"},342:{name:"TransferRange",type:"Short"},343:{name:"ClipPath",type:"Byte"},344:{name:"XClipPathUnits",type:"Long"},345:{name:"YClipPathUnits",type:"Long"},346:{name:"Indexed",type:"Short"},347:{name:"JPEGTables",type:"Undefined"},351:{name:"OPIProxy",type:"Short"},512:{name:"JPEGProc",type:"Long"},513:{name:"JPEGInterchangeFormat",type:"Long"},514:{name:"JPEGInterchangeFormatLength",type:"Long"},515:{name:"JPEGRestartInterval",type:"Short"},517:{name:"JPEGLosslessPredictors",type:"Short"},518:{name:"JPEGPointTransforms",type:"Short"},519:{name:"JPEGQTables",type:"Long"},520:{name:"JPEGDCTables",type:"Long"},521:{name:"JPEGACTables",type:"Long"},529:{name:"YCbCrCoefficients",type:"Rational"},530:{name:"YCbCrSubSampling",type:"Short"},531:{name:"YCbCrPositioning",type:"Short"},532:{name:"ReferenceBlackWhite",type:"Rational"},700:{name:"XMLPacket",type:"Byte"},18246:{name:"Rating",type:"Short"},18249:{name:"RatingPercent",type:"Short"},32781:{name:"ImageID",type:"Ascii"},33421:{name:"CFARepeatPatternDim",type:"Short"},33422:{name:"CFAPattern",type:"Byte"},33423:{name:"BatteryLevel",type:"Rational"},33432:{name:"Copyright",type:"Ascii"},33434:{name:"ExposureTime",type:"Rational"},34377:{name:"ImageResources",type:"Byte"},34665:{name:"ExifTag",type:"Long"},34675:{name:"InterColorProfile",type:"Undefined"},34853:{name:"GPSTag",type:"Long"},34857:{name:"Interlace",type:"Short"},34858:{name:"TimeZoneOffset",type:"Long"},34859:{name:"SelfTimerMode",type:"Short"},37387:{name:"FlashEnergy",type:"Rational"},37388:{name:"SpatialFrequencyResponse",type:"Undefined"},37389:{name:"Noise",type:"Undefined"},37390:{name:"FocalPlaneXResolution",type:"Rational"},37391:{name:"FocalPlaneYResolution",type:"Rational"},37392:{name:"FocalPlaneResolutionUnit",type:"Short"},37393:{name:"ImageNumber",type:"Long"},37394:{name:"SecurityClassification",type:"Ascii"},37395:{name:"ImageHistory",type:"Ascii"},37397:{name:"ExposureIndex",type:"Rational"},37398:{name:"TIFFEPStandardID",type:"Byte"},37399:{name:"SensingMethod",type:"Short"},40091:{name:"XPTitle",type:"Byte"},40092:{name:"XPComment",type:"Byte"},40093:{name:"XPAuthor",type:"Byte"},40094:{name:"XPKeywords",type:"Byte"},40095:{name:"XPSubject",type:"Byte"},50341:{name:"PrintImageMatching",type:"Undefined"},50706:{name:"DNGVersion",type:"Byte"},50707:{name:"DNGBackwardVersion",type:"Byte"},50708:{name:"UniqueCameraModel",type:"Ascii"},50709:{name:"LocalizedCameraModel",type:"Byte"},50710:{name:"CFAPlaneColor",type:"Byte"},50711:{name:"CFALayout",type:"Short"},50712:{name:"LinearizationTable",type:"Short"},50713:{name:"BlackLevelRepeatDim",type:"Short"},50714:{name:"BlackLevel",type:"Rational"},50715:{name:"BlackLevelDeltaH",type:"SRational"},50716:{name:"BlackLevelDeltaV",type:"SRational"},50717:{name:"WhiteLevel",type:"Short"},50718:{name:"DefaultScale",type:"Rational"},50719:{name:"DefaultCropOrigin",type:"Short"},50720:{name:"DefaultCropSize",type:"Short"},50721:{name:"ColorMatrix1",type:"SRational"},50722:{name:"ColorMatrix2",type:"SRational"},50723:{name:"CameraCalibration1",type:"SRational"},50724:{name:"CameraCalibration2",type:"SRational"},50725:{name:"ReductionMatrix1",type:"SRational"},50726:{name:"ReductionMatrix2",type:"SRational"},50727:{name:"AnalogBalance",type:"Rational"},50728:{name:"AsShotNeutral",type:"Short"},50729:{name:"AsShotWhiteXY",type:"Rational"},50730:{name:"BaselineExposure",type:"SRational"},50731:{name:"BaselineNoise",type:"Rational"},50732:{name:"BaselineSharpness",type:"Rational"},50733:{name:"BayerGreenSplit",type:"Long"},50734:{name:"LinearResponseLimit",type:"Rational"},50735:{name:"CameraSerialNumber",type:"Ascii"},50736:{name:"LensInfo",type:"Rational"},50737:{name:"ChromaBlurRadius",type:"Rational"},50738:{name:"AntiAliasStrength",type:"Rational"},50739:{name:"ShadowScale",type:"SRational"},50740:{name:"DNGPrivateData",type:"Byte"},50741:{name:"MakerNoteSafety",type:"Short"},50778:{name:"CalibrationIlluminant1",type:"Short"},50779:{name:"CalibrationIlluminant2",type:"Short"},50780:{name:"BestQualityScale",type:"Rational"},50781:{name:"RawDataUniqueID",type:"Byte"},50827:{name:"OriginalRawFileName",type:"Byte"},50828:{name:"OriginalRawFileData",type:"Undefined"},50829:{name:"ActiveArea",type:"Short"},50830:{name:"MaskedAreas",type:"Short"},50831:{name:"AsShotICCProfile",type:"Undefined"},50832:{name:"AsShotPreProfileMatrix",type:"SRational"},50833:{name:"CurrentICCProfile",type:"Undefined"},50834:{name:"CurrentPreProfileMatrix",type:"SRational"},50879:{name:"ColorimetricReference",type:"Short"},50931:{name:"CameraCalibrationSignature",type:"Byte"},50932:{name:"ProfileCalibrationSignature",type:"Byte"},50934:{name:"AsShotProfileName",type:"Byte"},50935:{name:"NoiseReductionApplied",type:"Rational"},50936:{name:"ProfileName",type:"Byte"},50937:{name:"ProfileHueSatMapDims",type:"Long"},50938:{name:"ProfileHueSatMapData1",type:"Float"},50939:{name:"ProfileHueSatMapData2",type:"Float"},50940:{name:"ProfileToneCurve",type:"Float"},50941:{name:"ProfileEmbedPolicy",type:"Long"},50942:{name:"ProfileCopyright",type:"Byte"},50964:{name:"ForwardMatrix1",type:"SRational"},50965:{name:"ForwardMatrix2",type:"SRational"},50966:{name:"PreviewApplicationName",type:"Byte"},50967:{name:"PreviewApplicationVersion",type:"Byte"},50968:{name:"PreviewSettingsName",type:"Byte"},50969:{name:"PreviewSettingsDigest",type:"Byte"},50970:{name:"PreviewColorSpace",type:"Long"},50971:{name:"PreviewDateTime",type:"Ascii"},50972:{name:"RawImageDigest",type:"Undefined"},50973:{name:"OriginalRawFileDigest",type:"Undefined"},50974:{name:"SubTileBlockSize",type:"Long"},50975:{name:"RowInterleaveFactor",type:"Long"},50981:{name:"ProfileLookTableDims",type:"Long"},50982:{name:"ProfileLookTableData",type:"Float"},51008:{name:"OpcodeList1",type:"Undefined"},51009:{name:"OpcodeList2",type:"Undefined"},51022:{name:"OpcodeList3",type:"Undefined"}},Exif:{33434:{name:"ExposureTime",type:"Rational"},33437:{name:"FNumber",type:"Rational"},34850:{name:"ExposureProgram",type:"Short"},34852:{name:"SpectralSensitivity",type:"Ascii"},34855:{name:"ISOSpeedRatings",type:"Short"},34856:{name:"OECF",type:"Undefined"},34864:{name:"SensitivityType",type:"Short"},34865:{name:"StandardOutputSensitivity",type:"Long"},34866:{name:"RecommendedExposureIndex",type:"Long"},34867:{name:"ISOSpeed",type:"Long"},34868:{name:"ISOSpeedLatitudeyyy",type:"Long"},34869:{name:"ISOSpeedLatitudezzz",type:"Long"},36864:{name:"ExifVersion",type:"Undefined"},36867:{name:"DateTimeOriginal",type:"Ascii"},36868:{name:"DateTimeDigitized",type:"Ascii"},37121:{name:"ComponentsConfiguration",type:"Undefined"},37122:{name:"CompressedBitsPerPixel",type:"Rational"},37377:{name:"ShutterSpeedValue",type:"SRational"},37378:{name:"ApertureValue",type:"Rational"},37379:{name:"BrightnessValue",type:"SRational"},37380:{name:"ExposureBiasValue",type:"SRational"},37381:{name:"MaxApertureValue",type:"Rational"},37382:{name:"SubjectDistance",type:"Rational"},37383:{name:"MeteringMode",type:"Short"},37384:{name:"LightSource",type:"Short"},37385:{name:"Flash",type:"Short"},37386:{name:"FocalLength",type:"Rational"},37396:{name:"SubjectArea",type:"Short"},37500:{name:"MakerNote",type:"Undefined"},37510:{name:"UserComment",type:"Ascii"},37520:{name:"SubSecTime",type:"Ascii"},37521:{name:"SubSecTimeOriginal",type:"Ascii"},37522:{name:"SubSecTimeDigitized",type:"Ascii"},40960:{name:"FlashpixVersion",type:"Undefined"},40961:{name:"ColorSpace",type:"Short"},40962:{name:"PixelXDimension",type:"Long"},40963:{name:"PixelYDimension",type:"Long"},40964:{name:"RelatedSoundFile",type:"Ascii"},40965:{name:"InteroperabilityTag",type:"Long"},41483:{name:"FlashEnergy",type:"Rational"},41484:{name:"SpatialFrequencyResponse",type:"Undefined"},41486:{name:"FocalPlaneXResolution",type:"Rational"},41487:{name:"FocalPlaneYResolution",type:"Rational"},41488:{name:"FocalPlaneResolutionUnit",type:"Short"},41492:{name:"SubjectLocation",type:"Short"},41493:{name:"ExposureIndex",type:"Rational"},41495:{name:"SensingMethod",type:"Short"},41728:{name:"FileSource",type:"Undefined"},41729:{name:"SceneType",type:"Undefined"},41730:{name:"CFAPattern",type:"Undefined"},41985:{name:"CustomRendered",type:"Short"},41986:{name:"ExposureMode",type:"Short"},41987:{name:"WhiteBalance",type:"Short"},41988:{name:"DigitalZoomRatio",type:"Rational"},41989:{name:"FocalLengthIn35mmFilm",type:"Short"},41990:{name:"SceneCaptureType",type:"Short"},41991:{name:"GainControl",type:"Short"},41992:{name:"Contrast",type:"Short"},41993:{name:"Saturation",type:"Short"},41994:{name:"Sharpness",type:"Short"},41995:{name:"DeviceSettingDescription",type:"Undefined"},41996:{name:"SubjectDistanceRange",type:"Short"},42016:{name:"ImageUniqueID",type:"Ascii"},42032:{name:"CameraOwnerName",type:"Ascii"},42033:{name:"BodySerialNumber",type:"Ascii"},42034:{name:"LensSpecification",type:"Rational"},42035:{name:"LensMake",type:"Ascii"},42036:{name:"LensModel",type:"Ascii"},42037:{name:"LensSerialNumber",type:"Ascii"},42240:{name:"Gamma",type:"Rational"}},GPS:{0:{name:"GPSVersionID",type:"Byte"},1:{name:"GPSLatitudeRef",type:"Ascii"},2:{name:"GPSLatitude",type:"Rational"},3:{name:"GPSLongitudeRef",type:"Ascii"},4:{name:"GPSLongitude",type:"Rational"},5:{name:"GPSAltitudeRef",type:"Byte"},6:{name:"GPSAltitude",type:"Rational"},7:{name:"GPSTimeStamp",type:"Rational"},8:{name:"GPSSatellites",type:"Ascii"},9:{name:"GPSStatus",type:"Ascii"},10:{name:"GPSMeasureMode",type:"Ascii"},11:{name:"GPSDOP",type:"Rational"},12:{name:"GPSSpeedRef",type:"Ascii"},13:{name:"GPSSpeed",type:"Rational"},14:{name:"GPSTrackRef",type:"Ascii"},15:{name:"GPSTrack",type:"Rational"},16:{name:"GPSImgDirectionRef",type:"Ascii"},17:{name:"GPSImgDirection",type:"Rational"},18:{name:"GPSMapDatum",type:"Ascii"},19:{name:"GPSDestLatitudeRef",type:"Ascii"},20:{name:"GPSDestLatitude",type:"Rational"},21:{name:"GPSDestLongitudeRef",type:"Ascii"},22:{name:"GPSDestLongitude",type:"Rational"},23:{name:"GPSDestBearingRef",type:"Ascii"},24:{name:"GPSDestBearing",type:"Rational"},25:{name:"GPSDestDistanceRef",type:"Ascii"},26:{name:"GPSDestDistance",type:"Rational"},27:{name:"GPSProcessingMethod",type:"Undefined"},28:{name:"GPSAreaInformation",type:"Undefined"},29:{name:"GPSDateStamp",type:"Ascii"},30:{name:"GPSDifferential",type:"Short"},31:{name:"GPSHPositioningError",type:"Rational"}},Interop:{1:{name:"InteroperabilityIndex",type:"Ascii"}}};u["0th"]=u.Image,u["1st"]=u.Image,f.TAGS=u,f.ImageIFD={ProcessingSoftware:11,NewSubfileType:254,SubfileType:255,ImageWidth:256,ImageLength:257,BitsPerSample:258,Compression:259,PhotometricInterpretation:262,Threshholding:263,CellWidth:264,CellLength:265,FillOrder:266,DocumentName:269,ImageDescription:270,Make:271,Model:272,StripOffsets:273,Orientation:274,SamplesPerPixel:277,RowsPerStrip:278,StripByteCounts:279,XResolution:282,YResolution:283,PlanarConfiguration:284,GrayResponseUnit:290,GrayResponseCurve:291,T4Options:292,T6Options:293,ResolutionUnit:296,TransferFunction:301,Software:305,DateTime:306,Artist:315,HostComputer:316,Predictor:317,WhitePoint:318,PrimaryChromaticities:319,ColorMap:320,HalftoneHints:321,TileWidth:322,TileLength:323,TileOffsets:324,TileByteCounts:325,SubIFDs:330,InkSet:332,InkNames:333,NumberOfInks:334,DotRange:336,TargetPrinter:337,ExtraSamples:338,SampleFormat:339,SMinSampleValue:340,SMaxSampleValue:341,TransferRange:342,ClipPath:343,XClipPathUnits:344,YClipPathUnits:345,Indexed:346,JPEGTables:347,OPIProxy:351,JPEGProc:512,JPEGInterchangeFormat:513,JPEGInterchangeFormatLength:514,JPEGRestartInterval:515,JPEGLosslessPredictors:517,JPEGPointTransforms:518,JPEGQTables:519,JPEGDCTables:520,JPEGACTables:521,YCbCrCoefficients:529,YCbCrSubSampling:530,YCbCrPositioning:531,ReferenceBlackWhite:532,XMLPacket:700,Rating:18246,RatingPercent:18249,ImageID:32781,CFARepeatPatternDim:33421,CFAPattern:33422,BatteryLevel:33423,Copyright:33432,ExposureTime:33434,ImageResources:34377,ExifTag:34665,InterColorProfile:34675,GPSTag:34853,Interlace:34857,TimeZoneOffset:34858,SelfTimerMode:34859,FlashEnergy:37387,SpatialFrequencyResponse:37388,Noise:37389,FocalPlaneXResolution:37390,FocalPlaneYResolution:37391,FocalPlaneResolutionUnit:37392,ImageNumber:37393,SecurityClassification:37394,ImageHistory:37395,ExposureIndex:37397,TIFFEPStandardID:37398,SensingMethod:37399,XPTitle:40091,XPComment:40092,XPAuthor:40093,XPKeywords:40094,XPSubject:40095,PrintImageMatching:50341,DNGVersion:50706,DNGBackwardVersion:50707,UniqueCameraModel:50708,LocalizedCameraModel:50709,CFAPlaneColor:50710,CFALayout:50711,LinearizationTable:50712,BlackLevelRepeatDim:50713,BlackLevel:50714,BlackLevelDeltaH:50715,BlackLevelDeltaV:50716,WhiteLevel:50717,DefaultScale:50718,DefaultCropOrigin:50719,DefaultCropSize:50720,ColorMatrix1:50721,ColorMatrix2:50722,CameraCalibration1:50723,CameraCalibration2:50724,ReductionMatrix1:50725,ReductionMatrix2:50726,AnalogBalance:50727,AsShotNeutral:50728,AsShotWhiteXY:50729,BaselineExposure:50730,BaselineNoise:50731,BaselineSharpness:50732,BayerGreenSplit:50733,LinearResponseLimit:50734,CameraSerialNumber:50735,LensInfo:50736,ChromaBlurRadius:50737,AntiAliasStrength:50738,ShadowScale:50739,DNGPrivateData:50740,MakerNoteSafety:50741,CalibrationIlluminant1:50778,CalibrationIlluminant2:50779,BestQualityScale:50780,RawDataUniqueID:50781,OriginalRawFileName:50827,OriginalRawFileData:50828,ActiveArea:50829,MaskedAreas:50830,AsShotICCProfile:50831,AsShotPreProfileMatrix:50832,CurrentICCProfile:50833,CurrentPreProfileMatrix:50834,ColorimetricReference:50879,CameraCalibrationSignature:50931,ProfileCalibrationSignature:50932,AsShotProfileName:50934,NoiseReductionApplied:50935,ProfileName:50936,ProfileHueSatMapDims:50937,ProfileHueSatMapData1:50938,ProfileHueSatMapData2:50939,ProfileToneCurve:50940,ProfileEmbedPolicy:50941,ProfileCopyright:50942,ForwardMatrix1:50964,ForwardMatrix2:50965,PreviewApplicationName:50966,PreviewApplicationVersion:50967,PreviewSettingsName:50968,PreviewSettingsDigest:50969,PreviewColorSpace:50970,PreviewDateTime:50971,RawImageDigest:50972,OriginalRawFileDigest:50973,SubTileBlockSize:50974,RowInterleaveFactor:50975,ProfileLookTableDims:50981,ProfileLookTableData:50982,OpcodeList1:51008,OpcodeList2:51009,OpcodeList3:51022,NoiseProfile:51041},f.ExifIFD={ExposureTime:33434,FNumber:33437,ExposureProgram:34850,SpectralSensitivity:34852,ISOSpeedRatings:34855,OECF:34856,SensitivityType:34864,StandardOutputSensitivity:34865,RecommendedExposureIndex:34866,ISOSpeed:34867,ISOSpeedLatitudeyyy:34868,ISOSpeedLatitudezzz:34869,ExifVersion:36864,DateTimeOriginal:36867,DateTimeDigitized:36868,ComponentsConfiguration:37121,CompressedBitsPerPixel:37122,ShutterSpeedValue:37377,ApertureValue:37378,BrightnessValue:37379,ExposureBiasValue:37380,MaxApertureValue:37381,SubjectDistance:37382,MeteringMode:37383,LightSource:37384,Flash:37385,FocalLength:37386,SubjectArea:37396,MakerNote:37500,UserComment:37510,SubSecTime:37520,SubSecTimeOriginal:37521,SubSecTimeDigitized:37522,FlashpixVersion:40960,ColorSpace:40961,PixelXDimension:40962,PixelYDimension:40963,RelatedSoundFile:40964,InteroperabilityTag:40965,FlashEnergy:41483,SpatialFrequencyResponse:41484,FocalPlaneXResolution:41486,FocalPlaneYResolution:41487,FocalPlaneResolutionUnit:41488,SubjectLocation:41492,ExposureIndex:41493,SensingMethod:41495,FileSource:41728,SceneType:41729,CFAPattern:41730,CustomRendered:41985,ExposureMode:41986,WhiteBalance:41987,DigitalZoomRatio:41988,FocalLengthIn35mmFilm:41989,SceneCaptureType:41990,GainControl:41991,Contrast:41992,Saturation:41993,Sharpness:41994,DeviceSettingDescription:41995,SubjectDistanceRange:41996,ImageUniqueID:42016,CameraOwnerName:42032,BodySerialNumber:42033,LensSpecification:42034,LensMake:42035,LensModel:42036,LensSerialNumber:42037,Gamma:42240},f.GPSIFD={GPSVersionID:0,GPSLatitudeRef:1,GPSLatitude:2,GPSLongitudeRef:3,GPSLongitude:4,GPSAltitudeRef:5,GPSAltitude:6,GPSTimeStamp:7,GPSSatellites:8,GPSStatus:9,GPSMeasureMode:10,GPSDOP:11,GPSSpeedRef:12,GPSSpeed:13,GPSTrackRef:14,GPSTrack:15,GPSImgDirectionRef:16,GPSImgDirection:17,GPSMapDatum:18,GPSDestLatitudeRef:19,GPSDestLatitude:20,GPSDestLongitudeRef:21,GPSDestLongitude:22,GPSDestBearingRef:23,GPSDestBearing:24,GPSDestDistanceRef:25,GPSDestDistance:26,GPSProcessingMethod:27,GPSAreaInformation:28,GPSDateStamp:29,GPSDifferential:30,GPSHPositioningError:31},f.InteropIFD={InteroperabilityIndex:1},f.GPSHelper={degToDmsRational:function(e){var t=e%1*60,a=t%1*60,i=Math.floor(e),n=Math.floor(t),r=Math.round(100*a);return[[i,1],[n,1],[r,100]]}},"undefined"!=typeof exports?("undefined"!=typeof module&&module.exports&&(exports=module.exports=f),exports.piexif=f):window.piexif=f}();
!function(t){"use strict";"function"==typeof define&&define.amd?define(t):"undefined"!=typeof module&&"undefined"!=typeof module.exports?module.exports=t():window.KvSortable=t()}(function(){"use strict";function t(t,e){if(!t||!t.nodeType||1!==t.nodeType)throw"KvSortable: `el` must be HTMLElement, and not "+{}.toString.call(t);this.el=t,this.options=e=_({},e),t[K]=this;var n={group:Math.random(),sort:!0,disabled:!1,store:null,handle:null,scroll:!0,scrollSensitivity:30,scrollSpeed:10,draggable:/[uo]l/i.test(t.nodeName)?"li":">*",ghostClass:"kvsortable-ghost",chosenClass:"kvsortable-chosen",dragClass:"kvsortable-drag",ignore:"a, img",filter:null,preventOnFilter:!0,animation:0,setData:function(t,e){t.setData("Text",e.textContent)},dropBubble:!1,dragoverBubble:!1,dataIdAttr:"data-id",delay:0,forceFallback:!1,fallbackClass:"kvsortable-fallback",fallbackOnBody:!1,fallbackTolerance:0,fallbackOffset:{x:0,y:0}};for(var i in n)!(i in e)&&(e[i]=n[i]);rt(e);for(var o in this)"_"===o.charAt(0)&&"function"==typeof this[o]&&(this[o]=this[o].bind(this));this.nativeDraggable=e.forceFallback?!1:J,a(t,"mousedown",this._onTapStart),a(t,"touchstart",this._onTapStart),a(t,"pointerdown",this._onTapStart),this.nativeDraggable&&(a(t,"dragover",this),a(t,"dragenter",this)),ot.push(this._onDragOver),e.store&&this.sort(e.store.get(this))}function e(t,e){"clone"!==t.lastPullMode&&(e=!0),S&&S.state!==e&&(l(S,"display",e?"none":""),e||S.state&&(t.options.group.revertClone?(E.insertBefore(S,x),t._animate(w,S)):E.insertBefore(S,w)),S.state=e)}function n(t,e,n){if(t){n=n||V;do if(">*"===e&&t.parentNode===n||m(t,e))return t;while(t=i(t))}return null}function i(t){var e=t.host;return e&&e.nodeType?e:t.parentNode}function o(t){t.dataTransfer&&(t.dataTransfer.dropEffect="move"),t.preventDefault()}function a(t,e,n){t.addEventListener(e,n,Z)}function r(t,e,n){t.removeEventListener(e,n,Z)}function s(t,e,n){if(t)if(t.classList)t.classList[n?"add":"remove"](e);else{var i=(" "+t.className+" ").replace(H," ").replace(" "+e+" "," ");t.className=(i+(n?" "+e:"")).replace(H," ")}}function l(t,e,n){var i=t&&t.style;if(i){if(void 0===n)return V.defaultView&&V.defaultView.getComputedStyle?n=V.defaultView.getComputedStyle(t,""):t.currentStyle&&(n=t.currentStyle),void 0===e?n:n[e];e in i||(e="-webkit-"+e),i[e]=n+("string"==typeof n?"":"px")}}function c(t,e,n){if(t){var i=t.getElementsByTagName(e),o=0,a=i.length;if(n)for(;a>o;o++)n(i[o],o);return i}return[]}function d(t,e,n,i,o,a,r){t=t||e[K];var s=V.createEvent("Event"),l=t.options,c="on"+n.charAt(0).toUpperCase()+n.substr(1);s.initEvent(n,!0,!0),s.to=e,s.from=o||e,s.item=i||e,s.clone=S,s.oldIndex=a,s.newIndex=r,e.dispatchEvent(s),l[c]&&l[c].call(t,s)}function h(t,e,n,i,o,a,r){var s,l,c=t[K],d=c.options.onMove;return s=V.createEvent("Event"),s.initEvent("move",!0,!0),s.to=e,s.from=t,s.dragged=n,s.draggedRect=i,s.related=o||e,s.relatedRect=a||e.getBoundingClientRect(),t.dispatchEvent(s),d&&(l=d.call(c,s,r)),l}function u(t){t.draggable=!1}function f(){tt=!1}function p(t,e){var n=t.lastElementChild,i=n.getBoundingClientRect();return(e.clientY-(i.top+i.height)>5||e.clientX-(i.right+i.width)>5)&&n}function g(t){for(var e=t.tagName+t.className+t.src+t.href+t.textContent,n=e.length,i=0;n--;)i+=e.charCodeAt(n);return i.toString(36)}function v(t,e){var n=0;if(!t||!t.parentNode)return-1;for(;t&&(t=t.previousElementSibling);)"TEMPLATE"===t.nodeName.toUpperCase()||">*"!==e&&!m(t,e)||n++;return n}function m(t,e){if(t){e=e.split(".");var n=e.shift().toUpperCase(),i=new RegExp("\\s("+e.join("|")+")(?=\\s)","g");return!(""!==n&&t.nodeName.toUpperCase()!=n||e.length&&((" "+t.className+" ").match(i)||[]).length!=e.length)}return!1}function b(t,e){var n,i;return function(){void 0===n&&(n=arguments,i=this,setTimeout(function(){1===n.length?t.call(i,n[0]):t.apply(i,n),n=void 0},e))}}function _(t,e){if(t&&e)for(var n in e)e.hasOwnProperty(n)&&(t[n]=e[n]);return t}function y(t){return G?G(t).clone(!0)[0]:Q&&Q.dom?Q.dom(t).cloneNode(!0):t.cloneNode(!0)}function D(t){for(var e=t.getElementsByTagName("input"),n=e.length;n--;){var i=e[n];i.checked&&it.push(i)}}if("undefined"==typeof window||!window.document)return function(){throw new Error("KvSortable.js requires a window with a document")};var w,T,C,S,E,x,k,N,B,O,Y,X,M,A,P,R,I,j,L,F,U={},H=/\s+/g,W=/left|right|inline/,K="KvSortable"+(new Date).getTime(),q=window,V=q.document,z=q.parseInt,G=q.jQuery||q.Zepto,Q=q.Polymer,Z=!1,J=!!("draggable"in V.createElement("div")),$=function(t){return navigator.userAgent.match(/Trident.*rv[ :]?11\./)?!1:(t=V.createElement("x"),t.style.cssText="pointer-events:auto","auto"===t.style.pointerEvents)}(),tt=!1,et=Math.abs,nt=Math.min,it=[],ot=[],at=b(function(t,e,n){if(n&&e.scroll){var i,o,a,r,s,l,c=n[K],d=e.scrollSensitivity,h=e.scrollSpeed,u=t.clientX,f=t.clientY,p=window.innerWidth,g=window.innerHeight;if(B!==n&&(N=e.scroll,B=n,O=e.scrollFn,N===!0)){N=n;do if(N.offsetWidth<N.scrollWidth||N.offsetHeight<N.scrollHeight)break;while(N=N.parentNode)}N&&(i=N,o=N.getBoundingClientRect(),a=(et(o.right-u)<=d)-(et(o.left-u)<=d),r=(et(o.bottom-f)<=d)-(et(o.top-f)<=d)),a||r||(a=(d>=p-u)-(d>=u),r=(d>=g-f)-(d>=f),(a||r)&&(i=q)),U.vx===a&&U.vy===r&&U.el===i||(U.el=i,U.vx=a,U.vy=r,clearInterval(U.pid),i&&(U.pid=setInterval(function(){return l=r?r*h:0,s=a?a*h:0,"function"==typeof O?O.call(c,s,l,t):void(i===q?q.scrollTo(q.pageXOffset+s,q.pageYOffset+l):(i.scrollTop+=l,i.scrollLeft+=s))},24)))}},30),rt=function(t){function e(t,e){return void 0!==t&&t!==!0||(t=n.name),"function"==typeof t?t:function(n,i){var o=i.options.group.name;return e?t:t&&(t.join?t.indexOf(o)>-1:o==t)}}var n={},i=t.group;i&&"object"==typeof i||(i={name:i}),n.name=i.name,n.checkPull=e(i.pull,!0),n.checkPut=e(i.put),n.revertClone=i.revertClone,t.group=n};t.prototype={constructor:t,_onTapStart:function(t){var e,i=this,o=this.el,a=this.options,r=a.preventOnFilter,s=t.type,l=t.touches&&t.touches[0],c=(l||t).target,h=t.target.shadowRoot&&t.path[0]||c,u=a.filter;if(D(o),!w&&!("mousedown"===s&&0!==t.button||a.disabled)&&(c=n(c,a.draggable,o),c&&k!==c)){if(e=v(c,a.draggable),"function"==typeof u){if(u.call(this,t,c,this))return d(i,h,"filter",c,o,e),void(r&&t.preventDefault())}else if(u&&(u=u.split(",").some(function(t){return t=n(h,t.trim(),o),t?(d(i,t,"filter",c,o,e),!0):void 0})))return void(r&&t.preventDefault());a.handle&&!n(h,a.handle,o)||this._prepareDragStart(t,l,c,e)}},_prepareDragStart:function(t,e,n,i){var o,r=this,l=r.el,h=r.options,f=l.ownerDocument;n&&!w&&n.parentNode===l&&(j=t,E=l,w=n,T=w.parentNode,x=w.nextSibling,k=n,R=h.group,A=i,this._lastX=(e||t).clientX,this._lastY=(e||t).clientY,w.style["will-change"]="transform",o=function(){r._disableDelayedDrag(),w.draggable=r.nativeDraggable,s(w,h.chosenClass,!0),r._triggerDragStart(t,e),d(r,E,"choose",w,E,A)},h.ignore.split(",").forEach(function(t){c(w,t.trim(),u)}),a(f,"mouseup",r._onDrop),a(f,"touchend",r._onDrop),a(f,"touchcancel",r._onDrop),a(f,"pointercancel",r._onDrop),a(f,"selectstart",r),h.delay?(a(f,"mouseup",r._disableDelayedDrag),a(f,"touchend",r._disableDelayedDrag),a(f,"touchcancel",r._disableDelayedDrag),a(f,"mousemove",r._disableDelayedDrag),a(f,"touchmove",r._disableDelayedDrag),a(f,"pointermove",r._disableDelayedDrag),r._dragStartTimer=setTimeout(o,h.delay)):o())},_disableDelayedDrag:function(){var t=this.el.ownerDocument;clearTimeout(this._dragStartTimer),r(t,"mouseup",this._disableDelayedDrag),r(t,"touchend",this._disableDelayedDrag),r(t,"touchcancel",this._disableDelayedDrag),r(t,"mousemove",this._disableDelayedDrag),r(t,"touchmove",this._disableDelayedDrag),r(t,"pointermove",this._disableDelayedDrag)},_triggerDragStart:function(t,e){e=e||("touch"==t.pointerType?t:null),e?(j={target:w,clientX:e.clientX,clientY:e.clientY},this._onDragStart(j,"touch")):this.nativeDraggable?(a(w,"dragend",this),a(E,"dragstart",this._onDragStart)):this._onDragStart(j,!0);try{V.selection?setTimeout(function(){V.selection.empty()}):window.getSelection().removeAllRanges()}catch(n){}},_dragStarted:function(){if(E&&w){var e=this.options;s(w,e.ghostClass,!0),s(w,e.dragClass,!1),t.active=this,d(this,E,"start",w,E,A)}else this._nulling()},_emulateDragOver:function(){if(L){if(this._lastX===L.clientX&&this._lastY===L.clientY)return;this._lastX=L.clientX,this._lastY=L.clientY,$||l(C,"display","none");var t=V.elementFromPoint(L.clientX,L.clientY),e=t,n=ot.length;if(e)do{if(e[K]){for(;n--;)ot[n]({clientX:L.clientX,clientY:L.clientY,target:t,rootEl:e});break}t=e}while(e=e.parentNode);$||l(C,"display","")}},_onTouchMove:function(e){if(j){var n=this.options,i=n.fallbackTolerance,o=n.fallbackOffset,a=e.touches?e.touches[0]:e,r=a.clientX-j.clientX+o.x,s=a.clientY-j.clientY+o.y,c=e.touches?"translate3d("+r+"px,"+s+"px,0)":"translate("+r+"px,"+s+"px)";if(!t.active){if(i&&nt(et(a.clientX-this._lastX),et(a.clientY-this._lastY))<i)return;this._dragStarted()}this._appendGhost(),F=!0,L=a,l(C,"webkitTransform",c),l(C,"mozTransform",c),l(C,"msTransform",c),l(C,"transform",c),e.preventDefault()}},_appendGhost:function(){if(!C){var t,e=w.getBoundingClientRect(),n=l(w),i=this.options;C=w.cloneNode(!0),s(C,i.ghostClass,!1),s(C,i.fallbackClass,!0),s(C,i.dragClass,!0),l(C,"top",e.top-z(n.marginTop,10)),l(C,"left",e.left-z(n.marginLeft,10)),l(C,"width",e.width),l(C,"height",e.height),l(C,"opacity","0.8"),l(C,"position","fixed"),l(C,"zIndex","100000"),l(C,"pointerEvents","none"),i.fallbackOnBody&&V.body.appendChild(C)||E.appendChild(C),t=C.getBoundingClientRect(),l(C,"width",2*e.width-t.width),l(C,"height",2*e.height-t.height)}},_onDragStart:function(t,e){var n=t.dataTransfer,i=this.options;this._offUpEvents(),R.checkPull(this,this,w,t)&&(S=y(w),S.draggable=!1,S.style["will-change"]="",l(S,"display","none"),s(S,this.options.chosenClass,!1),E.insertBefore(S,w),d(this,E,"clone",w)),s(w,i.dragClass,!0),e?("touch"===e?(a(V,"touchmove",this._onTouchMove),a(V,"touchend",this._onDrop),a(V,"touchcancel",this._onDrop),a(V,"pointermove",this._onTouchMove),a(V,"pointerup",this._onDrop)):(a(V,"mousemove",this._onTouchMove),a(V,"mouseup",this._onDrop)),this._loopId=setInterval(this._emulateDragOver,50)):(n&&(n.effectAllowed="move",i.setData&&i.setData.call(this,n,w)),a(V,"drop",this),setTimeout(this._dragStarted,0))},_onDragOver:function(i){var o,a,r,s,c=this.el,d=this.options,u=d.group,g=t.active,v=R===u,m=!1,b=d.sort;if(void 0!==i.preventDefault&&(i.preventDefault(),!d.dragoverBubble&&i.stopPropagation()),!w.animated&&(F=!0,g&&!d.disabled&&(v?b||(s=!E.contains(w)):I===this||(g.lastPullMode=R.checkPull(this,g,w,i))&&u.checkPut(this,g,w,i))&&(void 0===i.rootEl||i.rootEl===this.el))){if(at(i,d,this.el),tt)return;if(o=n(i.target,d.draggable,c),a=w.getBoundingClientRect(),I!==this&&(I=this,m=!0),s)return e(g,!0),T=E,void(S||x?E.insertBefore(w,S||x):b||E.appendChild(w));if(0===c.children.length||c.children[0]===C||c===i.target&&(o=p(c,i))){if(o){if(o.animated)return;r=o.getBoundingClientRect()}e(g,v),h(E,c,w,a,o,r,i)!==!1&&(w.contains(c)||(c.appendChild(w),T=c),this._animate(a,w),o&&this._animate(r,o))}else if(o&&!o.animated&&o!==w&&void 0!==o.parentNode[K]){Y!==o&&(Y=o,X=l(o),M=l(o.parentNode)),r=o.getBoundingClientRect();var _=r.right-r.left,y=r.bottom-r.top,D=W.test(X.cssFloat+X.display)||"flex"==M.display&&0===M["flex-direction"].indexOf("row"),k=o.offsetWidth>w.offsetWidth,N=o.offsetHeight>w.offsetHeight,B=(D?(i.clientX-r.left)/_:(i.clientY-r.top)/y)>.5,O=o.nextElementSibling,A=h(E,c,w,a,o,r,i),P=!1;if(A!==!1){if(tt=!0,setTimeout(f,30),e(g,v),1===A||-1===A)P=1===A;else if(D){var j=w.offsetTop,L=o.offsetTop;P=j===L?o.previousElementSibling===w&&!k||B&&k:o.previousElementSibling===w||w.previousElementSibling===o?(i.clientY-r.top)/y>.5:L>j}else m||(P=O!==w&&!N||B&&N);w.contains(c)||(P&&!O?c.appendChild(w):o.parentNode.insertBefore(w,P?O:o)),T=w.parentNode,this._animate(a,w),this._animate(r,o)}}}},_animate:function(t,e){var n=this.options.animation;if(n){var i=e.getBoundingClientRect();1===t.nodeType&&(t=t.getBoundingClientRect()),l(e,"transition","none"),l(e,"transform","translate3d("+(t.left-i.left)+"px,"+(t.top-i.top)+"px,0)"),e.offsetWidth,l(e,"transition","all "+n+"ms"),l(e,"transform","translate3d(0,0,0)"),clearTimeout(e.animated),e.animated=setTimeout(function(){l(e,"transition",""),l(e,"transform",""),e.animated=!1},n)}},_offUpEvents:function(){var t=this.el.ownerDocument;r(V,"touchmove",this._onTouchMove),r(V,"pointermove",this._onTouchMove),r(t,"mouseup",this._onDrop),r(t,"touchend",this._onDrop),r(t,"pointerup",this._onDrop),r(t,"touchcancel",this._onDrop),r(t,"selectstart",this)},_onDrop:function(e){var n=this.el,i=this.options;clearInterval(this._loopId),clearInterval(U.pid),clearTimeout(this._dragStartTimer),r(V,"mousemove",this._onTouchMove),this.nativeDraggable&&(r(V,"drop",this),r(n,"dragstart",this._onDragStart)),this._offUpEvents(),e&&(F&&(e.preventDefault(),!i.dropBubble&&e.stopPropagation()),C&&C.parentNode.removeChild(C),E!==T&&"clone"===t.active.lastPullMode||S&&S.parentNode.removeChild(S),w&&(this.nativeDraggable&&r(w,"dragend",this),u(w),w.style["will-change"]="",s(w,this.options.ghostClass,!1),s(w,this.options.chosenClass,!1),E!==T?(P=v(w,i.draggable),P>=0&&(d(null,T,"add",w,E,A,P),d(this,E,"remove",w,E,A,P),d(null,T,"sort",w,E,A,P),d(this,E,"sort",w,E,A,P))):w.nextSibling!==x&&(P=v(w,i.draggable),P>=0&&(d(this,E,"update",w,E,A,P),d(this,E,"sort",w,E,A,P))),t.active&&(null!=P&&-1!==P||(P=A),d(this,E,"end",w,E,A,P),this.save()))),this._nulling()},_nulling:function(){E=w=T=C=x=S=k=N=B=j=L=F=P=Y=X=I=R=t.active=null,it.forEach(function(t){t.checked=!0}),it.length=0},handleEvent:function(t){switch(t.type){case"drop":case"dragend":this._onDrop(t);break;case"dragover":case"dragenter":w&&(this._onDragOver(t),o(t));break;case"selectstart":t.preventDefault()}},toArray:function(){for(var t,e=[],i=this.el.children,o=0,a=i.length,r=this.options;a>o;o++)t=i[o],n(t,r.draggable,this.el)&&e.push(t.getAttribute(r.dataIdAttr)||g(t));return e},sort:function(t){var e={},i=this.el;this.toArray().forEach(function(t,o){var a=i.children[o];n(a,this.options.draggable,i)&&(e[t]=a)},this),t.forEach(function(t){e[t]&&(i.removeChild(e[t]),i.appendChild(e[t]))})},save:function(){var t=this.options.store;t&&t.set(this)},closest:function(t,e){return n(t,e||this.options.draggable,this.el)},option:function(t,e){var n=this.options;return void 0===e?n[t]:(n[t]=e,void("group"===t&&rt(n)))},destroy:function(){var t=this.el;t[K]=null,r(t,"mousedown",this._onTapStart),r(t,"touchstart",this._onTapStart),r(t,"pointerdown",this._onTapStart),this.nativeDraggable&&(r(t,"dragover",this),r(t,"dragenter",this)),Array.prototype.forEach.call(t.querySelectorAll("[draggable]"),function(t){t.removeAttribute("draggable")}),ot.splice(ot.indexOf(this._onDragOver),1),this._onDrop(),this.el=t=null}},a(V,"touchmove",function(e){t.active&&e.preventDefault()});try{window.addEventListener("test",null,Object.defineProperty({},"passive",{get:function(){Z={capture:!1,passive:!1}}}))}catch(st){}return t.utils={on:a,off:r,css:l,find:c,is:function(t,e){return!!n(t,e,t)},extend:_,throttle:b,closest:n,toggleClass:s,clone:y,index:v},t.create=function(e,n){return new t(e,n)},t.version="1.5.1",t}),function(t){"use strict";"function"==typeof define&&define.amd?define(["jquery"],t):t(jQuery)}(function(t){"use strict";t.fn.kvsortable=function(e){var n,i=arguments;return this.each(function(){var o=t(this),a=o.data("kvsortable");a||!(e instanceof Object)&&e||(a=new KvSortable(this,e),o.data("kvsortable",a)),a&&("widget"===e?n=a:"destroy"===e?(a.destroy(),o.removeData("kvsortable")):"function"==typeof a[e]?n=a[e].apply(a,[].slice.call(i,1)):e in a.options&&(n=a.option.apply(a,i)))}),void 0===n?this:n}});
(function(e){"use strict";var t=typeof window==="undefined"?null:window;if(typeof define==="function"&&define.amd){define(function(){return e(t)})}else if(typeof module!=="undefined"){module.exports=e(t)}else{t.DOMPurify=e(t)}})(function e(t){"use strict";var r=function(t){return e(t)};r.version="0.7.4";if(!t||!t.document||t.document.nodeType!==9){r.isSupported=false;return r}var n=t.document;var a=n;var i=t.DocumentFragment;var o=t.HTMLTemplateElement;var l=t.NodeFilter;var s=t.NamedNodeMap||t.MozNamedAttrMap;var f=t.Text;var c=t.Comment;var u=t.DOMParser;if(typeof o==="function"){var d=n.createElement("template");if(d.content&&d.content.ownerDocument){n=d.content.ownerDocument}}var m=n.implementation;var p=n.createNodeIterator;var h=n.getElementsByTagName;var v=n.createDocumentFragment;var g=a.importNode;var y={};r.isSupported=typeof m.createHTMLDocument!=="undefined"&&n.documentMode!==9;var b=function(e,t){var r=t.length;while(r--){if(typeof t[r]==="string"){t[r]=t[r].toLowerCase()}e[t[r]]=true}return e};var T=function(e){var t={};var r;for(r in e){if(e.hasOwnProperty(r)){t[r]=e[r]}}return t};var x=null;var k=b({},["a","abbr","acronym","address","area","article","aside","audio","b","bdi","bdo","big","blink","blockquote","body","br","button","canvas","caption","center","cite","code","col","colgroup","content","data","datalist","dd","decorator","del","details","dfn","dir","div","dl","dt","element","em","fieldset","figcaption","figure","font","footer","form","h1","h2","h3","h4","h5","h6","head","header","hgroup","hr","html","i","img","input","ins","kbd","label","legend","li","main","map","mark","marquee","menu","menuitem","meter","nav","nobr","ol","optgroup","option","output","p","pre","progress","q","rp","rt","ruby","s","samp","section","select","shadow","small","source","spacer","span","strike","strong","style","sub","summary","sup","table","tbody","td","template","textarea","tfoot","th","thead","time","tr","track","tt","u","ul","var","video","wbr","svg","altglyph","altglyphdef","altglyphitem","animatecolor","animatemotion","animatetransform","circle","clippath","defs","desc","ellipse","filter","font","g","glyph","glyphref","hkern","image","line","lineargradient","marker","mask","metadata","mpath","path","pattern","polygon","polyline","radialgradient","rect","stop","switch","symbol","text","textpath","title","tref","tspan","view","vkern","feBlend","feColorMatrix","feComponentTransfer","feComposite","feConvolveMatrix","feDiffuseLighting","feDisplacementMap","feFlood","feFuncA","feFuncB","feFuncG","feFuncR","feGaussianBlur","feMerge","feMergeNode","feMorphology","feOffset","feSpecularLighting","feTile","feTurbulence","math","menclose","merror","mfenced","mfrac","mglyph","mi","mlabeledtr","mmuliscripts","mn","mo","mover","mpadded","mphantom","mroot","mrow","ms","mpspace","msqrt","mystyle","msub","msup","msubsup","mtable","mtd","mtext","mtr","munder","munderover","#text"]);var A=null;var w=b({},["accept","action","align","alt","autocomplete","background","bgcolor","border","cellpadding","cellspacing","checked","cite","class","clear","color","cols","colspan","coords","datetime","default","dir","disabled","download","enctype","face","for","headers","height","hidden","high","href","hreflang","id","ismap","label","lang","list","loop","low","max","maxlength","media","method","min","multiple","name","noshade","novalidate","nowrap","open","optimum","pattern","placeholder","poster","preload","pubdate","radiogroup","readonly","rel","required","rev","reversed","rows","rowspan","spellcheck","scope","selected","shape","size","span","srclang","start","src","step","style","summary","tabindex","title","type","usemap","valign","value","width","xmlns","accent-height","accumulate","additivive","alignment-baseline","ascent","attributename","attributetype","azimuth","basefrequency","baseline-shift","begin","bias","by","clip","clip-path","clip-rule","color","color-interpolation","color-interpolation-filters","color-profile","color-rendering","cx","cy","d","dx","dy","diffuseconstant","direction","display","divisor","dur","edgemode","elevation","end","fill","fill-opacity","fill-rule","filter","flood-color","flood-opacity","font-family","font-size","font-size-adjust","font-stretch","font-style","font-variant","font-weight","fx","fy","g1","g2","glyph-name","glyphref","gradientunits","gradienttransform","image-rendering","in","in2","k","k1","k2","k3","k4","kerning","keypoints","keysplines","keytimes","lengthadjust","letter-spacing","kernelmatrix","kernelunitlength","lighting-color","local","marker-end","marker-mid","marker-start","markerheight","markerunits","markerwidth","maskcontentunits","maskunits","max","mask","mode","min","numoctaves","offset","operator","opacity","order","orient","orientation","origin","overflow","paint-order","path","pathlength","patterncontentunits","patterntransform","patternunits","points","preservealpha","r","rx","ry","radius","refx","refy","repeatcount","repeatdur","restart","result","rotate","scale","seed","shape-rendering","specularconstant","specularexponent","spreadmethod","stddeviation","stitchtiles","stop-color","stop-opacity","stroke-dasharray","stroke-dashoffset","stroke-linecap","stroke-linejoin","stroke-miterlimit","stroke-opacity","stroke","stroke-width","surfacescale","targetx","targety","transform","text-anchor","text-decoration","text-rendering","textlength","u1","u2","unicode","values","viewbox","visibility","vert-adv-y","vert-origin-x","vert-origin-y","word-spacing","wrap","writing-mode","xchannelselector","ychannelselector","x","x1","x2","y","y1","y2","z","zoomandpan","accent","accentunder","bevelled","close","columnsalign","columnlines","columnspan","denomalign","depth","display","displaystyle","fence","frame","largeop","length","linethickness","lspace","lquote","mathbackground","mathcolor","mathsize","mathvariant","maxsize","minsize","movablelimits","notation","numalign","open","rowalign","rowlines","rowspacing","rowspan","rspace","rquote","scriptlevel","scriptminsize","scriptsizemultiplier","selection","separator","separators","stretchy","subscriptshift","supscriptshift","symmetric","voffset","xlink:href","xml:id","xlink:title","xml:space","xmlns:xlink"]);var E=null;var S=null;var M=true;var O=false;var L=false;var D=false;var N=/\{\{[\s\S]*|[\s\S]*\}\}/gm;var _=/<%[\s\S]*|[\s\S]*%>/gm;var C=false;var z=false;var R=false;var F=false;var H=true;var B=true;var W=b({},["audio","head","math","script","style","svg","video"]);var j=b({},["audio","video","img","source"]);var G=b({},["alt","class","for","id","label","name","pattern","placeholder","summary","title","value","style","xmlns"]);var I=null;var q=n.createElement("form");var P=function(e){if(typeof e!=="object"){e={}}x="ALLOWED_TAGS"in e?b({},e.ALLOWED_TAGS):k;A="ALLOWED_ATTR"in e?b({},e.ALLOWED_ATTR):w;E="FORBID_TAGS"in e?b({},e.FORBID_TAGS):{};S="FORBID_ATTR"in e?b({},e.FORBID_ATTR):{};M=e.ALLOW_DATA_ATTR!==false;O=e.ALLOW_UNKNOWN_PROTOCOLS||false;L=e.SAFE_FOR_JQUERY||false;D=e.SAFE_FOR_TEMPLATES||false;C=e.WHOLE_DOCUMENT||false;z=e.RETURN_DOM||false;R=e.RETURN_DOM_FRAGMENT||false;F=e.RETURN_DOM_IMPORT||false;H=e.SANITIZE_DOM!==false;B=e.KEEP_CONTENT!==false;if(D){M=false}if(R){z=true}if(e.ADD_TAGS){if(x===k){x=T(x)}b(x,e.ADD_TAGS)}if(e.ADD_ATTR){if(A===w){A=T(A)}b(A,e.ADD_ATTR)}if(B){x["#text"]=true}if(Object&&"freeze"in Object){Object.freeze(e)}I=e};var U=function(e){try{e.parentNode.removeChild(e)}catch(t){e.outerHTML=""}};var V=function(e){var t,r;try{t=(new u).parseFromString(e,"text/html")}catch(n){}if(!t){t=m.createHTMLDocument("");r=t.body;r.parentNode.removeChild(r.parentNode.firstElementChild);r.outerHTML=e}if(typeof t.getElementsByTagName==="function"){return t.getElementsByTagName(C?"html":"body")[0]}return h.call(t,C?"html":"body")[0]};var K=function(e){return p.call(e.ownerDocument||e,e,l.SHOW_ELEMENT|l.SHOW_COMMENT|l.SHOW_TEXT,function(){return l.FILTER_ACCEPT},false)};var J=function(e){if(e instanceof f||e instanceof c){return false}if(typeof e.nodeName!=="string"||typeof e.textContent!=="string"||typeof e.removeChild!=="function"||!(e.attributes instanceof s)||typeof e.removeAttribute!=="function"||typeof e.setAttribute!=="function"){return true}return false};var Q=function(e){var t,r;re("beforeSanitizeElements",e,null);if(J(e)){U(e);return true}t=e.nodeName.toLowerCase();re("uponSanitizeElement",e,{tagName:t});if(!x[t]||E[t]){if(B&&!W[t]&&typeof e.insertAdjacentHTML==="function"){try{e.insertAdjacentHTML("AfterEnd",e.innerHTML)}catch(n){}}U(e);return true}if(L&&!e.firstElementChild&&(!e.content||!e.content.firstElementChild)){e.innerHTML=e.textContent.replace(/</g,"&lt;")}if(D&&e.nodeType===3){r=e.textContent;r=r.replace(N," ");r=r.replace(_," ");e.textContent=r}re("afterSanitizeElements",e,null);return false};var X=/^data-[\w.\u00B7-\uFFFF-]/;var Y=/^(?:(?:(?:f|ht)tps?|mailto|tel):|[^a-z]|[a-z+.\-]+(?:[^a-z+.\-:]|$))/i;var Z=/^(?:\w+script|data):/i;var $=/[\x00-\x20\xA0\u1680\u180E\u2000-\u2029\u205f\u3000]/g;var ee=function(e){var r,a,i,o,l,s,f,c;re("beforeSanitizeAttributes",e,null);s=e.attributes;if(!s){return}f={attrName:"",attrValue:"",keepAttr:true};c=s.length;while(c--){r=s[c];a=r.name;i=r.value;o=a.toLowerCase();f.attrName=o;f.attrValue=i;f.keepAttr=true;re("uponSanitizeAttribute",e,f);i=f.attrValue;if(o==="name"&&e.nodeName==="IMG"&&s.id){l=s.id;s=Array.prototype.slice.apply(s);e.removeAttribute("id");e.removeAttribute(a);if(s.indexOf(l)>c){e.setAttribute("id",l.value)}}else{if(a==="id"){e.setAttribute(a,"")}e.removeAttribute(a)}if(!f.keepAttr){continue}if(H&&(o==="id"||o==="name")&&(i in t||i in n||i in q)){continue}if(D){i=i.replace(N," ");i=i.replace(_," ")}if(A[o]&&!S[o]&&(G[o]||Y.test(i.replace($,""))||o==="src"&&i.indexOf("data:")===0&&j[e.nodeName.toLowerCase()])||M&&X.test(o)||O&&!Z.test(i.replace($,""))){try{e.setAttribute(a,i)}catch(u){}}}re("afterSanitizeAttributes",e,null)};var te=function(e){var t;var r=K(e);re("beforeSanitizeShadowDOM",e,null);while(t=r.nextNode()){re("uponSanitizeShadowNode",t,null);if(Q(t)){continue}if(t.content instanceof i){te(t.content)}ee(t)}re("afterSanitizeShadowDOM",e,null)};var re=function(e,t,n){if(!y[e]){return}y[e].forEach(function(e){e.call(r,t,n,I)})};r.sanitize=function(e,n){var o,l,s,f,c;if(!e){e=""}if(typeof e!=="string"){if(typeof e.toString!=="function"){throw new TypeError("toString is not a function")}else{e=e.toString()}}if(!r.isSupported){if(typeof t.toStaticHTML==="object"||typeof t.toStaticHTML==="function"){return t.toStaticHTML(e)}return e}P(n);if(!z&&!C&&e.indexOf("<")===-1){return e}o=V(e);if(!o){return z?null:""}f=K(o);while(l=f.nextNode()){if(l.nodeType===3&&l===s){continue}if(Q(l)){continue}if(l.content instanceof i){te(l.content)}ee(l);s=l}if(z){if(R){c=v.call(o.ownerDocument);while(o.firstChild){c.appendChild(o.firstChild)}}else{c=o}if(F){c=g.call(a,c,true)}return c}return C?o.outerHTML:o.innerHTML};r.addHook=function(e,t){if(typeof t!=="function"){return}y[e]=y[e]||[];y[e].push(t)};r.removeHook=function(e){if(y[e]){y[e].pop()}};r.removeHooks=function(e){if(y[e]){y[e]=[]}};r.removeAllHooks=function(){y=[]};return r});
/*!
 * bootstrap-fileinput v4.4.6
 * http://plugins.krajee.com/file-input
 *
 * Author: Kartik Visweswaran
 * Copyright: 2014 - 2017, Kartik Visweswaran, Krajee.com
 *
 * Licensed under the BSD 3-Clause
 * https://github.com/kartik-v/bootstrap-fileinput/blob/master/LICENSE.md
 */!function(e){"use strict";"function"==typeof define&&define.amd?define(["jquery"],e):"object"==typeof module&&module.exports?module.exports=e(require("jquery")):e(window.jQuery)}(function(e){"use strict";e.fn.fileinputLocales={},e.fn.fileinputThemes={},String.prototype.setTokens=function(e){var t,i,a=this.toString();for(t in e)e.hasOwnProperty(t)&&(i=new RegExp("{"+t+"}","g"),a=a.replace(i,e[t]));return a};var t,i;t={FRAMES:".kv-preview-thumb",SORT_CSS:"file-sortable",OBJECT_PARAMS:'<param name="controller" value="true" />\n<param name="allowFullScreen" value="true" />\n<param name="allowScriptAccess" value="always" />\n<param name="autoPlay" value="false" />\n<param name="autoStart" value="false" />\n<param name="quality" value="high" />\n',DEFAULT_PREVIEW:'<div class="file-preview-other">\n<span class="{previewFileIconClass}">{previewFileIcon}</span>\n</div>',MODAL_ID:"kvFileinputModal",MODAL_EVENTS:["show","shown","hide","hidden","loaded"],objUrl:window.URL||window.webkitURL,compare:function(e,t,i){return void 0!==e&&(i?e===t:e.match(t))},isIE:function(e){if("Microsoft Internet Explorer"!==navigator.appName)return!1;if(10===e)return new RegExp("msie\\s"+e,"i").test(navigator.userAgent);var t,i=document.createElement("div");return i.innerHTML="<!--[if IE "+e+"]> <i></i> <![endif]-->",t=i.getElementsByTagName("i").length,document.body.appendChild(i),i.parentNode.removeChild(i),t},initModal:function(t){var i=e("body");i.length&&t.appendTo(i)},isEmpty:function(t,i){return void 0===t||null===t||0===t.length||i&&""===e.trim(t)},isArray:function(e){return Array.isArray(e)||"[object Array]"===Object.prototype.toString.call(e)},ifSet:function(e,t,i){return i=i||"",t&&"object"==typeof t&&e in t?t[e]:i},cleanArray:function(e){return e instanceof Array||(e=[]),e.filter(function(e){return void 0!==e&&null!==e})},spliceArray:function(e,t){var i,a=0,r=[];if(!(e instanceof Array))return[];for(i=0;i<e.length;i++)i!==t&&(r[a]=e[i],a++);return r},getNum:function(e,t){return t=t||0,"number"==typeof e?e:("string"==typeof e&&(e=parseFloat(e)),isNaN(e)?t:e)},hasFileAPISupport:function(){return!(!window.File||!window.FileReader)},hasDragDropSupport:function(){var e=document.createElement("div");return!t.isIE(9)&&(void 0!==e.draggable||void 0!==e.ondragstart&&void 0!==e.ondrop)},hasFileUploadSupport:function(){return t.hasFileAPISupport()&&window.FormData},hasBlobSupport:function(){try{return!!window.Blob&&Boolean(new Blob)}catch(e){return!1}},hasArrayBufferViewSupport:function(){try{return 100===new Blob([new Uint8Array(100)]).size}catch(e){return!1}},dataURI2Blob:function(e){var i,a,r,n,o,l,s=window.BlobBuilder||window.WebKitBlobBuilder||window.MozBlobBuilder||window.MSBlobBuilder,d=t.hasBlobSupport(),c=(d||s)&&window.atob&&window.ArrayBuffer&&window.Uint8Array;if(!c)return null;for(i=e.split(",")[0].indexOf("base64")>=0?atob(e.split(",")[1]):decodeURIComponent(e.split(",")[1]),a=new ArrayBuffer(i.length),r=new Uint8Array(a),n=0;n<i.length;n+=1)r[n]=i.charCodeAt(n);return o=e.split(",")[0].split(":")[1].split(";")[0],d?new Blob([t.hasArrayBufferViewSupport()?r:a],{type:o}):(l=new s,l.append(a),l.getBlob(o))},arrayBuffer2String:function(e){if(window.TextDecoder)return new TextDecoder("utf-8").decode(e);var t,i,a,r,n=Array.prototype.slice.apply(new Uint8Array(e)),o="",l=0;for(t=n.length;t>l;)switch(i=n[l++],i>>4){case 0:case 1:case 2:case 3:case 4:case 5:case 6:case 7:o+=String.fromCharCode(i);break;case 12:case 13:a=n[l++],o+=String.fromCharCode((31&i)<<6|63&a);break;case 14:a=n[l++],r=n[l++],o+=String.fromCharCode((15&i)<<12|(63&a)<<6|(63&r)<<0)}return o},isHtml:function(e){var t=document.createElement("div");t.innerHTML=e;for(var i=t.childNodes,a=i.length;a--;)if(1===i[a].nodeType)return!0;return!1},isSvg:function(e){return e.match(/^\s*<\?xml/i)&&(e.match(/<!DOCTYPE svg/i)||e.match(/<svg/i))},getMimeType:function(e,t,i){switch(e){case"ffd8ffe0":case"ffd8ffe1":case"ffd8ffe2":return"image/jpeg";case"89504E47":return"image/png";case"47494638":return"image/gif";case"49492a00":return"image/tiff";case"52494646":return"image/webp";case"66747970":return"video/3gp";case"4f676753":return"video/ogg";case"1a45dfa3":return"video/mkv";case"000001ba":case"000001b3":return"video/mpeg";case"3026b275":return"video/wmv";case"25504446":return"application/pdf";case"25215053":return"application/ps";case"504b0304":case"504b0506":case"504b0508":return"application/zip";case"377abcaf":return"application/7z";case"75737461":return"application/tar";case"7801730d":return"application/dmg";default:switch(e.substring(0,6)){case"435753":return"application/x-shockwave-flash";case"494433":return"audio/mp3";case"425a68":return"application/bzip";default:switch(e.substring(0,4)){case"424d":return"image/bmp";case"fffb":return"audio/mp3";case"4d5a":return"application/exe";case"1f9d":case"1fa0":return"application/zip";case"1f8b":return"application/gzip";default:return t&&!t.match(/[^\u0000-\u007f]/)?"application/text-plain":i}}}},addCss:function(e,t){e.removeClass(t).addClass(t)},getElement:function(i,a,r){return t.isEmpty(i)||t.isEmpty(i[a])?r:e(i[a])},uniqId:function(){return Math.round((new Date).getTime())+"_"+Math.round(100*Math.random())},htmlEncode:function(e){return e.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&apos;")},replaceTags:function(t,i){var a=t;return i?(e.each(i,function(e,t){"function"==typeof t&&(t=t()),a=a.split(e).join(t)}),a):a},cleanMemory:function(e){var i=e.is("img")?e.attr("src"):e.find("source").attr("src");t.objUrl.revokeObjectURL(i)},findFileName:function(e){var t=e.lastIndexOf("/");return-1===t&&(t=e.lastIndexOf("\\")),e.split(e.substring(t,t+1)).pop()},checkFullScreen:function(){return document.fullscreenElement||document.mozFullScreenElement||document.webkitFullscreenElement||document.msFullscreenElement},toggleFullScreen:function(e){var i=document,a=i.documentElement;a&&e&&!t.checkFullScreen()?a.requestFullscreen?a.requestFullscreen():a.msRequestFullscreen?a.msRequestFullscreen():a.mozRequestFullScreen?a.mozRequestFullScreen():a.webkitRequestFullscreen&&a.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT):i.exitFullscreen?i.exitFullscreen():i.msExitFullscreen?i.msExitFullscreen():i.mozCancelFullScreen?i.mozCancelFullScreen():i.webkitExitFullscreen&&i.webkitExitFullscreen()},moveArray:function(e,t,i){if(i>=e.length)for(var a=i-e.length;a--+1;)e.push(void 0);return e.splice(i,0,e.splice(t,1)[0]),e},cleanZoomCache:function(e){var t=e.closest(".kv-zoom-cache-theme");t.length||(t=e.closest(".kv-zoom-cache")),t.remove()},setOrientation:function(e,t){var i,a,r,n=new DataView(e),o=0,l=1;if(65496!==n.getUint16(o)||e.length<2)return void(t&&t());for(o+=2,i=n.byteLength;i-2>o;)switch(a=n.getUint16(o),o+=2,a){case 65505:r=n.getUint16(o),i=r-o,o+=2;break;case 274:l=n.getUint16(o+6,!1),i=0}t&&t(l)},validateOrientation:function(e,i){if(window.FileReader&&window.DataView){var a,r=new FileReader;r.onloadend=function(){a=r.result,t.setOrientation(a,i)},r.readAsArrayBuffer(e)}},adjustOrientedImage:function(e,t){var i,a,r;if(e.hasClass("is-portrait-gt4")){if(t)return void e.css({width:e.parent().height()});e.css({height:"auto",width:e.height()}),i=e.parent().offset().top,a=e.offset().top,r=i-a,e.css("margin-top",r)}},closeButton:function(e){return e=e?"close "+e:"close",'<button type="button" class="'+e+'" aria-label="Close">\n  <span aria-hidden="true">&times;</span>\n</button>'}},i=function(i,a){var r=this;r.$element=e(i),r.$parent=r.$element.parent(),r._validate()&&(r.isPreviewable=t.hasFileAPISupport(),r.isIE9=t.isIE(9),r.isIE10=t.isIE(10),(r.isPreviewable||r.isIE9)&&(r._init(a),r._listen()),r.$element.removeClass("file-loading"))},i.prototype={constructor:i,_cleanup:function(){var e=this;e.reader=null,e.formdata={},e.uploadCount=0,e.uploadStatus={},e.uploadLog=[],e.uploadAsyncCount=0,e.loadedImages=[],e.totalImagesCount=0,e.ajaxRequests=[],e.clearStack(),e.fileInputCleared=!1,e.fileBatchCompleted=!0,e.isPreviewable||(e.showPreview=!1),e.isError=!1,e.ajaxAborted=!1,e.cancelling=!1},_init:function(i,a){var r,n,o,l,s=this,d=s.$element;s.options=i,e.each(i,function(e,i){switch(e){case"minFileCount":case"maxFileCount":case"minFileSize":case"maxFileSize":case"maxFilePreviewSize":case"resizeImageQuality":case"resizeIfSizeMoreThan":case"progressUploadThreshold":case"initialPreviewCount":case"zoomModalHeight":case"minImageHeight":case"maxImageHeight":case"minImageWidth":case"maxImageWidth":s[e]=t.getNum(i);break;default:s[e]=i}}),s.rtl&&(l=s.previewZoomButtonIcons.prev,s.previewZoomButtonIcons.prev=s.previewZoomButtonIcons.next,s.previewZoomButtonIcons.next=l),a||s._cleanup(),s.$form=d.closest("form"),s._initTemplateDefaults(),s.uploadFileAttr=t.isEmpty(d.attr("name"))?"file_data":d.attr("name"),o=s._getLayoutTemplate("progress"),s.progressTemplate=o.replace("{class}",s.progressClass),s.progressCompleteTemplate=o.replace("{class}",s.progressCompleteClass),s.progressErrorTemplate=o.replace("{class}",s.progressErrorClass),s.dropZoneEnabled=t.hasDragDropSupport()&&s.dropZoneEnabled,s.isDisabled=d.attr("disabled")||d.attr("readonly"),s.isDisabled&&d.attr("disabled",!0),s.isAjaxUpload=t.hasFileUploadSupport()&&!t.isEmpty(s.uploadUrl),s.isClickable=s.browseOnZoneClick&&s.showPreview&&(s.isAjaxUpload&&s.dropZoneEnabled||!t.isEmpty(s.defaultPreviewContent)),s.slug="function"==typeof i.slugCallback?i.slugCallback:s._slugDefault,s.mainTemplate=s.showCaption?s._getLayoutTemplate("main1"):s._getLayoutTemplate("main2"),s.captionTemplate=s._getLayoutTemplate("caption"),s.previewGenericTemplate=s._getPreviewTemplate("generic"),!s.imageCanvas&&s.resizeImage&&(s.maxImageWidth||s.maxImageHeight)&&(s.imageCanvas=document.createElement("canvas"),s.imageCanvasContext=s.imageCanvas.getContext("2d")),t.isEmpty(d.attr("id"))&&d.attr("id",t.uniqId()),s.namespace=".fileinput_"+d.attr("id").replace(/-/g,"_"),void 0===s.$container?s.$container=s._createContainer():s._refreshContainer(),n=s.$container,s.$dropZone=n.find(".file-drop-zone"),s.$progress=n.find(".kv-upload-progress"),s.$btnUpload=n.find(".fileinput-upload"),s.$captionContainer=t.getElement(i,"elCaptionContainer",n.find(".file-caption")),s.$caption=t.getElement(i,"elCaptionText",n.find(".file-caption-name")),t.isEmpty(s.msgPlaceholder)||(r=d.attr("multiple")?s.filePlural:s.fileSingle,s.$caption.attr("placeholder",s.msgPlaceholder.replace("{files}",r))),s.$captionIcon=s.$captionContainer.find(".file-caption-icon"),s.mainClass.indexOf("input-group-lg")>-1?t.addCss(s.$captionIcon,"icon-lg"):s.$captionIcon.removeClass("icon-lg"),s.$previewContainer=t.getElement(i,"elPreviewContainer",n.find(".file-preview")),s.$preview=t.getElement(i,"elPreviewImage",n.find(".file-preview-thumbnails")),s.$previewStatus=t.getElement(i,"elPreviewStatus",n.find(".file-preview-status")),s.$errorContainer=t.getElement(i,"elErrorContainer",s.$previewContainer.find(".kv-fileinput-error")),s._validateDisabled(),t.isEmpty(s.msgErrorClass)||t.addCss(s.$errorContainer,s.msgErrorClass),a||(s.$errorContainer.hide(),s.previewInitId="preview-"+t.uniqId(),s._initPreviewCache(),s._initPreview(!0),s._initPreviewActions(),s._setFileDropZoneTitle(),s.$parent.hasClass("file-loading")&&(s.$container.insertBefore(s.$parent),s.$parent.remove())),d.attr("disabled")&&s.disable(),s._initZoom(),s.hideThumbnailContent&&t.addCss(s.$preview,"hide-content")},_initTemplateDefaults:function(){var i,a,r,n,o,l,s,d,c,p,u,f,m,v,g,h,w,_,b,C,y,x,T,E,S,k,F,I,P,A,D,z,$,j,U,B,R,O,L=this;i='{preview}\n<div class="kv-upload-progress kv-hidden"></div><div class="clearfix"></div>\n<div class="input-group {class}">\n  {caption}\n<div class="input-group-btn">\n      {remove}\n      {cancel}\n      {upload}\n      {browse}\n    </div>\n</div>',a='{preview}\n<div class="kv-upload-progress kv-hidden"></div>\n<div class="clearfix"></div>\n{remove}\n{cancel}\n{upload}\n{browse}\n',r='<div class="file-preview {class}">\n    {close}    <div class="{dropClass}">\n    <div class="file-preview-thumbnails">\n    </div>\n    <div class="clearfix"></div>    <div class="file-preview-status text-center text-success"></div>\n    <div class="kv-fileinput-error"></div>\n    </div>\n</div>',o=t.closeButton("fileinput-remove"),n='<i class="glyphicon glyphicon-file"></i>',l='<div class="file-caption form-control {class}" tabindex="500">\n  <span class="file-caption-icon"></span>\n  <input class="file-caption-name" onkeydown="return false;" onpaste="return false;">\n</div>',s='<button type="{type}" tabindex="500" title="{title}" class="{css}" {status}>{icon} {label}</button>',d='<a href="{href}" tabindex="500" title="{title}" class="{css}" {status}>{icon} {label}</a>',c='<div tabindex="500" class="{css}" {status}>{icon} {label}</div>',p='<div id="'+t.MODAL_ID+'" class="file-zoom-dialog modal fade" tabindex="-1" aria-labelledby="'+t.MODAL_ID+'Label"></div>',u='<div class="modal-dialog modal-lg{rtl}" role="document">\n  <div class="modal-content">\n    <div class="modal-header">\n      <h5 class="modal-title">{heading}</h5>\n      <span class="kv-zoom-title"></span>\n      <div class="kv-zoom-actions">{toggleheader}{fullscreen}{borderless}{close}</div>\n    </div>\n    <div class="modal-body">\n      <div class="floating-buttons"></div>\n      <div class="kv-zoom-body file-zoom-content {zoomFrameClass}"></div>\n{prev} {next}\n    </div>\n  </div>\n</div>\n',f='<div class="progress">\n    <div class="{class}" role="progressbar" aria-valuenow="{percent}" aria-valuemin="0" aria-valuemax="100" style="width:{percent}%;">\n        {status}\n     </div>\n</div>',m=" <samp>({sizeText})</samp>",v='<div class="file-thumbnail-footer">\n    <div class="file-footer-caption" title="{caption}">\n        <div class="file-caption-info">{caption}</div>\n        <div class="file-size-info">{size}</div>\n    </div>\n    {progress}\n{indicator}\n{actions}\n</div>',g='<div class="file-actions">\n    <div class="file-footer-buttons">\n        {download} {upload} {delete} {zoom} {other}    </div>\n</div>\n{drag}\n<div class="clearfix"></div>',h='<button type="button" class="kv-file-remove {removeClass}" title="{removeTitle}" {dataUrl}{dataKey}>{removeIcon}</button>\n',w='<button type="button" class="kv-file-upload {uploadClass}" title="{uploadTitle}">{uploadIcon}</button>',_='<button type="button" class="kv-file-download {downloadClass}" title="{downloadTitle}" data-url="{downloadUrl}" data-caption="{caption}">{downloadIcon}</button>',b='<button type="button" class="kv-file-zoom {zoomClass}" title="{zoomTitle}">{zoomIcon}</button>',C='<span class="file-drag-handle {dragClass}" title="{dragTitle}">{dragIcon}</span>',y='<div class="file-upload-indicator" title="{indicatorTitle}">{indicator}</div>',x='<div class="file-preview-frame {frameClass}" id="{previewId}" data-fileindex="{fileindex}" data-template="{template}"',T=x+'><div class="kv-file-content">\n',E=x+' title="{caption}"><div class="kv-file-content">\n',S="</div>{footer}\n</div>\n",k="{content}\n",F='<div class="kv-preview-data file-preview-html" title="{caption}" {style}>{data}</div>\n',I='<img src="{data}" class="file-preview-image kv-preview-data" title="{caption}" alt="{caption}" {style}>\n',P='<textarea class="kv-preview-data file-preview-text" title="{caption}" readonly {style}>{data}</textarea>\n',A='<iframe class="kv-preview-data file-preview-office" src="https://docs.google.com/gview?url={data}&embedded=true" {style}></iframe>',D='<video class="kv-preview-data file-preview-video" controls {style}>\n<source src="{data}" type="{type}">\n'+t.DEFAULT_PREVIEW+"\n</video>\n",z='<audio class="kv-preview-data file-preview-audio" controls {style}>\n<source src="{data}" type="{type}">\n'+t.DEFAULT_PREVIEW+"\n</audio>\n",$='<embed class="kv-preview-data file-preview-flash" src="{data}" type="application/x-shockwave-flash" {style}>\n',U='<embed class="kv-preview-data file-preview-pdf" src="{data}" type="application/pdf" {style}>\n',j='<object class="kv-preview-data file-preview-object file-object {typeCss}" data="{data}" type="{type}" {style}>\n<param name="movie" value="{caption}" />\n'+t.OBJECT_PARAMS+" "+t.DEFAULT_PREVIEW+"\n</object>\n",B='<div class="kv-preview-data file-preview-other-frame" {style}>\n'+t.DEFAULT_PREVIEW+"\n</div>\n",R='<div class="kv-zoom-cache" style="display:none">{zoomContent}</div>',O={width:"100%",height:"100%","min-height":"480px"},L.defaults={layoutTemplates:{main1:i,main2:a,preview:r,close:o,fileIcon:n,caption:l,modalMain:p,modal:u,progress:f,size:m,footer:v,indicator:y,actions:g,actionDelete:h,actionUpload:w,actionDownload:_,actionZoom:b,actionDrag:C,btnDefault:s,btnLink:d,btnBrowse:c,zoomCache:R},previewMarkupTags:{tagBefore1:T,tagBefore2:E,tagAfter:S},previewContentTemplates:{generic:k,html:F,image:I,text:P,office:A,video:D,audio:z,flash:$,object:j,pdf:U,other:B},allowedPreviewTypes:["image","html","text","video","audio","flash","pdf","object"],previewTemplates:{},previewSettings:{image:{width:"auto",height:"auto","max-width":"100%","max-height":"100%"},html:{width:"213px",height:"160px"},text:{width:"213px",height:"160px"},office:{width:"213px",height:"160px"},video:{width:"213px",height:"160px"},audio:{width:"100%",height:"30px"},flash:{width:"213px",height:"160px"},object:{width:"213px",height:"160px"},pdf:{width:"213px",height:"160px"},other:{width:"213px",height:"160px"}},previewSettingsSmall:{image:{width:"auto",height:"auto","max-width":"100%","max-height":"100%"},html:{width:"100%",height:"160px"},text:{width:"100%",height:"160px"},office:{width:"100%",height:"160px"},video:{width:"100%",height:"auto"},audio:{width:"100%",height:"30px"},flash:{width:"100%",height:"auto"},object:{width:"100%",height:"auto"},pdf:{width:"100%",height:"160px"},other:{width:"100%",height:"160px"}},previewZoomSettings:{image:{width:"auto",height:"auto","max-width":"100%","max-height":"100%"},html:O,text:O,office:{width:"100%",height:"100%","max-width":"100%","min-height":"480px"},video:{width:"auto",height:"100%","max-width":"100%"},audio:{width:"100%",height:"30px"},flash:{width:"auto",height:"480px"},object:{width:"auto",height:"100%","max-width":"100%","min-height":"480px"},pdf:O,other:{width:"auto",height:"100%","min-height":"480px"}},fileTypeSettings:{image:function(e,i){return t.compare(e,"image.*")&&!t.compare(e,/(tiff?|wmf)$/i)||t.compare(i,/\.(gif|png|jpe?g)$/i)},html:function(e,i){return t.compare(e,"text/html")||t.compare(i,/\.(htm|html)$/i)},office:function(e,i){return t.compare(e,/(word|excel|powerpoint|office|iwork-pages|tiff?)$/i)||t.compare(i,/\.(rtf|docx?|xlsx?|pptx?|pps|potx?|ods|odt|pages|ai|dxf|ttf|tiff?|wmf|e?ps)$/i)},text:function(e,i){return t.compare(e,"text.*")||t.compare(i,/\.(xml|javascript)$/i)||t.compare(i,/\.(txt|md|csv|nfo|ini|json|php|js|css)$/i)},video:function(e,i){return t.compare(e,"video.*")&&(t.compare(e,/(ogg|mp4|mp?g|mov|webm|3gp)$/i)||t.compare(i,/\.(og?|mp4|webm|mp?g|mov|3gp)$/i))},audio:function(e,i){return t.compare(e,"audio.*")&&(t.compare(i,/(ogg|mp3|mp?g|wav)$/i)||t.compare(i,/\.(og?|mp3|mp?g|wav)$/i))},flash:function(e,i){return t.compare(e,"application/x-shockwave-flash",!0)||t.compare(i,/\.(swf)$/i)},pdf:function(e,i){return t.compare(e,"application/pdf",!0)||t.compare(i,/\.(pdf)$/i)},object:function(){return!0},other:function(){return!0}},fileActionSettings:{showRemove:!0,showUpload:!0,showDownload:!0,showZoom:!0,showDrag:!0,removeIcon:'<i class="glyphicon glyphicon-trash"></i>',removeClass:"btn btn-kv btn-default btn-outline-secondary",removeErrorClass:"btn btn-kv btn-danger",removeTitle:"Remove file",uploadIcon:'<i class="glyphicon glyphicon-upload"></i>',uploadClass:"btn btn-kv btn-default btn-outline-secondary",uploadTitle:"Upload file",uploadRetryIcon:'<i class="glyphicon glyphicon-repeat"></i>',uploadRetryTitle:"Retry upload",downloadIcon:'<i class="glyphicon glyphicon-download"></i>',downloadClass:"btn btn-kv btn-default btn-outline-secondary",downloadTitle:"Download file",zoomIcon:'<i class="glyphicon glyphicon-zoom-in"></i>',zoomClass:"btn btn-kv btn-default btn-outline-secondary",zoomTitle:"View Details",dragIcon:'<i class="glyphicon glyphicon-move"></i>',dragClass:"text-info",dragTitle:"Move / Rearrange",dragSettings:{},indicatorNew:'<i class="glyphicon glyphicon-plus-sign text-warning"></i>',indicatorSuccess:'<i class="glyphicon glyphicon-ok-sign text-success"></i>',indicatorError:'<i class="glyphicon glyphicon-exclamation-sign text-danger"></i>',indicatorLoading:'<i class="glyphicon glyphicon-hourglass text-muted"></i>',indicatorNewTitle:"Not uploaded yet",indicatorSuccessTitle:"Uploaded",indicatorErrorTitle:"Upload Error",indicatorLoadingTitle:"Uploading ..."}},e.each(L.defaults,function(t,i){return"allowedPreviewTypes"===t?void(void 0===L.allowedPreviewTypes&&(L.allowedPreviewTypes=i)):void(L[t]=e.extend(!0,{},i,L[t]))}),L._initPreviewTemplates()},_initPreviewTemplates:function(){var i,a=this,r=a.defaults,n=a.previewMarkupTags,o=n.tagAfter;e.each(r.previewContentTemplates,function(e,r){t.isEmpty(a.previewTemplates[e])&&(i=n.tagBefore2,"generic"!==e&&"image"!==e&&"html"!==e&&"text"!==e||(i=n.tagBefore1),a.previewTemplates[e]=i+r+o)})},_initPreviewCache:function(){var i=this;i.previewCache={data:{},init:function(){var e=i.initialPreview;e.length>0&&!t.isArray(e)&&(e=e.split(i.initialPreviewDelimiter)),i.previewCache.data={content:e,config:i.initialPreviewConfig,tags:i.initialPreviewThumbTags}},count:function(){return i.previewCache.data&&i.previewCache.data.content?i.previewCache.data.content.length:0},get:function(a,r){var n,o,l,s,d,c,p,u="init_"+a,f=i.previewCache.data,m=f.config[a],v=f.content[a],g=i.previewInitId+"-"+u,h=t.ifSet("previewAsData",m,i.initialPreviewAsData),w=function(e,a,r,n,o,l,s,d,c){return d=" file-preview-initial "+t.SORT_CSS+(d?" "+d:""),i._generatePreviewTemplate(e,a,r,n,o,!1,null,d,l,s,c)};return v?(r=void 0===r?!0:r,l=t.ifSet("type",m,i.initialPreviewFileType||"generic"),d=t.ifSet("filename",m,t.ifSet("caption",m)),c=t.ifSet("filetype",m,l),s=i.previewCache.footer(a,r,m&&m.size||null),p=t.ifSet("frameClass",m),n=h?w(l,v,d,c,g,s,u,p):w("generic",v,d,c,g,s,u,p,l).setTokens({content:f.content[a]}),f.tags.length&&f.tags[a]&&(n=t.replaceTags(n,f.tags[a])),t.isEmpty(m)||t.isEmpty(m.frameAttr)||(o=e(document.createElement("div")).html(n),o.find(".file-preview-initial").attr(m.frameAttr),n=o.html(),o.remove()),n):""},add:function(e,a,r,n){var o,l=i.previewCache.data;return t.isArray(e)||(e=e.split(i.initialPreviewDelimiter)),n?(o=l.content.push(e)-1,l.config[o]=a,l.tags[o]=r):(o=e.length-1,l.content=e,l.config=a,l.tags=r),i.previewCache.data=l,o},set:function(e,a,r,n){var o,l,s=i.previewCache.data;if(e&&e.length&&(t.isArray(e)||(e=e.split(i.initialPreviewDelimiter)),l=e.filter(function(e){return null!==e}),l.length)){if(void 0===s.content&&(s.content=[]),void 0===s.config&&(s.config=[]),void 0===s.tags&&(s.tags=[]),n){for(o=0;o<e.length;o++)e[o]&&s.content.push(e[o]);for(o=0;o<a.length;o++)a[o]&&s.config.push(a[o]);for(o=0;o<r.length;o++)r[o]&&s.tags.push(r[o])}else s.content=e,s.config=a,s.tags=r;i.previewCache.data=s}},unset:function(e){var t=i.previewCache.count();if(t){if(1===t)return i.previewCache.data.content=[],i.previewCache.data.config=[],i.previewCache.data.tags=[],i.initialPreview=[],i.initialPreviewConfig=[],void(i.initialPreviewThumbTags=[]);i.previewCache.data.content.splice(e,1),i.previewCache.data.config.splice(e,1),i.previewCache.data.tags.splice(e,1)}},out:function(){var e,t,a="",r=i.previewCache.count();if(0===r)return{content:"",caption:""};for(t=0;r>t;t++)a+=i.previewCache.get(t);return e=i._getMsgSelected(r),{content:a,caption:e}},footer:function(e,a,r){var n=i.previewCache.data||{};if(t.isEmpty(n.content))return"";(t.isEmpty(n.config)||t.isEmpty(n.config[e]))&&(n.config[e]={}),a=void 0===a?!0:a;var o,l=n.config[e],s=t.ifSet("caption",l),d=t.ifSet("width",l,"auto"),c=t.ifSet("url",l,!1),p=t.ifSet("key",l,null),u=i.fileActionSettings,f=i.initialPreviewShowDelete||!1,m=l.downloadUrl||i.initialPreviewDownloadUrl||"",v=l.filename||l.caption||"",g=!!m,h=t.ifSet("showDelete",l,t.ifSet("showDelete",u,f)),w=t.ifSet("showDownload",l,t.ifSet("showDownload",u,g)),_=t.ifSet("showZoom",l,t.ifSet("showZoom",u,!0)),b=t.ifSet("showDrag",l,t.ifSet("showDrag",u,!0)),C=c===!1&&a;return w=w&&l.downloadUrl!==!1&&!!m,o=i._renderFileActions(!1,w,h,_,b,C,c,p,!0,m,v),i._getLayoutTemplate("footer").setTokens({progress:i._renderThumbProgress(),actions:o,caption:s,size:i._getSize(r),width:d,indicator:""})}},i.previewCache.init()},_handler:function(e,t,i){var a=this,r=a.namespace,n=t.split(" ").join(r+" ")+r;e&&e.length&&e.off(n).on(n,i)},_log:function(e){var t=this,i=t.$element.attr("id");i&&(e='"'+i+'": '+e),"undefined"!=typeof window.console.log?window.console.log(e):window.alert(e)},_validate:function(){var e=this,t="file"===e.$element.attr("type");return t||e._log('The input "type" must be set to "file" for initializing the "bootstrap-fileinput" plugin.'),t},_errorsExist:function(){var t,i=this,a=i.$errorContainer.find("li");return a.length?!0:(t=e(document.createElement("div")).html(i.$errorContainer.html()),t.find(".kv-error-close").remove(),t.find("ul").remove(),!!e.trim(t.text()).length)},_errorHandler:function(e,t){var i=this,a=e.target.error,r=function(e){i._showError(e.replace("{name}",t))};r(a.code===a.NOT_FOUND_ERR?i.msgFileNotFound:a.code===a.SECURITY_ERR?i.msgFileSecured:a.code===a.NOT_READABLE_ERR?i.msgFileNotReadable:a.code===a.ABORT_ERR?i.msgFilePreviewAborted:i.msgFilePreviewError)},_addError:function(e){var t=this,i=t.$errorContainer;e&&i.length&&(i.html(t.errorCloseButton+e),t._handler(i.find(".kv-error-close"),"click",function(){setTimeout(function(){t.showPreview&&!t.getFrames().length&&t.clear(),i.fadeOut("slow")},10)}))},_setValidationError:function(e){var i=this;e=(e?e+" ":"")+"has-error",i.$container.removeClass(e).addClass("has-error"),t.addCss(i.$captionContainer,"is-invalid")},_resetErrors:function(e){var t=this,i=t.$errorContainer;t.isError=!1,t.$container.removeClass("has-error"),t.$captionContainer.removeClass("is-invalid"),i.html(""),e?i.fadeOut("slow"):i.hide()},_showFolderError:function(e){var t,i=this,a=i.$errorContainer;e&&(t=i.msgFoldersNotAllowed.replace("{n}",e),i._addError(t),i._setValidationError(),a.fadeIn(800),i._raise("filefoldererror",[e,t]))},_showUploadError:function(e,t,i){var a=this,r=a.$errorContainer,n=i||"fileuploaderror",o=t&&t.id?'<li data-file-id="'+t.id+'">'+e+"</li>":"<li>"+e+"</li>";return 0===r.find("ul").length?a._addError("<ul>"+o+"</ul>"):r.find("ul").append(o),r.fadeIn(800),a._raise(n,[t,e]),a._setValidationError("file-input-new"),!0},_showError:function(e,t,i){var a=this,r=a.$errorContainer,n=i||"fileerror";return t=t||{},t.reader=a.reader,a._addError(e),r.fadeIn(800),a._raise(n,[t,e]),a.isAjaxUpload||a._clearFileInput(),a._setValidationError("file-input-new"),a.$btnUpload.attr("disabled",!0),!0},_noFilesError:function(e){var t=this,i=t.minFileCount>1?t.filePlural:t.fileSingle,a=t.msgFilesTooLess.replace("{n}",t.minFileCount).replace("{files}",i),r=t.$errorContainer;t._addError(a),t.isError=!0,t._updateFileDetails(0),r.fadeIn(800),t._raise("fileerror",[e,a]),t._clearFileInput(),t._setValidationError()},_parseError:function(t,i,a,r){var n,o=this,l=e.trim(a+""),s=void 0!==i.responseJSON&&void 0!==i.responseJSON.error?i.responseJSON.error:i.responseText;return o.cancelling&&o.msgUploadAborted&&(l=o.msgUploadAborted),o.showAjaxErrorDetails&&s&&(s=e.trim(s.replace(/\n\s*\n/g,"\n")),n=s.length?"<pre>"+s+"</pre>":"",l+=l?n:s),l||(l=o.msgAjaxError.replace("{operation}",t)),o.cancelling=!1,r?"<b>"+r+": </b>"+l:l},_parseFileType:function(e,i){var a,r,n,o,l=this,s=l.allowedPreviewTypes||[];if("application/text-plain"===e)return"text";for(o=0;o<s.length;o++)if(n=s[o],a=l.fileTypeSettings[n],r=a(e,i)?n:"",!t.isEmpty(r))return r;return"other"},_getPreviewIcon:function(t){var i,a=this,r=null;return t&&t.indexOf(".")>-1&&(i=t.split(".").pop(),a.previewFileIconSettings&&(r=a.previewFileIconSettings[i]||a.previewFileIconSettings[i.toLowerCase()]||null),a.previewFileExtSettings&&e.each(a.previewFileExtSettings,function(e,t){return a.previewFileIconSettings[e]&&t(i)?void(r=a.previewFileIconSettings[e]):void 0})),r},_parseFilePreviewIcon:function(e,t){var i=this,a=i._getPreviewIcon(t)||i.previewFileIcon,r=e;return r.indexOf("{previewFileIcon}")>-1&&(r=r.setTokens({previewFileIconClass:i.previewFileIconClass,previewFileIcon:a})),r},_raise:function(t,i){var a=this,r=e.Event(t);if(void 0!==i?a.$element.trigger(r,i):a.$element.trigger(r),r.isDefaultPrevented()||r.result===!1)return!1;switch(t){case"filebatchuploadcomplete":case"filebatchuploadsuccess":case"fileuploaded":case"fileclear":case"filecleared":case"filereset":case"fileerror":case"filefoldererror":case"fileuploaderror":case"filebatchuploaderror":case"filedeleteerror":case"filecustomerror":case"filesuccessremove":break;default:a.ajaxAborted||(a.ajaxAborted=r.result)}return!0},_listenFullScreen:function(e){var t,i,a=this,r=a.$modal;r&&r.length&&(t=r&&r.find(".btn-fullscreen"),i=r&&r.find(".btn-borderless"),t.length&&i.length&&(t.removeClass("active").attr("aria-pressed","false"),i.removeClass("active").attr("aria-pressed","false"),e?t.addClass("active").attr("aria-pressed","true"):i.addClass("active").attr("aria-pressed","true"),r.hasClass("file-zoom-fullscreen")?a._maximizeZoomDialog():e?a._maximizeZoomDialog():i.removeClass("active").attr("aria-pressed","false")))},_listen:function(){var i,a=this,r=a.$element,n=a.$form,o=a.$container;a._handler(r,"change",e.proxy(a._change,a)),a.showBrowse&&a._handler(a.$btnFile,"click",e.proxy(a._browse,a)),a._handler(o.find(".fileinput-remove:not([disabled])"),"click",e.proxy(a.clear,a)),a._handler(o.find(".fileinput-cancel"),"click",e.proxy(a.cancel,a)),a._initDragDrop(),a._handler(n,"reset",e.proxy(a.clear,a)),a.isAjaxUpload||a._handler(n,"submit",e.proxy(a._submitForm,a)),a._handler(a.$container.find(".fileinput-upload"),"click",e.proxy(a._uploadClick,a)),a._handler(e(window),"resize",function(){a._listenFullScreen(screen.width===window.innerWidth&&screen.height===window.innerHeight)}),i="webkitfullscreenchange mozfullscreenchange fullscreenchange MSFullscreenChange",a._handler(e(document),i,function(){a._listenFullScreen(t.checkFullScreen())}),a._autoFitContent(),a._initClickable()},_autoFitContent:function(){var t,i=window.innerWidth||document.documentElement.clientWidth||document.body.clientWidth,a=this,r=400>i?a.previewSettingsSmall||a.defaults.previewSettingsSmall:a.previewSettings||a.defaults.previewSettings;e.each(r,function(e,i){t=".file-preview-frame .file-preview-"+e,a.$preview.find(t+".kv-preview-data,"+t+" .kv-preview-data").css(i)})},_initClickable:function(){var i,a=this;a.isClickable&&(i=a.isAjaxUpload?a.$dropZone:a.$preview.find(".file-default-preview"),t.addCss(i,"clickable"),i.attr("tabindex",-1),a._handler(i,"click",function(t){var r=e(t.target);i.find(".kv-fileinput-error:visible").length||r.parents(".file-preview-thumbnails").length&&!r.parents(".file-default-preview").length||(a.$element.trigger("click"),i.blur())}))},_initDragDrop:function(){var t=this,i=t.$dropZone;t.isAjaxUpload&&t.dropZoneEnabled&&t.showPreview&&(t._handler(i,"dragenter dragover",e.proxy(t._zoneDragEnter,t)),t._handler(i,"dragleave",e.proxy(t._zoneDragLeave,t)),t._handler(i,"drop",e.proxy(t._zoneDrop,t)),t._handler(e(document),"dragenter dragover drop",t._zoneDragDropInit))},_zoneDragDropInit:function(e){e.stopPropagation(),e.preventDefault()},_zoneDragEnter:function(i){var a=this,r=e.inArray("Files",i.originalEvent.dataTransfer.types)>-1;return a._zoneDragDropInit(i),a.isDisabled||!r?(i.originalEvent.dataTransfer.effectAllowed="none",void(i.originalEvent.dataTransfer.dropEffect="none")):void t.addCss(a.$dropZone,"file-highlighted")},_zoneDragLeave:function(e){var t=this;t._zoneDragDropInit(e),t.isDisabled||t.$dropZone.removeClass("file-highlighted")},_zoneDrop:function(e){var i=this;e.preventDefault(),i.isDisabled||t.isEmpty(e.originalEvent.dataTransfer.files)||(i._change(e,"dragdrop"),
i.$dropZone.removeClass("file-highlighted"))},_uploadClick:function(e){var i,a=this,r=a.$container.find(".fileinput-upload"),n=!r.hasClass("disabled")&&t.isEmpty(r.attr("disabled"));if(!e||!e.isDefaultPrevented()){if(!a.isAjaxUpload)return void(n&&"submit"!==r.attr("type")&&(i=r.closest("form"),i.length&&i.trigger("submit"),e.preventDefault()));e.preventDefault(),n&&a.upload()}},_submitForm:function(){var e=this;return e._isFileSelectionValid()&&!e._abort({})},_clearPreview:function(){var i=this,a=i.$preview,r=i.showUploadedThumbs?i.getFrames(":not(.file-preview-success)"):i.getFrames();r.each(function(){var i=e(this);i.remove(),t.cleanZoomCache(a.find("#zoom-"+i.attr("id")))}),i.getFrames().length&&i.showPreview||i._resetUpload(),i._validateDefaultPreview()},_initSortable:function(){var i,a=this,r=a.$preview,n="."+t.SORT_CSS;window.KvSortable&&0!==r.find(n).length&&(i={handle:".drag-handle-init",dataIdAttr:"data-preview-id",scroll:!1,draggable:n,onSort:function(i){var r,n,o=i.oldIndex,l=i.newIndex;a.initialPreview=t.moveArray(a.initialPreview,o,l),a.initialPreviewConfig=t.moveArray(a.initialPreviewConfig,o,l),a.previewCache.init();for(var s=0;s<a.initialPreviewConfig.length;s++)null!==a.initialPreviewConfig[s]&&(n=e(i.item),r=n.closest(t.FRAMES),r.attr("data-fileindex","init_"+s).attr("data-fileindex","init_"+s));a._raise("filesorted",{previewId:e(i.item).attr("id"),oldIndex:o,newIndex:l,stack:a.initialPreviewConfig})}},r.data("kvsortable")&&r.kvsortable("destroy"),e.extend(!0,i,a.fileActionSettings.dragSettings),r.kvsortable(i))},_setPreviewContent:function(e){var t=this;t.$preview.html(e),t._autoFitContent()},_initPreview:function(e){var i,a=this,r=a.initialCaption||"";return a.previewCache.count()?(i=a.previewCache.out(),r=e&&a.initialCaption?a.initialCaption:i.caption,a._setPreviewContent(i.content),a._setInitThumbAttr(),a._setCaption(r),a._initSortable(),void(t.isEmpty(i.content)||a.$container.removeClass("file-input-new"))):(a._clearPreview(),void(e?a._setCaption(r):a._initCaption()))},_getZoomButton:function(e){var t=this,i=t.previewZoomButtonIcons[e],a=t.previewZoomButtonClasses[e],r=' title="'+(t.previewZoomButtonTitles[e]||"")+'" ',n=r+("close"===e?' data-dismiss="modal" aria-hidden="true"':"");return"fullscreen"!==e&&"borderless"!==e&&"toggleheader"!==e||(n+=' data-toggle="button" aria-pressed="false" autocomplete="off"'),'<button type="button" class="'+a+" btn-"+e+'"'+n+">"+i+"</button>"},_getModalContent:function(){var e=this;return e._getLayoutTemplate("modal").setTokens({rtl:e.rtl?" kv-rtl":"",zoomFrameClass:e.frameClass,heading:e.msgZoomModalHeading,prev:e._getZoomButton("prev"),next:e._getZoomButton("next"),toggleheader:e._getZoomButton("toggleheader"),fullscreen:e._getZoomButton("fullscreen"),borderless:e._getZoomButton("borderless"),close:e._getZoomButton("close")})},_listenModalEvent:function(e){var i=this,a=i.$modal,r=function(e){return{sourceEvent:e,previewId:a.data("previewId"),modal:a}};a.on(e+".bs.modal",function(n){var o=a.find(".btn-fullscreen"),l=a.find(".btn-borderless");i._raise("filezoom"+e,r(n)),"shown"===e&&(l.removeClass("active").attr("aria-pressed","false"),o.removeClass("active").attr("aria-pressed","false"),a.hasClass("file-zoom-fullscreen")&&(i._maximizeZoomDialog(),t.checkFullScreen()?o.addClass("active").attr("aria-pressed","true"):l.addClass("active").attr("aria-pressed","true")))})},_initZoom:function(){var i,a=this,r=a._getLayoutTemplate("modalMain"),n="#"+t.MODAL_ID;a.showPreview&&(a.$modal=e(n),a.$modal&&a.$modal.length||(i=e(document.createElement("div")).html(r).insertAfter(a.$container),a.$modal=e(n).insertBefore(i),i.remove()),t.initModal(a.$modal),a.$modal.html(a._getModalContent()),e.each(t.MODAL_EVENTS,function(e,t){a._listenModalEvent(t)}))},_initZoomButtons:function(){var t,i,a=this,r=a.$modal.data("previewId")||"",n=a.getFrames().toArray(),o=n.length,l=a.$modal.find(".btn-prev"),s=a.$modal.find(".btn-next");return n.length<2?(l.hide(),void s.hide()):(l.show(),s.show(),void(o&&(t=e(n[0]),i=e(n[o-1]),l.removeAttr("disabled"),s.removeAttr("disabled"),t.length&&t.attr("id")===r&&l.attr("disabled",!0),i.length&&i.attr("id")===r&&s.attr("disabled",!0))))},_maximizeZoomDialog:function(){var t=this,i=t.$modal,a=i.find(".modal-header:visible"),r=i.find(".modal-footer:visible"),n=i.find(".modal-body"),o=e(window).height(),l=0;i.addClass("file-zoom-fullscreen"),a&&a.length&&(o-=a.outerHeight(!0)),r&&r.length&&(o-=r.outerHeight(!0)),n&&n.length&&(l=n.outerHeight(!0)-n.height(),o-=l),i.find(".kv-zoom-body").height(o)},_resizeZoomDialog:function(e){var i=this,a=i.$modal,r=a.find(".btn-fullscreen"),n=a.find(".btn-borderless");if(a.hasClass("file-zoom-fullscreen"))t.toggleFullScreen(!1),e?r.hasClass("active")||(a.removeClass("file-zoom-fullscreen"),i._resizeZoomDialog(!0),n.hasClass("active")&&n.removeClass("active").attr("aria-pressed","false")):r.hasClass("active")?r.removeClass("active").attr("aria-pressed","false"):(a.removeClass("file-zoom-fullscreen"),i.$modal.find(".kv-zoom-body").css("height",i.zoomModalHeight));else{if(!e)return void i._maximizeZoomDialog();t.toggleFullScreen(!0)}a.focus()},_setZoomContent:function(i,a){var r,n,o,l,s,d,c,p,u,f,m=this,v=i.attr("id"),g=m.$modal,h=g.find(".btn-prev"),w=g.find(".btn-next"),_=g.find(".btn-fullscreen"),b=g.find(".btn-borderless"),C=g.find(".btn-toggleheader"),y=m.$preview.find("#zoom-"+v);n=y.attr("data-template")||"generic",r=y.find(".kv-file-content"),o=r.length?r.html():"",u=i.data("caption")||"",f=i.data("size")||"",l=u+" "+f,g.find(".kv-zoom-title").attr("title",e("<div/>").html(l).text()).html(l),s=g.find(".kv-zoom-body"),g.removeClass("kv-single-content"),a?(p=s.addClass("file-thumb-loading").clone().insertAfter(s),s.html(o).hide(),p.fadeOut("fast",function(){s.fadeIn("fast",function(){s.removeClass("file-thumb-loading")}),p.remove()})):s.html(o),c=m.previewZoomSettings[n],c&&(d=s.find(".kv-preview-data"),t.addCss(d,"file-zoom-detail"),e.each(c,function(e,t){d.css(e,t),(d.attr("width")&&"width"===e||d.attr("height")&&"height"===e)&&d.removeAttr(e)})),g.data("previewId",v);var x=s.find("img");x.length&&t.adjustOrientedImage(x,!0),m._handler(h,"click",function(){m._zoomSlideShow("prev",v)}),m._handler(w,"click",function(){m._zoomSlideShow("next",v)}),m._handler(_,"click",function(){m._resizeZoomDialog(!0)}),m._handler(b,"click",function(){m._resizeZoomDialog(!1)}),m._handler(C,"click",function(){var e,t=g.find(".modal-header"),i=g.find(".modal-body .floating-buttons"),a=t.find(".kv-zoom-actions"),r=function(e){var i=m.$modal.find(".kv-zoom-body"),a=m.zoomModalHeight;g.hasClass("file-zoom-fullscreen")&&(a=i.outerHeight(!0),e||(a-=t.outerHeight(!0))),i.css("height",e?a+e:a)};t.is(":visible")?(e=t.outerHeight(!0),t.slideUp("slow",function(){a.find(".btn").appendTo(i),r(e)})):(i.find(".btn").appendTo(a),t.slideDown("slow",function(){r()})),g.focus()}),m._handler(g,"keydown",function(e){var t=e.which||e.keyCode;37!==t||h.attr("disabled")||m._zoomSlideShow("prev",v),39!==t||w.attr("disabled")||m._zoomSlideShow("next",v)})},_zoomPreview:function(e){var i,a=this,r=a.$modal;if(!e.length)throw"Cannot zoom to detailed preview!";t.initModal(r),r.html(a._getModalContent()),i=e.closest(t.FRAMES),a._setZoomContent(i),r.modal("show"),a._initZoomButtons()},_zoomSlideShow:function(t,i){var a,r,n,o=this,l=o.$modal.find(".kv-zoom-actions .btn-"+t),s=o.getFrames().toArray(),d=s.length;if(!l.attr("disabled")){for(r=0;d>r;r++)if(e(s[r]).attr("id")===i){n="prev"===t?r-1:r+1;break}0>n||n>=d||!s[n]||(a=e(s[n]),a.length&&o._setZoomContent(a,!0),o._initZoomButtons(),o._raise("filezoom"+t,{previewId:i,modal:o.$modal}))}},_initZoomButton:function(){var t=this;t.$preview.find(".kv-file-zoom").each(function(){var i=e(this);t._handler(i,"click",function(){t._zoomPreview(i)})})},_clearObjects:function(t){t.find("video audio").each(function(){this.pause(),e(this).remove()}),t.find("img object div").each(function(){e(this).remove()})},_clearFileInput:function(){var i,a,r,n=this,o=n.$element;n.fileInputCleared=!0,t.isEmpty(o.val())||(n.isIE9||n.isIE10?(i=o.closest("form"),a=e(document.createElement("form")),r=e(document.createElement("div")),o.before(r),i.length?i.after(a):r.after(a),a.append(o).trigger("reset"),r.before(o).remove(),a.remove()):o.val(""))},_resetUpload:function(){var e=this;e.uploadCache={content:[],config:[],tags:[],append:!0},e.uploadCount=0,e.uploadStatus={},e.uploadLog=[],e.uploadAsyncCount=0,e.loadedImages=[],e.totalImagesCount=0,e.$btnUpload.removeAttr("disabled"),e._setProgress(0),e.$progress.hide(),e._resetErrors(!1),e.ajaxAborted=!1,e.ajaxRequests=[],e._resetCanvas(),e.cacheInitialPreview={},e.overwriteInitial&&(e.initialPreview=[],e.initialPreviewConfig=[],e.initialPreviewThumbTags=[],e.previewCache.data={content:[],config:[],tags:[]})},_resetCanvas:function(){var e=this;e.canvas&&e.imageCanvasContext&&e.imageCanvasContext.clearRect(0,0,e.canvas.width,e.canvas.height)},_hasInitialPreview:function(){var e=this;return!e.overwriteInitial&&e.previewCache.count()},_resetPreview:function(){var e,t,i=this;i.previewCache.count()?(e=i.previewCache.out(),i._setPreviewContent(e.content),i._setInitThumbAttr(),t=i.initialCaption?i.initialCaption:e.caption,i._setCaption(t)):(i._clearPreview(),i._initCaption()),i.showPreview&&(i._initZoom(),i._initSortable())},_clearDefaultPreview:function(){var e=this;e.$preview.find(".file-default-preview").remove()},_validateDefaultPreview:function(){var e=this;e.showPreview&&!t.isEmpty(e.defaultPreviewContent)&&(e._setPreviewContent('<div class="file-default-preview">'+e.defaultPreviewContent+"</div>"),e.$container.removeClass("file-input-new"),e._initClickable())},_resetPreviewThumbs:function(e){var t,i=this;return e?(i._clearPreview(),void i.clearStack()):void(i._hasInitialPreview()?(t=i.previewCache.out(),i._setPreviewContent(t.content),i._setInitThumbAttr(),i._setCaption(t.caption),i._initPreviewActions()):i._clearPreview())},_getLayoutTemplate:function(e){var i=this,a=i.layoutTemplates[e];return t.isEmpty(i.customLayoutTags)?a:t.replaceTags(a,i.customLayoutTags)},_getPreviewTemplate:function(e){var i=this,a=i.previewTemplates[e];return t.isEmpty(i.customPreviewTags)?a:t.replaceTags(a,i.customPreviewTags)},_getOutData:function(e,t,i){var a=this;return e=e||{},t=t||{},i=i||a.filestack.slice(0)||{},{form:a.formdata,files:i,filenames:a.filenames,filescount:a.getFilesCount(),extra:a._getExtraData(),response:t,reader:a.reader,jqXHR:e}},_getMsgSelected:function(e){var t=this,i=1===e?t.fileSingle:t.filePlural;return e>0?t.msgSelected.replace("{n}",e).replace("{files}",i):t.msgNoFilesSelected},_getFrame:function(t){var i=this,a=e("#"+t);return a.length?a:(i._log('Invalid thumb frame with id: "'+t+'".'),null)},_getThumbs:function(e){return e=e||"",this.getFrames(":not(.file-preview-initial)"+e)},_getExtraData:function(e,t){var i=this,a=i.uploadExtraData;return"function"==typeof i.uploadExtraData&&(a=i.uploadExtraData(e,t)),a},_initXhr:function(e,t,i){var a=this;return e.upload&&e.upload.addEventListener("progress",function(e){var r=0,n=e.total,o=e.loaded||e.position;e.lengthComputable&&(r=Math.floor(o/n*100)),t?a._setAsyncUploadStatus(t,r,i):a._setProgress(r)},!1),e},_mergeAjaxCallback:function(e,t,i){var a,r=this,n=r.ajaxSettings,o=r.mergeAjaxCallbacks;"delete"===i&&(n=r.ajaxDeleteSettings,o=r.mergeAjaxDeleteCallbacks),a=n[e],o&&"function"==typeof a?"before"===o?n[e]=function(){a.apply(this,arguments),t.apply(this,arguments)}:n[e]=function(){t.apply(this,arguments),a.apply(this,arguments)}:n[e]=t,"delete"===i?r.ajaxDeleteSettings=n:r.ajaxSettings=n},_ajaxSubmit:function(t,i,a,r,n,o){var l,s=this;s._raise("filepreajax",[n,o])&&(s._uploadExtra(n,o),s._mergeAjaxCallback("beforeSend",t),s._mergeAjaxCallback("success",i),s._mergeAjaxCallback("complete",a),s._mergeAjaxCallback("error",r),l=e.extend(!0,{},{xhr:function(){var t=e.ajaxSettings.xhr();return s._initXhr(t,n,s.getFileStack().length)},url:o&&s.uploadUrlThumb?s.uploadUrlThumb:s.uploadUrl,type:"POST",dataType:"json",data:s.formdata,cache:!1,processData:!1,contentType:!1},s.ajaxSettings),s.ajaxRequests.push(e.ajax(l)))},_mergeArray:function(e,i){var a=this,r=t.cleanArray(a[e]),n=t.cleanArray(i);a[e]=r.concat(n)},_initUploadSuccess:function(i,a,r){var n,o,l,s,d,c,p,u,f,m=this;m.showPreview&&"object"==typeof i&&!e.isEmptyObject(i)&&void 0!==i.initialPreview&&i.initialPreview.length>0&&(m.hasInitData=!0,c=i.initialPreview||[],p=i.initialPreviewConfig||[],u=i.initialPreviewThumbTags||[],n=void 0===i.append||i.append,c.length>0&&!t.isArray(c)&&(c=c.split(m.initialPreviewDelimiter)),m._mergeArray("initialPreview",c),m._mergeArray("initialPreviewConfig",p),m._mergeArray("initialPreviewThumbTags",u),void 0!==a?r?(f=a.attr("data-fileindex"),m.uploadCache.content[f]=c[0],m.uploadCache.config[f]=p[0]||[],m.uploadCache.tags[f]=u[0]||[],m.uploadCache.append=n):(l=m.previewCache.add(c,p[0],u[0],n),o=m.previewCache.get(l,!1),s=e(document.createElement("div")).html(o).hide().insertAfter(a),d=s.find(".kv-zoom-cache"),d&&d.length&&d.insertAfter(a),a.fadeOut("slow",function(){var e=s.find(".file-preview-frame");e&&e.length&&e.insertBefore(a).fadeIn("slow").css("display:inline-block"),m._initPreviewActions(),m._clearFileInput(),t.cleanZoomCache(m.$preview.find("#zoom-"+a.attr("id"))),a.remove(),s.remove(),m._initSortable()})):(m.previewCache.set(c,p,u,n),m._initPreview(),m._initPreviewActions()))},_initSuccessThumbs:function(){var i=this;i.showPreview&&i._getThumbs(t.FRAMES+".file-preview-success").each(function(){var a=e(this),r=i.$preview,n=a.find(".kv-file-remove");n.removeAttr("disabled"),i._handler(n,"click",function(){var e=a.attr("id"),n=i._raise("filesuccessremove",[e,a.attr("data-fileindex")]);t.cleanMemory(a),n!==!1&&a.fadeOut("slow",function(){t.cleanZoomCache(r.find("#zoom-"+e)),a.remove(),i.getFrames().length||i.reset()})})})},_checkAsyncComplete:function(){var t,i,a=this;for(i=0;i<a.filestack.length;i++)if(a.filestack[i]&&(t=a.previewInitId+"-"+i,-1===e.inArray(t,a.uploadLog)))return!1;return a.uploadAsyncCount===a.uploadLog.length},_uploadExtra:function(t,i){var a=this,r=a._getExtraData(t,i);0!==r.length&&e.each(r,function(e,t){a.formdata.append(e,t)})},_uploadSingle:function(i,a){var r,n,o,l,s,d,c,p,u,f,m,v=this,g=v.getFileStack().length,h=new FormData,w=v.previewInitId+"-"+i,_=v.filestack.length>0||!e.isEmptyObject(v.uploadExtraData),b=e("#"+w).find(".file-thumb-progress"),C={id:w,index:i};v.formdata=h,v.showPreview&&(n=e("#"+w+":not(.file-preview-initial)"),l=n.find(".kv-file-upload"),s=n.find(".kv-file-remove"),b.show()),0===g||!_||l&&l.hasClass("disabled")||v._abort(C)||(m=function(e,t){d||v.updateStack(e,void 0),v.uploadLog.push(t),v._checkAsyncComplete()&&(v.fileBatchCompleted=!0)},o=function(){var e,i,a,r=v.uploadCache,n=0,o=v.cacheInitialPreview;v.fileBatchCompleted&&(o&&o.content&&(n=o.content.length),setTimeout(function(){var l=0===v.getFileStack(!0).length;if(v.showPreview){if(v.previewCache.set(r.content,r.config,r.tags,r.append),n){for(i=0;i<r.content.length;i++)a=i+n,o.content[a]=r.content[i],o.config.length&&(o.config[a]=r.config[i]),o.tags.length&&(o.tags[a]=r.tags[i]);v.initialPreview=t.cleanArray(o.content),v.initialPreviewConfig=t.cleanArray(o.config),v.initialPreviewThumbTags=t.cleanArray(o.tags)}else v.initialPreview=r.content,v.initialPreviewConfig=r.config,v.initialPreviewThumbTags=r.tags;v.cacheInitialPreview={},v.hasInitData&&(v._initPreview(),v._initPreviewActions())}v.unlock(l),l&&v._clearFileInput(),e=v.$preview.find(".file-preview-initial"),v.uploadAsync&&e.length&&(t.addCss(e,t.SORT_CSS),v._initSortable()),v._raise("filebatchuploadcomplete",[v.filestack,v._getExtraData()]),v.uploadCount=0,v.uploadStatus={},v.uploadLog=[],v._setProgress(101),v.ajaxAborted=!1},100))},c=function(o){r=v._getOutData(o),v.fileBatchCompleted=!1,a||(v.ajaxAborted=!1),v.showPreview&&(n.hasClass("file-preview-success")||(v._setThumbStatus(n,"Loading"),t.addCss(n,"file-uploading")),l.attr("disabled",!0),s.attr("disabled",!0)),a||v.lock(),v._raise("filepreupload",[r,w,i]),e.extend(!0,C,r),v._abort(C)&&(o.abort(),a||(v._setThumbStatus(n,"New"),n.removeClass("file-uploading"),l.removeAttr("disabled"),s.removeAttr("disabled"),v.unlock()),v._setProgressCancelled())},p=function(o,s,c){var p=v.showPreview&&n.attr("id")?n.attr("id"):w;r=v._getOutData(c,o),e.extend(!0,C,r),setTimeout(function(){t.isEmpty(o)||t.isEmpty(o.error)?(v.showPreview&&(v._setThumbStatus(n,"Success"),l.hide(),v._initUploadSuccess(o,n,a),v._setProgress(101,b)),v._raise("fileuploaded",[r,p,i]),a?m(i,p):v.updateStack(i,void 0)):(d=!0,v._showUploadError(o.error,C),v._setPreviewError(n,i,v.filestack[i],v.retryErrorUploads),v.retryErrorUploads||l.hide(),a&&m(i,p),v._setProgress(101,e("#"+p).find(".file-thumb-progress"),v.msgUploadError))},100)},u=function(){setTimeout(function(){v.showPreview&&(l.removeAttr("disabled"),s.removeAttr("disabled"),n.removeClass("file-uploading")),a?o():(v.unlock(!1),v._clearFileInput()),v._initSuccessThumbs()},100)},f=function(t,r,o){var s=v.ajaxOperations.uploadThumb,c=v._parseError(s,t,o,a&&v.filestack[i].name?v.filestack[i].name:null);d=!0,setTimeout(function(){a&&m(i,w),v.uploadStatus[w]=100,v._setPreviewError(n,i,v.filestack[i],v.retryErrorUploads),v.retryErrorUploads||l.hide(),e.extend(!0,C,v._getOutData(t)),v._setProgress(101,b,v.msgAjaxProgressError.replace("{operation}",s)),v._setProgress(101,e("#"+w).find(".file-thumb-progress"),v.msgUploadError),v._showUploadError(c,C)},100)},h.append(v.uploadFileAttr,v.filestack[i],v.filenames[i]),h.append("file_id",i),v._ajaxSubmit(c,p,u,f,w,i))},_uploadBatch:function(){var i,a,r,n,o,l=this,s=l.filestack,d=s.length,c={},p=l.filestack.length>0||!e.isEmptyObject(l.uploadExtraData);l.formdata=new FormData,0!==d&&p&&!l._abort(c)&&(o=function(){e.each(s,function(e){l.updateStack(e,void 0)}),l._clearFileInput()},i=function(i){l.lock();var a=l._getOutData(i);l.ajaxAborted=!1,l.showPreview&&l._getThumbs().each(function(){var i=e(this),a=i.find(".kv-file-upload"),r=i.find(".kv-file-remove");i.hasClass("file-preview-success")||(l._setThumbStatus(i,"Loading"),t.addCss(i,"file-uploading")),a.attr("disabled",!0),r.attr("disabled",!0)}),l._raise("filebatchpreupload",[a]),l._abort(a)&&(i.abort(),l._getThumbs().each(function(){var t=e(this),i=t.find(".kv-file-upload"),a=t.find(".kv-file-remove");t.hasClass("file-preview-loading")&&(l._setThumbStatus(t,"New"),t.removeClass("file-uploading")),i.removeAttr("disabled"),a.removeAttr("disabled")}),l._setProgressCancelled())},a=function(i,a,r){var n=l._getOutData(r,i),s=0,d=l._getThumbs(":not(.file-preview-success)"),c=t.isEmpty(i)||t.isEmpty(i.errorkeys)?[]:i.errorkeys;t.isEmpty(i)||t.isEmpty(i.error)?(l._raise("filebatchuploadsuccess",[n]),o(),l.showPreview?(d.each(function(){var t=e(this);l._setThumbStatus(t,"Success"),t.removeClass("file-uploading"),t.find(".kv-file-upload").hide().removeAttr("disabled")}),l._initUploadSuccess(i)):l.reset(),l._setProgress(101)):(l.showPreview&&(d.each(function(){var t=e(this),i=t.attr("data-fileindex");t.removeClass("file-uploading"),t.find(".kv-file-upload").removeAttr("disabled"),t.find(".kv-file-remove").removeAttr("disabled"),0===c.length||-1!==e.inArray(s,c)?(l._setPreviewError(t,i,l.filestack[i],l.retryErrorUploads),l.retryErrorUploads||(t.find(".kv-file-upload").hide(),l.updateStack(i,void 0))):(t.find(".kv-file-upload").hide(),l._setThumbStatus(t,"Success"),l.updateStack(i,void 0)),t.hasClass("file-preview-error")&&!l.retryErrorUploads||s++}),l._initUploadSuccess(i)),l._showUploadError(i.error,n,"filebatchuploaderror"),l._setProgress(101,l.$progress,l.msgUploadError))},n=function(){l.unlock(),l._initSuccessThumbs(),l._clearFileInput(),l._raise("filebatchuploadcomplete",[l.filestack,l._getExtraData()])},r=function(t,i,a){var r=l._getOutData(t),n=l.ajaxOperations.uploadBatch,o=l._parseError(n,t,a);l._showUploadError(o,r,"filebatchuploaderror"),l.uploadFileCount=d-1,l.showPreview&&(l._getThumbs().each(function(){var t=e(this),i=t.attr("data-fileindex");t.removeClass("file-uploading"),void 0!==l.filestack[i]&&l._setPreviewError(t)}),l._getThumbs().removeClass("file-uploading"),l._getThumbs(" .kv-file-upload").removeAttr("disabled"),l._getThumbs(" .kv-file-delete").removeAttr("disabled"),l._setProgress(101,l.$progress,l.msgAjaxProgressError.replace("{operation}",n)))},e.each(s,function(e,i){t.isEmpty(s[e])||l.formdata.append(l.uploadFileAttr,i,l.filenames[e])}),l._ajaxSubmit(i,a,n,r))},_uploadExtraOnly:function(){var e,i,a,r,n=this,o={};n.formdata=new FormData,n._abort(o)||(e=function(e){n.lock();var t=n._getOutData(e);n._raise("filebatchpreupload",[t]),n._setProgress(50),o.data=t,o.xhr=e,n._abort(o)&&(e.abort(),n._setProgressCancelled())},i=function(e,i,a){var r=n._getOutData(a,e);t.isEmpty(e)||t.isEmpty(e.error)?(n._raise("filebatchuploadsuccess",[r]),n._clearFileInput(),n._initUploadSuccess(e),n._setProgress(101)):n._showUploadError(e.error,r,"filebatchuploaderror")},a=function(){n.unlock(),n._clearFileInput(),n._raise("filebatchuploadcomplete",[n.filestack,n._getExtraData()])},r=function(e,t,i){var a=n._getOutData(e),r=n.ajaxOperations.uploadExtra,l=n._parseError(r,e,i);o.data=a,n._showUploadError(l,a,"filebatchuploaderror"),n._setProgress(101,n.$progress,n.msgAjaxProgressError.replace("{operation}",r))},n._ajaxSubmit(e,i,a,r))},_deleteFileIndex:function(i){var a=this,r=i.attr("data-fileindex");"init_"===r.substring(0,5)&&(r=parseInt(r.replace("init_","")),a.initialPreview=t.spliceArray(a.initialPreview,r),a.initialPreviewConfig=t.spliceArray(a.initialPreviewConfig,r),a.initialPreviewThumbTags=t.spliceArray(a.initialPreviewThumbTags,r),a.getFrames().each(function(){var t=e(this),i=t.attr("data-fileindex");"init_"===i.substring(0,5)&&(i=parseInt(i.replace("init_","")),i>r&&(i--,t.attr("data-fileindex","init_"+i)))}),a.uploadAsync&&(a.cacheInitialPreview=a.getPreview()))},_initFileActions:function(){var i=this,a=i.$preview;i.showPreview&&(i._initZoomButton(),i.getFrames(" .kv-file-remove").each(function(){var r,n,o,l,s=e(this),d=s.closest(t.FRAMES),c=d.attr("id"),p=d.attr("data-fileindex");i._handler(s,"click",function(){return l=i._raise("filepreremove",[c,p]),l!==!1&&i._validateMinCount()?(r=d.hasClass("file-preview-error"),t.cleanMemory(d),void d.fadeOut("slow",function(){t.cleanZoomCache(a.find("#zoom-"+c)),i.updateStack(p,void 0),i._clearObjects(d),d.remove(),c&&r&&i.$errorContainer.find('li[data-file-id="'+c+'"]').fadeOut("fast",function(){e(this).remove(),i._errorsExist()||i._resetErrors()}),i._clearFileInput();var l=i.getFileStack(!0),s=i.previewCache.count(),u=l.length,f=i.showPreview&&i.getFrames().length;0!==u||0!==s||f?(n=s+u,o=n>1?i._getMsgSelected(n):l[0]?i._getFileNames()[0]:"",i._setCaption(o)):i.reset(),i._raise("fileremoved",[c,p])})):!1})}),i.getFrames(" .kv-file-upload").each(function(){var a=e(this);i._handler(a,"click",function(){var e=a.closest(t.FRAMES),r=e.attr("data-fileindex");i.$progress.hide(),e.hasClass("file-preview-error")&&!i.retryErrorUploads||i._uploadSingle(r,!1)})}))},_initPreviewActions:function(){var i=this,a=i.$preview,r=i.deleteExtraData||{},n=t.FRAMES+" .kv-file-remove",o=i.fileActionSettings,l=o.removeClass,s=o.removeErrorClass,d=function(){var e=i.isAjaxUpload?i.previewCache.count():i.$element.get(0).files.length;a.find(t.FRAMES).length||e||(i._setCaption(""),i.reset(),i.initialCaption="")};i._initZoomButton(),a.find(n).each(function(){var n,o,c,p=e(this),u=p.data("url")||i.deleteUrl,f=p.data("key");if(!t.isEmpty(u)&&void 0!==f){var m,v,g,h,w=p.closest(t.FRAMES),_=i.previewCache.data,b=w.attr("data-fileindex");b=parseInt(b.replace("init_","")),g=t.isEmpty(_.config)&&t.isEmpty(_.config[b])?null:_.config[b],h=t.isEmpty(g)||t.isEmpty(g.extra)?r:g.extra,"function"==typeof h&&(h=h()),v={id:p.attr("id"),key:f,extra:h},n=function(e){i.ajaxAborted=!1,i._raise("filepredelete",[f,e,h]),i._abort()?e.abort():(p.removeClass(s),t.addCss(w,"file-uploading"),t.addCss(p,"disabled "+l))},o=function(e,r,n){var o,c;return t.isEmpty(e)||t.isEmpty(e.error)?(w.removeClass("file-uploading").addClass("file-deleted"),void w.fadeOut("slow",function(){b=parseInt(w.attr("data-fileindex").replace("init_","")),i.previewCache.unset(b),o=i.previewCache.count(),c=o>0?i._getMsgSelected(o):"",i._deleteFileIndex(w),i._setCaption(c),i._raise("filedeleted",[f,n,h]),t.cleanZoomCache(a.find("#zoom-"+w.attr("id"))),i._clearObjects(w),w.remove(),d()})):(v.jqXHR=n,v.response=e,i._showError(e.error,v,"filedeleteerror"),w.removeClass("file-uploading"),p.removeClass("disabled "+l).addClass(s),void d())},c=function(e,t,a){var r=i.ajaxOperations.deleteThumb,n=i._parseError(r,e,a);v.jqXHR=e,v.response={},i._showError(n,v,"filedeleteerror"),w.removeClass("file-uploading"),p.removeClass("disabled "+l).addClass(s),d()},i._mergeAjaxCallback("beforeSend",n,"delete"),i._mergeAjaxCallback("success",o,"delete"),i._mergeAjaxCallback("error",c,"delete"),m=e.extend(!0,{},{url:u,type:"POST",dataType:"json",data:e.extend(!0,{},{key:f},h)},i.ajaxDeleteSettings),i._handler(p,"click",function(){return i._validateMinCount()?(i.ajaxAborted=!1,i._raise("filebeforedelete",[f,h]),void(i.ajaxAborted instanceof Promise?i.ajaxAborted.then(function(t){t||e.ajax(m)}):i.ajaxAborted||e.ajax(m))):!1})}}),i.getFrames(" .kv-file-download").each(function(){var t=e(this);i._handler(t,"click",function(){var e=document.createElement("a");e.href=t.attr("data-url"),e.download=t.attr("data-caption"),e.target="_blank",e.click()})})},_hideFileIcon:function(){var e=this;e.overwriteInitial&&e.$captionContainer.removeClass("icon-visible")},_showFileIcon:function(){var e=this;t.addCss(e.$captionContainer,"icon-visible")},_getSize:function(t){var i,a,r,n=this,o=parseFloat(t),l=n.fileSizeGetter;return e.isNumeric(t)&&e.isNumeric(o)?("function"==typeof l?r=l(o):0===o?r="0.00 B":(i=Math.floor(Math.log(o)/Math.log(1024)),a=["B","KB","MB","GB","TB","PB","EB","ZB","YB"],r=1*(o/Math.pow(1024,i)).toFixed(2)+" "+a[i]),n._getLayoutTemplate("size").replace("{sizeText}",r)):""},_generatePreviewTemplate:function(i,a,r,n,o,l,s,d,c,p,u){var f,m,v=this,g=v.slug(r),h="",w="",_=window.innerWidth||document.documentElement.clientWidth||document.body.clientWidth,b=400>_?v.previewSettingsSmall[i]||v.defaults.previewSettingsSmall[i]:v.previewSettings[i]||v.defaults.previewSettings[i],C=c||v._renderFileFooter(g,s,"auto",l),y=v._getPreviewIcon(r),x="type-default",T=y&&v.preferIconicPreview,E=y&&v.preferIconicZoomPreview;return b&&e.each(b,function(e,t){w+=e+":"+t+";"}),m=function(a,l,s,c){var f=s?"zoom-"+o:o,m=v._getPreviewTemplate(a),h=(d||"")+" "+c;return v.frameClass&&(h=v.frameClass+" "+h),s&&(h=h.replace(" "+t.SORT_CSS,"")),m=v._parseFilePreviewIcon(m,r),"text"===a&&(l=t.htmlEncode(l)),"object"!==i||n||e.each(v.defaults.fileTypeSettings,function(e,t){"object"!==e&&"other"!==e&&t(r,n)&&(x="type-"+e)}),m.setTokens({previewId:f,caption:g,frameClass:h,type:n,fileindex:p,typeCss:x,footer:C,data:l,template:u||i,style:w?'style="'+w+'"':""})},p=p||o.slice(o.lastIndexOf("-")+1),v.fileActionSettings.showZoom&&(h=m(E?"other":i,a,!0,"kv-zoom-thumb")),h="\n"+v._getLayoutTemplate("zoomCache").replace("{zoomContent}",h),f=m(T?"other":i,a,!1,"kv-preview-thumb"),f+h},_previewDefault:function(i,a,r){var n=this,o=n.$preview;if(n.showPreview){var l,s=i?i.name:"",d=i?i.type:"",c=i.size||0,p=n.slug(s),u=r===!0&&!n.isAjaxUpload,f=t.objUrl.createObjectURL(i);n._clearDefaultPreview(),l=n._generatePreviewTemplate("other",f,s,d,a,u,c),o.append("\n"+l),n._setThumbAttr(a,p,c),r===!0&&n.isAjaxUpload&&n._setThumbStatus(e("#"+a),"Error")}},_previewFile:function(e,i,a,r,n,o){if(this.showPreview){var l,s=this,d=i?i.name:"",c=o.type,p=o.name,u=s._parseFileType(c,d),f=s.allowedPreviewTypes,m=s.allowedPreviewMimeTypes,v=s.$preview,g=i.size||0,h=f&&f.indexOf(u)>=0,w=m&&-1!==m.indexOf(c),_="text"===u||"html"===u||"image"===u?a.target.result:n;if("html"===u&&s.purifyHtml&&window.DOMPurify&&(_=window.DOMPurify.sanitize(_)),h||w){l=s._generatePreviewTemplate(u,_,d,c,r,!1,g),s._clearDefaultPreview(),v.append("\n"+l);var b=v.find("#"+r+" img");b.length&&s.autoOrientImage?t.validateOrientation(i,function(e){if(!e)return void s._validateImage(r,p,c,g,_);var a=v.find("#zoom-"+r+" img"),n="rotate-"+e;e>4&&(n+=b.width()>b.height()?" is-portrait-gt4":" is-landscape-gt4"),t.addCss(b,n),t.addCss(a,n),s._raise("fileimageoriented",{$img:b,file:i}),s._validateImage(r,p,c,g,_),t.adjustOrientedImage(b)}):s._validateImage(r,p,c,g,_)}else s._previewDefault(i,r);s._setThumbAttr(r,p,g),s._initSortable()}},_setThumbAttr:function(t,i,a){var r=this,n=e("#"+t);n.length&&(a=a&&a>0?r._getSize(a):"",n.data({caption:i,size:a}))},_setInitThumbAttr:function(){var e,i,a,r,n=this,o=n.previewCache.data,l=n.previewCache.count();if(0!==l)for(var s=0;l>s;s++)e=o.config[s],r=n.previewInitId+"-init_"+s,i=t.ifSet("caption",e,t.ifSet("filename",e)),a=t.ifSet("size",e),n._setThumbAttr(r,i,a)},_slugDefault:function(e){return t.isEmpty(e)?"":String(e).replace(/[\[\]\/\{}:;#%=\(\)\*\+\?\\\^\$\|<>&"']/g,"_")},_readFiles:function(i){this.reader=new FileReader;var a,r=this,n=r.$element,o=r.$preview,l=r.reader,s=r.$previewContainer,d=r.$previewStatus,c=r.msgLoading,p=r.msgProgress,u=r.previewInitId,f=i.length,m=r.fileTypeSettings,v=r.filestack.length,g=r.allowedFileTypes,h=g?g.length:0,w=r.allowedFileExtensions,_=t.isEmpty(w)?"":w.join(", "),b=r.maxFilePreviewSize&&parseFloat(r.maxFilePreviewSize),C=o.length&&(!b||isNaN(b)),y=function(t,n,o,l){var s,d=e.extend(!0,{},r._getOutData({},{},i),{id:o,index:l}),c={id:o,index:l,file:n,files:i};r._previewDefault(n,o,!0),r.isAjaxUpload?(r.addToStack(void 0),setTimeout(function(){a(l+1)},100)):f=0,r._initFileActions(),s=e("#"+o),s.find(".kv-file-upload").hide(),r.removeFromPreviewOnError&&s.remove(),r.isError=r.isAjaxUpload?r._showUploadError(t,d):r._showError(t,c),r._updateFileDetails(f)};r.loadedImages=[],r.totalImagesCount=0,e.each(i,function(e,t){var i=r.fileTypeSettings.image;i&&i(t.type)&&r.totalImagesCount++}),a=function(x){if(t.isEmpty(n.attr("multiple"))&&(f=1),x>=f)return r.isAjaxUpload&&r.filestack.length>0?r._raise("filebatchselected",[r.getFileStack()]):r._raise("filebatchselected",[i]),s.removeClass("file-thumb-loading"),void d.html("");var T,E,S,k,F,I,P,A,D,z,$,j,U=v+x,B=u+"-"+U,R=i[x],O=m.text,L=m.image,M=m.html,Z=R.name?r.slug(R.name):"",N=(R.size||0)/1e3,H="",V=t.objUrl.createObjectURL(R),W=0,q="",K=0,Y=function(){var e=p.setTokens({index:x+1,files:f,percent:50,name:Z});setTimeout(function(){d.html(e),r._updateFileDetails(f),a(x+1)},100),r._raise("fileloaded",[R,B,x,l])};if(h>0)for(E=0;h>E;E++)I=g[E],P=r.msgFileTypes[I]||I,q+=0===E?P:", "+P;if(Z===!1)return void a(x+1);if(0===Z.length)return S=r.msgInvalidFileName.replace("{name}",t.htmlEncode(R.name)),void y(S,R,B,x);if(t.isEmpty(w)||(H=new RegExp("\\.("+w.join("|")+")$","i")),T=N.toFixed(2),r.maxFileSize>0&&N>r.maxFileSize)return S=r.msgSizeTooLarge.setTokens({name:Z,size:T,maxSize:r.maxFileSize}),void y(S,R,B,x);if(null!==r.minFileSize&&N<=t.getNum(r.minFileSize))return S=r.msgSizeTooSmall.setTokens({name:Z,size:T,minSize:r.minFileSize}),void y(S,R,B,x);if(!t.isEmpty(g)&&t.isArray(g)){for(E=0;E<g.length;E+=1)k=g[E],A=m[k],W+=A&&"function"==typeof A&&A(R.type,R.name)?1:0;if(0===W)return S=r.msgInvalidFileType.setTokens({name:Z,types:q}),void y(S,R,B,x)}return 0!==W||t.isEmpty(w)||!t.isArray(w)||t.isEmpty(H)||(F=t.compare(Z,H),W+=t.isEmpty(F)?0:F.length,0!==W)?r.showPreview?!C&&N>b?(r.addToStack(R),s.addClass("file-thumb-loading"),r._previewDefault(R,B),r._initFileActions(),r._updateFileDetails(f),void a(x+1)):(o.length&&void 0!==FileReader?(D=O(R.type,Z),z=M(R.type,Z),$=L(R.type,Z),d.html(c.replace("{index}",x+1).replace("{files}",f)),s.addClass("file-thumb-loading"),l.onerror=function(e){r._errorHandler(e,Z)},l.onload=function(i){var a,n,o,s,d,c,p=[],u=function(e){var t=new FileReader;t.onerror=function(e){r._errorHandler(e,Z)},t.onload=function(e){r._previewFile(x,R,e,B,V,n),r._initFileActions(),Y()},e?t.readAsText(R,r.textEncoding):t.readAsDataURL(R)};if(n={name:Z,
type:R.type},e.each(m,function(e,t){"object"!==e&&"other"!==e&&t(R.type,Z)&&K++}),0===K){for(o=new Uint8Array(i.target.result),E=0;E<o.length;E++)s=o[E].toString(16),p.push(s);if(a=p.join("").toLowerCase().substring(0,8),c=t.getMimeType(a,"",""),t.isEmpty(c)&&(d=t.arrayBuffer2String(l.result),c=t.isSvg(d)?"image/svg+xml":t.getMimeType(a,d,R.type)),n={name:Z,type:c},D=O(c,""),z=M(c,""),$=L(c,""),j=D||z,j||$)return void u(j)}r._previewFile(x,R,i,B,V,n),r._initFileActions(),Y()},l.onprogress=function(e){if(e.lengthComputable){var t=e.loaded/e.total*100,i=Math.ceil(t);S=p.setTokens({index:x+1,files:f,percent:i,name:Z}),setTimeout(function(){d.html(S)},100)}},D||z?l.readAsText(R,r.textEncoding):$?l.readAsDataURL(R):l.readAsArrayBuffer(R)):(r._previewDefault(R,B),setTimeout(function(){a(x+1),r._updateFileDetails(f)},100),r._raise("fileloaded",[R,B,x,l])),void r.addToStack(R)):(r.isAjaxUpload&&r.addToStack(R),setTimeout(function(){a(x+1),r._updateFileDetails(f)},100),void r._raise("fileloaded",[R,B,x,l])):(S=r.msgInvalidFileExtension.setTokens({name:Z,extensions:_}),void y(S,R,B,x))},a(0),r._updateFileDetails(f,!1)},_updateFileDetails:function(e){var i=this,a=i.$element,r=i.getFileStack(),n=t.isIE(9)&&t.findFileName(a.val())||a[0].files[0]&&a[0].files[0].name||r.length&&r[0].name||"",o=i.slug(n),l=i.isAjaxUpload?r.length:e,s=i.previewCache.count()+l,d=1===l?o:i._getMsgSelected(s);i.isError?(i.$previewContainer.removeClass("file-thumb-loading"),i.$previewStatus.html(""),i.$captionContainer.removeClass("icon-visible")):i._showFileIcon(),i._setCaption(d,i.isError),i.$container.removeClass("file-input-new file-input-ajax-new"),1===arguments.length&&i._raise("fileselect",[e,o]),i.previewCache.count()&&i._initPreviewActions()},_setThumbStatus:function(e,t){var i=this;if(i.showPreview){var a="indicator"+t,r=a+"Title",n="file-preview-"+t.toLowerCase(),o=e.find(".file-upload-indicator"),l=i.fileActionSettings;e.removeClass("file-preview-success file-preview-error file-preview-loading"),"Success"===t&&e.find(".file-drag-handle").remove(),o.html(l[a]),o.attr("title",l[r]),e.addClass(n),"Error"!==t||i.retryErrorUploads||e.find(".kv-file-upload").attr("disabled",!0)}},_setProgressCancelled:function(){var e=this;e._setProgress(101,e.$progress,e.msgCancelled)},_setProgress:function(e,i,a){var r,n=this,o=Math.min(e,100),l=n.progressUploadThreshold,s=100>=e?n.progressTemplate:n.progressCompleteTemplate,d=100>o?n.progressTemplate:a?n.progressErrorTemplate:s;i=i||n.$progress,t.isEmpty(d)||(r=l&&o>l&&100>=e?d.setTokens({percent:l,status:n.msgUploadThreshold}):d.setTokens({percent:o,status:e>100?n.msgUploadEnd:o+"%"}),i.html(r),a&&i.find('[role="progressbar"]').html(a))},_setFileDropZoneTitle:function(){var e,i=this,a=i.$container.find(".file-drop-zone"),r=i.dropZoneTitle;i.isClickable&&(e=t.isEmpty(i.$element.attr("multiple"))?i.fileSingle:i.filePlural,r+=i.dropZoneClickTitle.replace("{files}",e)),a.find("."+i.dropZoneTitleClass).remove(),i.isAjaxUpload&&i.showPreview&&0!==a.length&&!(i.getFileStack().length>0)&&i.dropZoneEnabled&&(0===a.find(t.FRAMES).length&&t.isEmpty(i.defaultPreviewContent)&&a.prepend('<div class="'+i.dropZoneTitleClass+'">'+r+"</div>"),i.$container.removeClass("file-input-new"),t.addCss(i.$container,"file-input-ajax-new"))},_setAsyncUploadStatus:function(t,i,a){var r=this,n=0;r._setProgress(i,e("#"+t).find(".file-thumb-progress")),r.uploadStatus[t]=i,e.each(r.uploadStatus,function(e,t){n+=t}),r._setProgress(Math.floor(n/a))},_validateMinCount:function(){var e=this,t=e.isAjaxUpload?e.getFileStack().length:e.$element.get(0).files.length;return e.validateInitialCount&&e.minFileCount>0&&e._getFileCount(t-1)<e.minFileCount?(e._noFilesError({}),!1):!0},_getFileCount:function(e){var t=this,i=0;return t.validateInitialCount&&!t.overwriteInitial&&(i=t.previewCache.count(),e+=i),e},_getFileId:function(e){var t,i=this,a=i.generateFileId;return"function"==typeof a?a(e,event):e?(t=String(e.webkitRelativePath||e.fileName||e.name||null),t?e.size+"-"+t.replace(/[^0-9a-zA-Z_-]/gim,""):null):null},_getFileName:function(e){return e&&e.name?this.slug(e.name):void 0},_getFileIds:function(e){var t=this;return t.fileids.filter(function(t){return e?void 0!==t:void 0!==t&&null!==t})},_getFileNames:function(e){var t=this;return t.filenames.filter(function(t){return e?void 0!==t:void 0!==t&&null!==t})},_setPreviewError:function(e,t,i,a){var r=this;if(void 0!==t&&r.updateStack(t,i),r.showPreview){if(r.removeFromPreviewOnError&&!a)return void e.remove();r._setThumbStatus(e,"Error"),r._refreshUploadButton(e,a)}},_refreshUploadButton:function(e,t){var i=this,a=e.find(".kv-file-upload"),r=i.fileActionSettings,n=r.uploadIcon,o=r.uploadTitle;a.length&&(t&&(n=r.uploadRetryIcon,o=r.uploadRetryTitle),a.attr("title",o).html(n))},_checkDimensions:function(e,i,a,r,n,o,l){var s,d,c,p,u=this,f="Small"===i?"min":"max",m=u[f+"Image"+o];!t.isEmpty(m)&&a.length&&(c=a[0],d="Width"===o?c.naturalWidth||c.width:c.naturalHeight||c.height,p="Small"===i?d>=m:m>=d,p||(s=u["msgImage"+o+i].setTokens({name:n,size:m}),u._showUploadError(s,l),u._setPreviewError(r,e,null)))},_validateImage:function(t,i,a,r,n){var o,l,s,d,c=this,p=c.$preview,u=p.find("#"+t),f=u.attr("data-fileindex"),m=u.find("img");i=i||"Untitled",m.one("load",function(){l=u.width(),s=p.width(),l>s&&m.css("width","100%"),o={ind:f,id:t},c._checkDimensions(f,"Small",m,u,i,"Width",o),c._checkDimensions(f,"Small",m,u,i,"Height",o),c.resizeImage||(c._checkDimensions(f,"Large",m,u,i,"Width",o),c._checkDimensions(f,"Large",m,u,i,"Height",o)),c._raise("fileimageloaded",[t]);try{d=window.piexif?window.piexif.load(n):null}catch(e){d=null}c.loadedImages.push({ind:f,img:m,thumb:u,pid:t,typ:a,siz:r,validated:!1,imgData:n,exifObj:d}),u.data("exif",d),c._validateAllImages()}).one("error",function(){c._raise("fileimageloaderror",[t])}).each(function(){this.complete?e(this).trigger("load"):this.error&&e(this).trigger("error")})},_validateAllImages:function(){var e,t,i,a=this,r={val:0},n=a.loadedImages.length,o=a.resizeIfSizeMoreThan;if(n===a.totalImagesCount&&(a._raise("fileimagesloaded"),a.resizeImage))for(e=0;e<a.loadedImages.length;e++)t=a.loadedImages[e],t.validated||(i=t.siz,i&&i>1e3*o&&a._getResizedImage(t,r,n),a.loadedImages[e].validated=!0)},_getResizedImage:function(i,a,r){var n,o,l,s,d,c,p,u=this,f=e(i.img)[0],m=f.naturalWidth,v=f.naturalHeight,g=1,h=u.maxImageWidth||m,w=u.maxImageHeight||v,_=!(!m||!v),b=u.imageCanvas,C=u.imageCanvasContext,y=i.typ,x=i.pid,T=i.ind,E=i.thumb,S=i.exifObj;if(d=function(e,t,i){u.isAjaxUpload?u._showUploadError(e,t,i):u._showError(e,t,i),u._setPreviewError(E,T)},(!u.filestack[T]||!_||h>=m&&w>=v)&&(_&&u.filestack[T]&&u._raise("fileimageresized",[x,T]),a.val++,a.val===r&&u._raise("fileimagesresized"),!_))return void d(u.msgImageResizeError,{id:x,index:T},"fileimageresizeerror");y=y||u.resizeDefaultImageType,o=m>h,l=v>w,g="width"===u.resizePreference?o?h/m:l?w/v:1:l?w/v:o?h/m:1,u._resetCanvas(),m*=g,v*=g,b.width=m,b.height=v;try{C.drawImage(f,0,0,m,v),s=b.toDataURL(y,u.resizeQuality),S&&(p=window.piexif.dump(S),s=window.piexif.insert(p,s)),n=t.dataURI2Blob(s),u.filestack[T]=n,u._raise("fileimageresized",[x,T]),a.val++,a.val===r&&u._raise("fileimagesresized",[void 0,void 0]),n instanceof Blob||d(u.msgImageResizeError,{id:x,index:T},"fileimageresizeerror")}catch(k){a.val++,a.val===r&&u._raise("fileimagesresized",[void 0,void 0]),c=u.msgImageResizeException.replace("{errors}",k.message),d(c,{id:x,index:T},"fileimageresizeexception")}},_initBrowse:function(e){var t=this;t.showBrowse?(t.$btnFile=e.find(".btn-file"),t.$btnFile.append(t.$element)):t.$element.hide()},_initCaption:function(){var e=this,i=e.initialCaption||"";return e.overwriteInitial||t.isEmpty(i)?(e.$caption.val(""),!1):(e._setCaption(i),!0)},_setCaption:function(i,a){var r,n,o,l,s,d=this,c=d.getFileStack();if(d.$caption.length){if(d.$captionContainer.removeClass("icon-visible"),a)r=e("<div>"+d.msgValidationError+"</div>").text(),l=c.length,s=l?1===l&&c[0]?d._getFileNames()[0]:d._getMsgSelected(l):d._getMsgSelected(d.msgNo),n=t.isEmpty(i)?s:i,o='<span class="'+d.msgValidationErrorClass+'">'+d.msgValidationErrorIcon+"</span>";else{if(t.isEmpty(i))return;r=e("<div>"+i+"</div>").text(),n=r,o=d._getLayoutTemplate("fileIcon")}d.$captionContainer.addClass("icon-visible"),d.$caption.attr("title",r).val(n),d.$captionIcon.html(o)}},_createContainer:function(){var t=this,i={"class":"file-input file-input-new"+(t.rtl?" kv-rtl":"")},a=e(document.createElement("div")).attr(i).html(t._renderMain());return t.$element.before(a),t._initBrowse(a),t.theme&&a.addClass("theme-"+t.theme),a},_refreshContainer:function(){var e=this,t=e.$container;t.before(e.$element),t.html(e._renderMain()),e._initBrowse(t),e._validateDisabled()},_validateDisabled:function(){var e=this;e.$caption.attr({readonly:e.isDisabled})},_renderMain:function(){var e=this,t=e.isAjaxUpload&&e.dropZoneEnabled?" file-drop-zone":"file-drop-disabled",i=e.showClose?e._getLayoutTemplate("close"):"",a=e.showPreview?e._getLayoutTemplate("preview").setTokens({"class":e.previewClass,dropClass:t}):"",r=e.isDisabled?e.captionClass+" file-caption-disabled":e.captionClass,n=e.captionTemplate.setTokens({"class":r+" kv-fileinput-caption"});return e.mainTemplate.setTokens({"class":e.mainClass+(!e.showBrowse&&e.showCaption?" no-browse":""),preview:a,close:i,caption:n,upload:e._renderButton("upload"),remove:e._renderButton("remove"),cancel:e._renderButton("cancel"),browse:e._renderButton("browse")})},_renderButton:function(e){var i=this,a=i._getLayoutTemplate("btnDefault"),r=i[e+"Class"],n=i[e+"Title"],o=i[e+"Icon"],l=i[e+"Label"],s=i.isDisabled?" disabled":"",d="button";switch(e){case"remove":if(!i.showRemove)return"";break;case"cancel":if(!i.showCancel)return"";r+=" kv-hidden";break;case"upload":if(!i.showUpload)return"";i.isAjaxUpload&&!i.isDisabled?a=i._getLayoutTemplate("btnLink").replace("{href}",i.uploadUrl):d="submit";break;case"browse":if(!i.showBrowse)return"";a=i._getLayoutTemplate("btnBrowse");break;default:return""}return r+="browse"===e?" btn-file":" fileinput-"+e+" fileinput-"+e+"-button",t.isEmpty(l)||(l=' <span class="'+i.buttonLabelClass+'">'+l+"</span>"),a.setTokens({type:d,css:r,title:n,status:s,icon:o,label:l})},_renderThumbProgress:function(){var e=this;return'<div class="file-thumb-progress kv-hidden">'+e.progressTemplate.setTokens({percent:"0",status:e.msgUploadBegin})+"</div>"},_renderFileFooter:function(e,i,a,r){var n,o=this,l=o.fileActionSettings,s=l.showRemove,d=l.showDrag,c=l.showUpload,p=l.showZoom,u=o._getLayoutTemplate("footer"),f=o._getLayoutTemplate("indicator"),m=r?l.indicatorError:l.indicatorNew,v=r?l.indicatorErrorTitle:l.indicatorNewTitle,g=f.setTokens({indicator:m,indicatorTitle:v});return i=o._getSize(i),n=o.isAjaxUpload?u.setTokens({actions:o._renderFileActions(c,!1,s,p,d,!1,!1,!1),caption:e,size:i,width:a,progress:o._renderThumbProgress(),indicator:g}):u.setTokens({actions:o._renderFileActions(!1,!1,!1,p,d,!1,!1,!1),caption:e,size:i,width:a,progress:"",indicator:g}),n=t.replaceTags(n,o.previewThumbTags)},_renderFileActions:function(e,t,i,a,r,n,o,l,s,d,c){if(!(e||t||i||a||r))return"";var p,u=this,f=o===!1?"":' data-url="'+o+'"',m=l===!1?"":' data-key="'+l+'"',v="",g="",h="",w="",_="",b=u._getLayoutTemplate("actions"),C=u.fileActionSettings,y=u.otherActionButtons.setTokens({dataKey:m,key:l}),x=n?C.removeClass+" disabled":C.removeClass;return i&&(v=u._getLayoutTemplate("actionDelete").setTokens({removeClass:x,removeIcon:C.removeIcon,removeTitle:C.removeTitle,dataUrl:f,dataKey:m,key:l})),e&&(g=u._getLayoutTemplate("actionUpload").setTokens({uploadClass:C.uploadClass,uploadIcon:C.uploadIcon,uploadTitle:C.uploadTitle})),t&&(h=u._getLayoutTemplate("actionDownload").setTokens({downloadClass:C.downloadClass,downloadIcon:C.downloadIcon,downloadTitle:C.downloadTitle,downloadUrl:d||u.initialPreviewDownloadUrl}),h=h.setTokens({filename:c,key:l})),a&&(w=u._getLayoutTemplate("actionZoom").setTokens({zoomClass:C.zoomClass,zoomIcon:C.zoomIcon,zoomTitle:C.zoomTitle})),r&&s&&(p="drag-handle-init "+C.dragClass,_=u._getLayoutTemplate("actionDrag").setTokens({dragClass:p,dragTitle:C.dragTitle,dragIcon:C.dragIcon})),b.setTokens({"delete":v,upload:g,download:h,zoom:w,drag:_,other:y})},_browse:function(e){var t=this;t._raise("filebrowse"),e&&e.isDefaultPrevented()||(t.isError&&!t.isAjaxUpload&&t.clear(),t.$captionContainer.focus())},_filterDuplicate:function(e,t,i){var a=this,r=a._getFileId(e);r&&i&&i.indexOf(r)>-1||(i||(i=[]),t.push(e),i.push(r))},_change:function(i){var a=this,r=a.$element;if(!a.isAjaxUpload&&t.isEmpty(r.val())&&a.fileInputCleared)return void(a.fileInputCleared=!1);a.fileInputCleared=!1;var n,o,l,s,d=[],c=arguments.length>1,p=a.isAjaxUpload,u=c?i.originalEvent.dataTransfer.files:r.get(0).files,f=a.filestack.length,m=t.isEmpty(r.attr("multiple")),v=m&&f>0,g=0,h=a._getFileIds(),w=function(t,i,r,n){var o=e.extend(!0,{},a._getOutData({},{},u),{id:r,index:n}),l={id:r,index:n,file:i,files:u};return a.isAjaxUpload?a._showUploadError(t,o):a._showError(t,l)};if(a.reader=null,a._resetUpload(),a._hideFileIcon(),a.isAjaxUpload&&a.$container.find(".file-drop-zone ."+a.dropZoneTitleClass).remove(),c?e.each(u,function(e,t){t&&!t.type&&void 0!==t.size&&t.size%4096===0?g++:a._filterDuplicate(t,d,h)}):(u=i.target&&void 0===i.target.files?i.target.value?[{name:i.target.value.replace(/^.+\\/,"")}]:[]:i.target.files||{},p?e.each(u,function(e,t){a._filterDuplicate(t,d,h)}):d=u),t.isEmpty(d)||0===d.length)return p||a.clear(),a._showFolderError(g),void a._raise("fileselectnone");if(a._resetErrors(),s=d.length,o=a._getFileCount(a.isAjaxUpload?a.getFileStack().length+s:s),a.maxFileCount>0&&o>a.maxFileCount){if(!a.autoReplace||s>a.maxFileCount)return l=a.autoReplace&&s>a.maxFileCount?s:o,n=a.msgFilesTooMany.replace("{m}",a.maxFileCount).replace("{n}",l),a.isError=w(n,null,null,null),a.$captionContainer.removeClass("icon-visible"),a._setCaption("",!0),void a.$container.removeClass("file-input-new file-input-ajax-new");o>a.maxFileCount&&a._resetPreviewThumbs(p)}else!p||v?(a._resetPreviewThumbs(!1),v&&a.clearStack()):!p||0!==f||a.previewCache.count()&&!a.overwriteInitial||a._resetPreviewThumbs(!0);a.isPreviewable?a._readFiles(d):a._updateFileDetails(1),a._showFolderError(g)},_abort:function(t){var i,a=this;return a.ajaxAborted&&"object"==typeof a.ajaxAborted&&void 0!==a.ajaxAborted.message?(i=e.extend(!0,{},a._getOutData(),t),i.abortData=a.ajaxAborted.data||{},i.abortMessage=a.ajaxAborted.message,a._setProgress(101,a.$progress,a.msgCancelled),a._showUploadError(a.ajaxAborted.message,i,"filecustomerror"),a.cancel(),!0):!!a.ajaxAborted},_resetFileStack:function(){var i=this,a=0,r=[],n=[],o=[];i._getThumbs().each(function(){var l,s=e(this),d=s.attr("data-fileindex"),c=i.filestack[d],p=s.attr("id");"-1"!==d&&-1!==d&&(void 0!==c?(r[a]=c,n[a]=i._getFileName(c),o[a]=i._getFileId(c),s.attr({id:i.previewInitId+"-"+a,"data-fileindex":a}),a++):(l="uploaded-"+t.uniqId(),s.attr({id:l,"data-fileindex":"-1"}),i.$preview.find("#zoom-"+p).attr("id","zoom-"+l)))}),i.filestack=r,i.filenames=n,i.fileids=o},_isFileSelectionValid:function(e){var t=this;return e=e||0,t.required&&!t.getFilesCount()?(t.$errorContainer.html(""),t._showUploadError(t.msgFileRequired),!1):t.minFileCount>0&&t._getFileCount(e)<t.minFileCount?(t._noFilesError({}),!1):!0},clearStack:function(){var e=this;return e.filestack=[],e.filenames=[],e.fileids=[],e.$element},updateStack:function(e,t){var i=this;return i.filestack[e]=t,i.filenames[e]=i._getFileName(t),i.fileids[e]=t&&i._getFileId(t)||null,i.$element},addToStack:function(e){var t=this;return t.filestack.push(e),t.filenames.push(t._getFileName(e)),t.fileids.push(t._getFileId(e)),t.$element},getFileStack:function(e){var t=this;return t.filestack.filter(function(t){return e?void 0!==t:void 0!==t&&null!==t})},getFilesCount:function(){var e=this,t=e.isAjaxUpload?e.getFileStack().length:e.$element.get(0).files.length;return e._getFileCount(t)},lock:function(){var e=this;return e._resetErrors(),e.disable(),e.showRemove&&e.$container.find(".fileinput-remove").hide(),e.showCancel&&e.$container.find(".fileinput-cancel").show(),e._raise("filelock",[e.filestack,e._getExtraData()]),e.$element},unlock:function(e){var t=this;return void 0===e&&(e=!0),t.enable(),t.showCancel&&t.$container.find(".fileinput-cancel").hide(),t.showRemove&&t.$container.find(".fileinput-remove").show(),e&&t._resetFileStack(),t._raise("fileunlock",[t.filestack,t._getExtraData()]),t.$element},cancel:function(){var t,i=this,a=i.ajaxRequests,r=a.length;if(r>0)for(t=0;r>t;t+=1)i.cancelling=!0,a[t].abort();return i._setProgressCancelled(),i._getThumbs().each(function(){var t=e(this),a=t.attr("data-fileindex");t.removeClass("file-uploading"),void 0!==i.filestack[a]&&(t.find(".kv-file-upload").removeClass("disabled").removeAttr("disabled"),t.find(".kv-file-remove").removeClass("disabled").removeAttr("disabled")),i.unlock()}),i.$element},clear:function(){var i,a=this;if(a._raise("fileclear"))return a.$btnUpload.removeAttr("disabled"),a._getThumbs().find("video,audio,img").each(function(){t.cleanMemory(e(this))}),a._resetUpload(),a.clearStack(),a._clearFileInput(),a._resetErrors(!0),a._hasInitialPreview()?(a._showFileIcon(),a._resetPreview(),a._initPreviewActions(),a.$container.removeClass("file-input-new")):(a._getThumbs().each(function(){a._clearObjects(e(this))}),a.isAjaxUpload&&(a.previewCache.data={}),a.$preview.html(""),i=!a.overwriteInitial&&a.initialCaption.length>0?a.initialCaption:"",a.$caption.attr("title","").val(i),t.addCss(a.$container,"file-input-new"),a._validateDefaultPreview()),0===a.$container.find(t.FRAMES).length&&(a._initCaption()||a.$captionContainer.removeClass("icon-visible")),a._hideFileIcon(),a._raise("filecleared"),a.$captionContainer.focus(),a._setFileDropZoneTitle(),a.$element},reset:function(){var e=this;if(e._raise("filereset"))return e._resetPreview(),e.$container.find(".fileinput-filename").text(""),t.addCss(e.$container,"file-input-new"),(e.getFrames().length||e.isAjaxUpload&&e.dropZoneEnabled)&&e.$container.removeClass("file-input-new"),e.clearStack(),e.formdata={},e._setFileDropZoneTitle(),e.$element},disable:function(){var e=this;return e.isDisabled=!0,e._raise("filedisabled"),e.$element.attr("disabled","disabled"),e.$container.find(".kv-fileinput-caption").addClass("file-caption-disabled"),e.$container.find(".fileinput-remove, .fileinput-upload, .file-preview-frame button").attr("disabled",!0),t.addCss(e.$container.find(".btn-file"),"disabled"),e._initDragDrop(),e.$element},enable:function(){var e=this;return e.isDisabled=!1,e._raise("fileenabled"),e.$element.removeAttr("disabled"),e.$container.find(".kv-fileinput-caption").removeClass("file-caption-disabled"),e.$container.find(".fileinput-remove, .fileinput-upload, .file-preview-frame button").removeAttr("disabled"),e.$container.find(".btn-file").removeClass("disabled"),e._initDragDrop(),e.$element},upload:function(){var i,a,r,n=this,o=n.getFileStack().length,l=!e.isEmptyObject(n._getExtraData());if(n.isAjaxUpload&&!n.isDisabled&&n._isFileSelectionValid(o)){if(n._resetUpload(),0===o&&!l)return void n._showUploadError(n.msgUploadEmpty);if(n.$progress.show(),n.uploadCount=0,n.uploadStatus={},n.uploadLog=[],n.lock(),n._setProgress(2),0===o&&l)return void n._uploadExtraOnly();if(r=n.filestack.length,n.hasInitData=!1,!n.uploadAsync)return n._uploadBatch(),n.$element;for(a=n._getOutData(),n._raise("filebatchpreupload",[a]),n.fileBatchCompleted=!1,n.uploadCache={content:[],config:[],tags:[],append:!0},n.uploadAsyncCount=n.getFileStack().length,i=0;r>i;i++)n.uploadCache.content[i]=null,n.uploadCache.config[i]=null,n.uploadCache.tags[i]=null;for(n.$preview.find(".file-preview-initial").removeClass(t.SORT_CSS),n._initSortable(),n.cacheInitialPreview=n.getPreview(),i=0;r>i;i++)n.filestack[i]&&n._uploadSingle(i,!0)}},destroy:function(){var t=this,i=t.$form,a=t.$container,r=t.$element,n=t.namespace;return e(document).off(n),e(window).off(n),i&&i.length&&i.off(n),t.isAjaxUpload&&t._clearFileInput(),t._cleanup(),t._initPreviewCache(),r.insertBefore(a).off(n).removeData(),a.off().remove(),r},refresh:function(i,a){var r=this,n=r.$element;return i="object"!=typeof i||t.isEmpty(i)?r.options:e.extend(!0,{},r.options,i),r._init(i,!0),r._listen(),a&&n.trigger("change"+r.namespace),n},zoom:function(e){var i=this,a=i._getFrame(e),r=i.$modal;a&&(t.initModal(r),r.html(i._getModalContent()),i._setZoomContent(a),r.modal("show"),i._initZoomButtons())},getExif:function(e){var t=this,i=t._getFrame(e);return i&&i.data("exif")||null},getFrames:function(e){var i=this;return e=e||"",i.$preview.find(t.FRAMES+e)},getPreview:function(){var e=this;return{content:e.initialPreview,config:e.initialPreviewConfig,tags:e.initialPreviewThumbTags}}},e.fn.fileinput=function(a){if(t.hasFileAPISupport()||t.isIE(9)){var r=Array.apply(null,arguments),n=[];switch(r.shift(),this.each(function(){var o,l=e(this),s=l.data("fileinput"),d="object"==typeof a&&a,c=d.theme||l.data("theme"),p={},u={},f=d.language||l.data("language")||e.fn.fileinput.defaults.language||"en";s||(c&&(u=e.fn.fileinputThemes[c]||{}),"en"===f||t.isEmpty(e.fn.fileinputLocales[f])||(p=e.fn.fileinputLocales[f]||{}),o=e.extend(!0,{},e.fn.fileinput.defaults,u,e.fn.fileinputLocales.en,p,d,l.data()),s=new i(this,o),l.data("fileinput",s)),"string"==typeof a&&n.push(s[a].apply(s,r))}),n.length){case 0:return this;case 1:return n[0];default:return n}}},e.fn.fileinput.defaults={language:"en",showCaption:!0,showBrowse:!0,showPreview:!0,showRemove:!0,showUpload:!0,showCancel:!0,showClose:!0,showUploadedThumbs:!0,browseOnZoneClick:!1,autoReplace:!1,autoOrientImage:!0,required:!1,rtl:!1,hideThumbnailContent:!1,generateFileId:null,previewClass:"",captionClass:"",frameClass:"krajee-default",mainClass:"file-caption-main",mainTemplate:null,purifyHtml:!0,fileSizeGetter:null,initialCaption:"",initialPreview:[],initialPreviewDelimiter:"*$$*",initialPreviewAsData:!1,initialPreviewFileType:"image",initialPreviewConfig:[],initialPreviewThumbTags:[],previewThumbTags:{},initialPreviewShowDelete:!0,initialPreviewDownloadUrl:"",removeFromPreviewOnError:!1,deleteUrl:"",deleteExtraData:{},overwriteInitial:!0,previewZoomButtonIcons:{prev:'<i class="glyphicon glyphicon-triangle-left"></i>',next:'<i class="glyphicon glyphicon-triangle-right"></i>',toggleheader:'<i class="glyphicon glyphicon-resize-vertical"></i>',fullscreen:'<i class="glyphicon glyphicon-fullscreen"></i>',borderless:'<i class="glyphicon glyphicon-resize-full"></i>',close:'<i class="glyphicon glyphicon-remove"></i>'},previewZoomButtonClasses:{prev:"btn btn-navigate",next:"btn btn-navigate",toggleheader:"btn btn-kv btn-default btn-outline-secondary",fullscreen:"btn btn-kv btn-default btn-outline-secondary",borderless:"btn btn-kv btn-default btn-outline-secondary",close:"btn btn-kv btn-default btn-outline-secondary"},preferIconicPreview:!1,preferIconicZoomPreview:!1,allowedPreviewTypes:void 0,allowedPreviewMimeTypes:null,allowedFileTypes:null,allowedFileExtensions:null,defaultPreviewContent:null,customLayoutTags:{},customPreviewTags:{},previewFileIcon:'<i class="glyphicon glyphicon-file"></i>',previewFileIconClass:"file-other-icon",previewFileIconSettings:{},previewFileExtSettings:{},buttonLabelClass:"hidden-xs",browseIcon:'<i class="glyphicon glyphicon-folder-open"></i>&nbsp;',browseClass:"btn btn-primary",removeIcon:'<i class="glyphicon glyphicon-trash"></i>',removeClass:"btn btn-default btn-secondary",cancelIcon:'<i class="glyphicon glyphicon-ban-circle"></i>',cancelClass:"btn btn-default btn-secondary",uploadIcon:'<i class="glyphicon glyphicon-upload"></i>',uploadClass:"btn btn-default btn-secondary",uploadUrl:null,uploadUrlThumb:null,uploadAsync:!0,uploadExtraData:{},zoomModalHeight:480,minImageWidth:null,minImageHeight:null,maxImageWidth:null,maxImageHeight:null,resizeImage:!1,resizePreference:"width",resizeQuality:.92,resizeDefaultImageType:"image/jpeg",resizeIfSizeMoreThan:0,minFileSize:0,maxFileSize:0,maxFilePreviewSize:25600,minFileCount:0,maxFileCount:0,validateInitialCount:!1,msgValidationErrorClass:"text-danger",msgValidationErrorIcon:'<i class="glyphicon glyphicon-exclamation-sign"></i> ',msgErrorClass:"file-error-message",progressThumbClass:"progress-bar bg-success progress-bar-success progress-bar-striped active",progressClass:"progress-bar bg-success progress-bar-success progress-bar-striped active",progressCompleteClass:"progress-bar bg-success progress-bar-success",progressErrorClass:"progress-bar bg-danger progress-bar-danger",progressUploadThreshold:99,previewFileType:"image",elCaptionContainer:null,elCaptionText:null,elPreviewContainer:null,elPreviewImage:null,elPreviewStatus:null,elErrorContainer:null,errorCloseButton:t.closeButton("kv-error-close"),slugCallback:null,dropZoneEnabled:!0,dropZoneTitleClass:"file-drop-zone-title",fileActionSettings:{},otherActionButtons:"",textEncoding:"UTF-8",ajaxSettings:{},ajaxDeleteSettings:{},showAjaxErrorDetails:!0,mergeAjaxCallbacks:!1,mergeAjaxDeleteCallbacks:!1,retryErrorUploads:!0},e.fn.fileinputLocales.en={fileSingle:"file",filePlural:"files",browseLabel:"Browse &hellip;",removeLabel:"Remove",removeTitle:"Clear selected files",cancelLabel:"Cancel",cancelTitle:"Abort ongoing upload",uploadLabel:"Upload",uploadTitle:"Upload selected files",msgNo:"No",msgNoFilesSelected:"No files selected",msgCancelled:"Cancelled",msgPlaceholder:"Select {files}...",msgZoomModalHeading:"Detailed Preview",msgFileRequired:"You must select a file to upload.",msgSizeTooSmall:'File "{name}" (<b>{size} KB</b>) is too small and must be larger than <b>{minSize} KB</b>.',msgSizeTooLarge:'File "{name}" (<b>{size} KB</b>) exceeds maximum allowed upload size of <b>{maxSize} KB</b>.',msgFilesTooLess:"You must select at least <b>{n}</b> {files} to upload.",msgFilesTooMany:"Number of files selected for upload <b>({n})</b> exceeds maximum allowed limit of <b>{m}</b>.",msgFileNotFound:'File "{name}" not found!',msgFileSecured:'Security restrictions prevent reading the file "{name}".',msgFileNotReadable:'File "{name}" is not readable.',msgFilePreviewAborted:'File preview aborted for "{name}".',msgFilePreviewError:'An error occurred while reading the file "{name}".',msgInvalidFileName:'Invalid or unsupported characters in file name "{name}".',msgInvalidFileType:'Invalid type for file "{name}". Only "{types}" files are supported.',msgInvalidFileExtension:'Invalid extension for file "{name}". Only "{extensions}" files are supported.',msgFileTypes:{image:"image",html:"HTML",text:"text",video:"video",audio:"audio",flash:"flash",pdf:"PDF",object:"object"},msgUploadAborted:"The file upload was aborted",msgUploadThreshold:"Processing...",msgUploadBegin:"Initializing...",msgUploadEnd:"Done",msgUploadEmpty:"No valid data available for upload.",msgUploadError:"Error",msgValidationError:"Validation Error",msgLoading:"Loading file {index} of {files} &hellip;",msgProgress:"Loading file {index} of {files} - {name} - {percent}% completed.",msgSelected:"{n} {files} selected",msgFoldersNotAllowed:"Drag & drop files only! {n} folder(s) dropped were skipped.",msgImageWidthSmall:'Width of image file "{name}" must be at least {size} px.',msgImageHeightSmall:'Height of image file "{name}" must be at least {size} px.',msgImageWidthLarge:'Width of image file "{name}" cannot exceed {size} px.',msgImageHeightLarge:'Height of image file "{name}" cannot exceed {size} px.',msgImageResizeError:"Could not get the image dimensions to resize.",msgImageResizeException:"Error while resizing the image.<pre>{errors}</pre>",msgAjaxError:"Something went wrong with the {operation} operation. Please try again later!",msgAjaxProgressError:"{operation} failed",ajaxOperations:{deleteThumb:"file delete",uploadThumb:"file upload",uploadBatch:"batch file upload",uploadExtra:"form data upload"},dropZoneTitle:"Drag & drop files here &hellip;",dropZoneClickTitle:"<br>(or click to select {files})",previewZoomButtonTitles:{prev:"View previous file",next:"View next file",toggleheader:"Toggle header",fullscreen:"Toggle full screen",borderless:"Toggle borderless mode",close:"Close detailed preview"}},e.fn.fileinput.Constructor=i,e(document).ready(function(){var t=e("input.file[type=file]");t.length&&t.fileinput()})});
/*!
 * bootstrap-fileinput v4.4.6
 * http://plugins.krajee.com/file-input
 *
 * Font Awesome icon theme configuration for bootstrap-fileinput. Requires font awesome assets to be loaded.
 *
 * Author: Kartik Visweswaran
 * Copyright: 2014 - 2017, Kartik Visweswaran, Krajee.com
 *
 * Licensed under the BSD 3-Clause
 * https://github.com/kartik-v/bootstrap-fileinput/blob/master/LICENSE.md
 */!function(a){"use strict";a.fn.fileinputThemes.fa={fileActionSettings:{removeIcon:'<i class="fa fa-trash"></i>',uploadIcon:'<i class="fa fa-upload"></i>',uploadRetryIcon:'<i class="fa fa-repeat"></i>',zoomIcon:'<i class="fa fa-search-plus"></i>',dragIcon:'<i class="fa fa-bars"></i>',indicatorNew:'<i class="fa fa-plus-circle text-warning"></i>',indicatorSuccess:'<i class="fa fa-check-circle text-success"></i>',indicatorError:'<i class="fa fa-exclamation-circle text-danger"></i>',indicatorLoading:'<i class="fa fa-hourglass text-muted"></i>'},layoutTemplates:{fileIcon:'<i class="fa fa-file kv-caption-icon"></i> '},previewZoomButtonIcons:{prev:'<i class="fa fa-caret-left fa-lg"></i>',next:'<i class="fa fa-caret-right fa-lg"></i>',toggleheader:'<i class="fa fa-arrows-v"></i>',fullscreen:'<i class="fa fa-arrows-alt"></i>',borderless:'<i class="fa fa-external-link"></i>',close:'<i class="fa fa-remove"></i>'},previewFileIcon:'<i class="fa fa-file"></i>',browseIcon:'<i class="fa fa-folder-open"></i>',removeIcon:'<i class="fa fa-trash"></i>',cancelIcon:'<i class="fa fa-ban"></i>',uploadIcon:'<i class="fa fa-upload"></i>',msgValidationErrorIcon:'<i class="fa fa-exclamation-circle"></i> '}}(window.jQuery);
/*!
 * FileInput <_LANG_> Translations
 *
 * This file must be loaded after 'fileinput.js'. Patterns in braces '{}', or
 * any HTML markup tags in the messages must not be converted or translated.
 *
 * @see http://github.com/kartik-v/bootstrap-fileinput
 *
 * NOTE: this file must be saved in UTF-8 encoding.
 */
(function ($) {
    "use strict";

    $.fn.fileinputLocales['_LANG_'] = {
        fileSingle: 'file',
        filePlural: 'files',
        browseLabel: 'Browse &hellip;',
        removeLabel: 'Remove',
        removeTitle: 'Clear selected files',
        cancelLabel: 'Cancel',
        cancelTitle: 'Abort ongoing upload',
        uploadLabel: 'Upload',
        uploadTitle: 'Upload selected files',
        msgNo: 'No',
        msgNoFilesSelected: 'No files selected',
        msgCancelled: 'Cancelled',
        msgPlaceholder: 'Select {files}...',
        msgZoomModalHeading: 'Detailed Preview',
        msgFileRequired: 'You must select a file to upload.',
        msgSizeTooSmall: 'File "{name}" (<b>{size} KB</b>) is too small and must be larger than <b>{minSize} KB</b>.',
        msgSizeTooLarge: 'File "{name}" (<b>{size} KB</b>) exceeds maximum allowed upload size of <b>{maxSize} KB</b>.',
        msgFilesTooLess: 'You must select at least <b>{n}</b> {files} to upload.',
        msgFilesTooMany: 'Number of files selected for upload <b>({n})</b> exceeds maximum allowed limit of <b>{m}</b>.',
        msgFileNotFound: 'File "{name}" not found!',
        msgFileSecured: 'Security restrictions prevent reading the file "{name}".',
        msgFileNotReadable: 'File "{name}" is not readable.',
        msgFilePreviewAborted: 'File preview aborted for "{name}".',
        msgFilePreviewError: 'An error occurred while reading the file "{name}".',
        msgInvalidFileName: 'Invalid or unsupported characters in file name "{name}".',
        msgInvalidFileType: 'Invalid type for file "{name}". Only "{types}" files are supported.',
        msgInvalidFileExtension: 'Invalid extension for file "{name}". Only "{extensions}" files are supported.',
        msgFileTypes: {
            'image': 'image',
            'html': 'HTML',
            'text': 'text',
            'video': 'video',
            'audio': 'audio',
            'flash': 'flash',
            'pdf': 'PDF',
            'object': 'object'
        },
        msgUploadAborted: 'The file upload was aborted',
        msgUploadThreshold: 'Processing...',
        msgUploadBegin: 'Initializing...',
        msgUploadEnd: 'Done',
        msgUploadEmpty: 'No valid data available for upload.',
        msgUploadError: 'Error',
        msgValidationError: 'Validation Error',
        msgLoading: 'Loading file {index} of {files} &hellip;',
        msgProgress: 'Loading file {index} of {files} - {name} - {percent}% completed.',
        msgSelected: '{n} {files} selected',
        msgFoldersNotAllowed: 'Drag & drop files only! Skipped {n} dropped folder(s).',
        msgImageWidthSmall: 'Width of image file "{name}" must be at least {size} px.',
        msgImageHeightSmall: 'Height of image file "{name}" must be at least {size} px.',
        msgImageWidthLarge: 'Width of image file "{name}" cannot exceed {size} px.',
        msgImageHeightLarge: 'Height of image file "{name}" cannot exceed {size} px.',
        msgImageResizeError: 'Could not get the image dimensions to resize.',
        msgImageResizeException: 'Error while resizing the image.<pre>{errors}</pre>',
        msgAjaxError: 'Something went wrong with the {operation} operation. Please try again later!',
        msgAjaxProgressError: '{operation} failed',
        ajaxOperations: {
            deleteThumb: 'file delete',
            uploadThumb: 'file upload',
            uploadBatch: 'batch file upload',
            uploadExtra: 'form data upload'
        },
        dropZoneTitle: 'Drag & drop files here &hellip;',
        dropZoneClickTitle: '<br>(or click to select {files})',
        fileActionSettings: {
            removeTitle: 'Remove file',
            uploadTitle: 'Upload file',
            uploadRetryTitle: 'Retry upload',
            downloadTitle: 'Download file',
            zoomTitle: 'View details',
            dragTitle: 'Move / Rearrange',
            indicatorNewTitle: 'Not uploaded yet',
            indicatorSuccessTitle: 'Uploaded',
            indicatorErrorTitle: 'Upload Error',
            indicatorLoadingTitle: 'Uploading ...'
        },
        previewZoomButtonTitles: {
            prev: 'View previous file',
            next: 'View next file',
            toggleheader: 'Toggle header',
            fullscreen: 'Toggle full screen',
            borderless: 'Toggle borderless mode',
            close: 'Close detailed preview'
        }
    };
})(window.jQuery);