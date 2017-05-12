# Controller basics

A controllers will allow you to respond to requested URIs with different responses. They are all suffixed with `.controller.php` and are stored in your app's `Controllers` directory. The name of the class in the controller must be the same as the start of the filename.

## Create a controller

Create a new file called `Basics.controller.php` in your `/app/Controllers` directory:

```php
<?php

    /*
     * ================================
     * The PSR namespace for your app's
     * controllers
     * ================================
     */
    namespace App\Controllers;
    
    /*
     * ================================
     * The TwistPHP base controller
     * ================================
     */
    use Twist\Core\Controllers\Base;
    
    /*
     * ================================
     * This new controller class should
     * be named exactly the same as the
     * filename and extend the TwistPHP
     * base controller
     * ================================
     */
    class Basics extends Base {
    
        /*
         * ================================
         * If your controller needs to have
         * anything initialised this can be
         * done here
         * ================================
         */
        public function _baseCalls() {}
    
        /*
         * ================================
         * Add any other methods in here to
         * return data for that URI
         * ================================
         */
        
    }
```

Register your controller by adding the following lines to your main `index.php` file in your site root:

```php
<?php

    /*
     * ================================
     * Require the TwistPHP framework
     * ================================
     */
    require_once( 'twist/framework.php' );
    
    /*
     * ================================
     * Register the 'Basics' controller
     * for all requests that start with
     * the URI '/' (which should be the
     * base for the site)
     * ================================
     */
    Twist::Route() -> controller( '/%', 'Basics' );
    
    /*
     * ================================
     * Respond to all requests with the
     * relevant registered routes
     * ================================
     */
	Twist::Route() -> serve();
```

Visiting the site in the browser should now give a `404` response as we havven't yet defined any responses.

## Common methods

Several default methods are inherited from the TwistPHP base controller to make development easier.

### The default response (_index)

The index method is used when the root URI of the controller is requested.

Add the following `_index()` method function into your controller:

```php
<?php

    public function _index() {
        return 'Hello world!';
    }
```

When visiting your site in the browser, you should now see the welcome of "Hello world!".

### Fallback method

The fallback method is used whenever a controller method is not found. By default, the `_fallback()` method returns a `404` response.

You can overwrite the method and provide whatever response you need. To return another response, you can use the following code:

```php
<?php

    public function _fallback() {
        /*
         * ================================
         * A standard response to a missing
         * request is a 404 page
         * ================================
         */
        //return $this -> _404();
        
        /*
         * ================================
         * You can respond with any RFC7231
         * code, maybe even one that claims
         * you are a teapot
         * ================================
         */
        return $this -> _response( 418 );
    }
```

## Custom methods

To add custom responses to your controller, simply add a method with the name or the URI you want to capture into your controller:

```php
<?php

    /*
     * ================================
     * Remember that controller methods
     * are case sensitive
     * ================================
     */
    public function whendoesthenarwhalbacon() {
        return 'Midnight';
    }
```

Now when visiting your site, go to the URI `/whendoesthenarwhalbacon` to see the response 'Midnight'.