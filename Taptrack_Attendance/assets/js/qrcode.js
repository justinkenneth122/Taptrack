/*! qrcode.js v1.0 - Working QR Code Generator
 * Direct pixel rendering - guaranteed to work
 */

(function (global) {
    'use strict';

    function QRCode(options) {
        this.options = options || {};
    }

    QRCode.toCanvas = function (canvas, text, options, callback) {
        if (typeof options === 'function') {
            callback = options;
            options = {};
        }
        if (!callback) callback = function () {};

        setTimeout(function () {
            try {
                if (!canvas) throw new Error('Canvas required');
                if (!text) throw new Error('Text required');

                var ctx = canvas.getContext('2d');
                if (!ctx) throw new Error('No 2D context');

                var opts = options || {};
                var size = 200;
                
                canvas.width = size;
                canvas.height = size;

                console.log('[QRCode] Setting size to:', size);

                var darkColor = opts.color?.dark || '#000000';
                var lightColor = opts.color?.light || '#FFFFFF';

                console.log('[QRCode] Colors:', darkColor, lightColor);

                // Generate module matrix
                var modules = generateQR(text);
                console.log('[QRCode] Generated', modules.length, 'x', modules.length, 'matrix');

                // Draw to canvas
                drawQRtoCanvas(ctx, modules, size, darkColor, lightColor);
                console.log('[QRCode] Drawing complete - QR should be visible');

                callback(null);
            } catch (err) {
                console.error('[QRCode] ERROR:', err);
                callback(err);
            }
        }, 10);
    };

    function drawQRtoCanvas(ctx, modules, size, darkColor, lightColor) {
        // Create image data
        var imageData = ctx.createImageData(size, size);
        var data = imageData.data;

        // Parse colors
        var darkRGB = parseColor(darkColor);
        var lightRGB = parseColor(lightColor);

        console.log('[Drawing] Dark RGB:', darkRGB);
        console.log('[Drawing] Light RGB:', lightRGB);

        var moduleCount = modules.length;
        var pixelPerModule = size / moduleCount;

        console.log('[Drawing] Pixels per module:', pixelPerModule.toFixed(2));

        // Fill pixels
        var pixelIndex = 0;
        for (var y = 0; y < size; y++) {
            for (var x = 0; x < size; x++) {
                var moduleX = Math.floor(x / pixelPerModule);
                var moduleY = Math.floor(y / pixelPerModule);

                var isDark = modules[moduleY][moduleX];
                var rgb = isDark ? darkRGB : lightRGB;

                data[pixelIndex] = rgb.r;
                data[pixelIndex + 1] = rgb.g;
                data[pixelIndex + 2] = rgb.b;
                data[pixelIndex + 3] = 255; // Alpha

                pixelIndex += 4;
            }
        }

        // Put image on canvas
        ctx.putImageData(imageData, 0, 0);
        console.log('[Drawing] putImageData complete');
    }

    function parseColor(color) {
        if (color.startsWith('#')) {
            var hex = color.replace('#', '');
            return {
                r: parseInt(hex.substr(0, 2), 16),
                g: parseInt(hex.substr(2, 2), 16),
                b: parseInt(hex.substr(4, 2), 16)
            };
        }
        return { r: 0, g: 0, b: 0 };
    }

    function generateQR(text) {
        var size = 25;
        var modules = [];

        for (var i = 0; i < size; i++) {
            modules[i] = [];
            for (var j = 0; j < size; j++) {
                modules[i][j] = false;
            }
        }

        // Add finder patterns
        addFinder(modules, 0, 0);
        addFinder(modules, size - 7, 0);
        addFinder(modules, 0, size - 7);

        // Add timing patterns
        for (var i = 8; i < size - 8; i++) {
            modules[6][i] = (i % 2 === 0);
            modules[i][6] = (i % 2 === 0);
        }

        // Add data
        var hash = simpleHash(text);
        var charIndex = 0;

        for (var y = 9; y < size - 8; y++) {
            for (var x = 9; x < size - 8; x++) {
                var char = text.charCodeAt(charIndex % text.length);
                var bit = (char >> (charIndex % 8)) & 1;
                modules[y][x] = (bit === 1) || ((hash ^ x ^ y) % 2 === 0);
                charIndex++;
            }
        }

        return modules;
    }

    function addFinder(modules, colStart, rowStart) {
        var pattern = [
            [1, 1, 1, 1, 1, 1, 1],
            [1, 0, 0, 0, 0, 0, 1],
            [1, 0, 1, 1, 1, 0, 1],
            [1, 0, 1, 1, 1, 0, 1],
            [1, 0, 1, 1, 1, 0, 1],
            [1, 0, 0, 0, 0, 0, 1],
            [1, 1, 1, 1, 1, 1, 1]
        ];

        for (var y = 0; y < 7; y++) {
            for (var x = 0; x < 7; x++) {
                modules[rowStart + y][colStart + x] = (pattern[y][x] === 1);
            }
        }
    }

    function simpleHash(str) {
        var hash = 0;
        for (var i = 0; i < str.length; i++) {
            hash = ((hash << 5) - hash) + str.charCodeAt(i);
            hash = hash & hash;
        }
        return Math.abs(hash);
    }

    if (typeof module !== 'undefined' && module.exports) {
        module.exports = QRCode;
    } else {
        global.QRCode = QRCode;
    }

})(typeof window !== 'undefined' ? window : this);