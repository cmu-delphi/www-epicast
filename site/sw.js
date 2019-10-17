//Initialize Service Worker Properly
"use strict";
// var workbox = 0;

"function" == typeof importScripts &&
  importScripts (
    "https://storage.googleapis.com/workbox-cdn/releases/4.3.1/workbox-sw.js"
  );

console.log('Was Workbox able to load?');
  if (workbox) {
    console.log('Workbox loaded correctly.');
  } else {
    console.log('Workbox did not load correctly, please check your service worker for errors.');
}

workbox.precaching.precacheAndRoute([
    { url: 'index.php', revision: 'g9k4ra3' },
    { url: 'home.php', revision: 'd3rf3r4' },
    // If forecast.php gets cached it will show inaccurate data when accessing different regions
    // { url: 'forecast.php', revision: 'fc386e2' },
]);

//workbox.googleAnalytics.initialize();

workbox.routing.registerRoute(
  // Cache PHP files
  /.*\.php/,
  // Use cache but update in the background ASAP
  workbox.strategies.staleWhileRevalidate({
    cacheName: 'php-cache',
  })
);

workbox.routing.registerRoute(
  // Cache CSS files
  /.*\.css/,
  // Use cache but update in the background ASAP
  workbox.strategies.staleWhileRevalidate({
    cacheName: 'css-cache',
  })
);

workbox.routing.registerRoute(
  // Cache JS files
  /.*\.js/,
  // Use cache but update in the background ASAP
  workbox.strategies.staleWhileRevalidate({
    cacheName: 'js-cache',
  })
);

workbox.routing.registerRoute(
  // Cache Image files
  /.*\.(?:png|jpg|jpeg|svg|gif)/,
  // Use the cache if it's available
  workbox.strategies.cacheFirst({
    cacheName: 'image-cache',
    plugins: [
      new workbox.expiration.Plugin({
        // Cache up to 50 images
        maxEntries: 25,
        // Cache for a maximum of 28 days
        maxAgeSeconds: 28 * 24 * 60 * 60,
        // Automatically cleanup if quota is exceeded.
        purgeOnQuotaError: true,
      })
    ],
  })
);

workbox.routing.registerRoute(
  /.*(?:epicast)\.org.*$/,
  workbox.strategies.staleWhileRevalidate({
  cacheName: 'internal-cache',
  })
);

workbox.routing.registerRoute(
  /.*(?:facebook|googleapis|ajax.googleapis|fonts.googleapis|fonts.gstatic|maxcdn.bootstrapcdn)\.com.*$/,
  workbox.strategies.staleWhileRevalidate({
  cacheName: 'external-cache',
  })
);

workbox.routing.registerRoute(
  /.*(?:delphi.midas.cs.cmu)\.edu.*$/,
  workbox.strategies.staleWhileRevalidate({
  cacheName: 'API-cache',
  })
);
