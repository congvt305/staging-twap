define(["jquery"], function ($) {
    var mod = {};
    mod.init = function (opts) {
        if (!opts || !opts.pluginUrl) {
            return;
        }

        window.atomePaymentPluginPriceDividerOptions = opts;
        $(document).ready(function () {
            var pluginUrl = opts.pluginUrl;
            pluginUrl += pluginUrl.indexOf('?') >= 0 ? '&' : '?';
            pluginUrl += 'host=' + window.location.host;

            var head = document.head || document.getElementsByTagName('head')[0];
            var el = document.createElement('script');
            el.type = 'text/javascript';
            el.src = pluginUrl;
            head.appendChild(el);
        });
    }
    return mod;
});
