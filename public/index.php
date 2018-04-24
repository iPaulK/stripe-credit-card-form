<?php

require_once __DIR__ . '/../vendor/autoload.php';

$success = $error = '';
if ($_POST) {
    // Set your secret key: remember to change this to your live secret key in production
    // See your keys here: https://dashboard.stripe.com/account/apikeys
    \Stripe\Stripe::setApiKey("rk_test_esg7C3WBxKyG5BjkzxSFO235");

    try {
        if (!isset($_POST['stripeToken'])) {
            throw new Exception("The Stripe Token was not generated correctly");
        }

        $phone = htmlspecialchars(strip_tags(trim($_POST['phone'])));
        $cardholderName = htmlspecialchars(strip_tags(trim($_POST['cardholder-name'])));

        $charge = \Stripe\Charge::create([
            'amount' => 2500,
            'currency' => 'usd',
            'description' => 'Example charge',
            'source' => $_POST['stripeToken'],
            'metadata' => [
                'cardholder-name' => $cardholderName,
                'phone' => $phone,
            ],
        ]);

        $chargeID = $charge['id'];
        $success = sprintf('Successfuly created charge with ID: <a target="_blank" href="https://dashboard.stripe.com/test/payments/%s">%s</a>', $chargeID, $chargeID);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="stylesheet" type="text/css" href="css/styles.css">
        <script src="https://js.stripe.com/v3/"></script>
    </head>
    <form id="payment-form" method="POST">
        <label>
            <input name="cardholder-name" class="field is-empty" placeholder="Jane Doe" />
            <span><span>Name</span></span>
        </label>
        <label>
            <input class="field is-empty" type="tel" name="phone" placeholder="(123) 456-7890" />
            <span><span>Phone number</span></span>
        </label>
        <label>
            <div id="card-element" class="field is-empty"></div>
            <span><span>Credit or debit card</span></span>
        </label>
        <button type="submit">Pay $25</button>
        <div class="outcome">
            <div class="error" role="alert"><?php echo $error; ?></div>
            <div class="success"><?php echo $success;?></div>
        </div>
    </form>

    <script type="text/javascript">
            var stripe = Stripe('pk_test_dWca0y9wafteYuXPfhApxxKb');
            var elements = stripe.elements();

            var card = elements.create('card', {
                iconStyle: 'solid',
                style: {
                    base: {
                    iconColor: '#8898AA',
                    color: 'white',
                    lineHeight: '36px',
                    fontWeight: 300,
                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                    fontSize: '19px',

                    '::placeholder': {
                        color: '#8898AA',
                    },
                    },
                    invalid: {
                        iconColor: '#e85746',
                        color: '#e85746',
                    }
                },
                classes: {
                    focus: 'is-focused',
                    empty: 'is-empty',
                },
            });
            card.mount('#card-element');

            var inputs = document.querySelectorAll('input.field');
            Array.prototype.forEach.call(inputs, function(input) {
                input.addEventListener('focus', function() {
                    input.classList.add('is-focused');
                });
                input.addEventListener('blur', function() {
                    input.classList.remove('is-focused');
                });
                input.addEventListener('keyup', function() {
                    if (input.value.length === 0) {
                        input.classList.add('is-empty');
                    } else {
                        input.classList.remove('is-empty');
                    }
                });
            });

            function setOutcome(result) {
                var form = document.querySelector('#payment-form');
                var successElement = document.querySelector('.success');
                var errorElement = document.querySelector('.error');
                successElement.classList.remove('visible');
                errorElement.classList.remove('visible');

                if (result.token) {
                    // Use the token to create a charge or a customer
                    // https://stripe.com/docs/charges
                    var input = document.createElement("input"); 
                    input.value = result.token.id;
                    input.type = "hidden";
                    input.name = "stripeToken";

                    // insert the token into the form so it gets submitted to the server
                    form.appendChild(input);
                    // and submit
                    form.submit();
                } else if (result.error) {
                    errorElement.textContent = result.error.message;
                    errorElement.classList.add('visible');

                    form.querySelector('button[type="submit"]').disabled = false;
                }
            }

            card.on('change', function(event) {
                setOutcome(event);
            });

            document.querySelector('#payment-form').addEventListener('submit', function(e) {
                e.preventDefault();
                var chargeAmount = 2500; //amount you want to charge, in cents. 1000 = $10.00, 2000 = $20.00 ...
                var form = document.querySelector('#payment-form');
                form.querySelector('button[type="submit"]').disabled = true;
                var extraDetails = {
                    name: form.querySelector('input[name=cardholder-name]').value,
                };
                stripe.createToken(card, chargeAmount, extraDetails).then(setOutcome);
            });
        </script>
    </body>
</html>
