define([], function () {
    'use strict';

    return function (config, element) {
        var now = new Date();

        element.textContent = new Intl.DateTimeFormat(config.locale, {
            dateStyle: 'medium',
            timeStyle: 'short',
            timeZone: config.timezone
        }).format(now);
        element.setAttribute('datetime', now.toISOString());
    };
});
