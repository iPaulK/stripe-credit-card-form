A simple credit card payment form for Stripe
==========================================
This a simple credit card payment form that does integrates with Stripe.js

See [Stripe.js and Elements](https://stripe.com/docs/stripe-js) for details.


## Installation

Clone repository and install dependencies
```sh
$ cd path/to/install
$ composer update
```

Create and configure local config 
```sh
$ cp config.local.php.dist config.local.php
```

Update config.local.php file and set your public and secret key
```php
define('STRIPE_PUBLIC_API_KEY', 'YOUR_STRIPE_PUBLIC_API_KEY');
define('STRIPE_SECRET_API_KEY', 'YOUR_STRIPE_SECRET_API_KEY');
```

Run Project
```sh
$ php -S localhost:8000 -t public
```

After this command you can copy this link and check on your browser:
```sh
http://localhost:8000/
```

## Preview
![](./public/img/preview.png)