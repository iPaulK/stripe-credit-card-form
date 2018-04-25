<?php

require_once __DIR__ . '/../vendor/autoload.php';

$success = $error = '';

$phone = '';
$cardholderName = '';
$amount = 0;
$currency = 'USD';
$allowedCurrencies = array(
    // 'AUD',
    // 'BRL',
    // 'CAD',
    // 'CHF',
    // 'DKK',
    'EUR',
    'GBP',
    // 'HKD',
    // 'JPY',
    // 'MXN',
    // 'NOK',
    // 'NZD',
    // 'SEK',
    // 'SGD',
    'USD'
);

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
        $currency = $_POST['currency'];
        
        $amount = floatval($_POST['amount']);
        $chargeAmount = $amount * 100; //amount you want to charge, in cents. 1000 = $10.00, 2000 = $20.00 ...
        $chargeAmount = intval($chargeAmount);

        $charge = \Stripe\Charge::create([
            'amount' => $chargeAmount,
            'currency' => $currency,
            'description' => 'Example charge',
            'source' => $_POST['stripeToken'],
            'metadata' => [
                'cardholder-name' => $cardholderName,
                'phone' => $phone,
            ],
        ]);

        $chargeID = $charge['id'];
        //$success = sprintf('Successfuly created charge with ID: <a target="_blank" href="https://dashboard.stripe.com/test/payments/%s">%s</a>', $chargeID, $chargeID);
        $success = 'Successfuly created charge';
        $phone = $cardholderName = '';
        $amount = 0;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="stylesheet" type="text/css" href="css/styles.css">
        <link rel="stylesheet" type="text/css" href="chosen/chosen.css">
        <script src="https://js.stripe.com/v3/"></script>
    </head>
    <form id="payment-form" method="POST"> 
        <label>
            <input name="cardholder-name" class="field<?php echo (empty($cardholderName)) ? ' is-empty': ''; ?>" placeholder="Jane Doe" value="<?php echo $cardholderName; ?>"/>
            <span><span>Name</span></span>
        </label>
        <label>
            <input class="field<?php echo (empty($phone)) ? ' is-empty': '';?>" type="tel" name="phone" placeholder="(123) 456-7890" value="<?php echo $phone; ?>"/>
            <span><span>Phone number</span></span>
        </label>
        <label class="display-inline">
            <input name="amount" class="field<?php echo ($amount == 0) ? ' is-empty': '';?>" value="<?php echo $amount; ?>" required/>
            <span><span>Amount</span></span>
        </label>
        <label class="display-inline">
            <select name="currency" class="chosen-select" tabindex="2">
                <?php foreach ($allowedCurrencies as $item) { ?>
                    <option value="<?php echo $item; ?>"<?php echo ($item == $currency) ? ' selected' : '';?>>
                        <?php echo $item; ?>
                    </option>
                <?php } ?>
            </select>
        </label>
        <label>
            <div id="card-element" class="field is-empty"></div>
            <span><span>Credit or debit card</span></span>
        </label>
        <button type="submit">Pay</button>
        <div class="outcome">
            <div class="error" role="alert"><?php echo $error; ?></div>
            <div class="success"><?php echo $success;?></div>
        </div>
    </form>
    <script type="text/javascript" src="chosen/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="chosen/chosen.jquery.min.js"></script>
    <script type="text/javascript">
        $(".chosen-select").chosen({disable_search_threshold: 10});
    </script>
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

                var form = document.querySelector('#payment-form');
                var chargeAmount = form.querySelector('input[name="amount"]');
                
                if (chargeAmount.value > 0) {
                    chargeAmount = chargeAmount * 100; //amount you want to charge, in cents. 1000 = $10.00, 2000 = $20.00 ...
                    form.querySelector('button[type="submit"]').disabled = true;
                    var extraDetails = {
                        name: form.querySelector('input[name=cardholder-name]').value,
                    };
                    stripe.createToken(card, chargeAmount, extraDetails).then(setOutcome);
                } else {
                    chargeAmount.classList.add('is-error');
                }
            });
        </script>
    </body>
</html>
