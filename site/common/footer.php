      </div>
      <div class="box_footer">
         Questions/Suggestions/Feedback? Send us an <a target="_blank" href="mailto:<?= $epicastAdmin['email'] ?>?Subject=Epicast">email</a>!
      </div>
   </body>
  <script>
    /*
    Remove the service worker which was previously installed at this point.

    That particular worker cached essentially all of the site's assets, which
    was initially very helpful and significantly improved load times through
    extensive caching. However, that same caching became an impediment to
    development and led to at least one production outage. In addition, the
    worker used a number of workbox methods which have since been deprecated
    and slated for removal.

    The service worker was installed into the browser itself and will continue
    running even when the registration and the worker source code are both
    removed, which is highly problematic. Here we remove any lingering remnants
    of the service worker, if present.

    Given the persistence of the worker in the browser, the following code
    basically has to stay here forever so that any returning visitor can
    uninstall the worker, no matter how much time passes before returning to
    the site.
    */
    if(window.navigator && navigator.serviceWorker) {
      navigator.serviceWorker.getRegistrations().then(function(registrations) {
        for(let registration of registrations) {
          registration.unregister();
        }
      });
    }
  </script>
</html>
