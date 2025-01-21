# Amazon Incentives - Gift Codes on Demand stand alone class

This is a abastract from [https://github.com/kamerk22/AmazonGiftCod](https://github.com/kamerk22/AmazonGiftCode) to be not dependend on Laravel base code.

Amazon Gift Codes On Demand (AGCOD). Integration for Amazon Incentive API.

[General Amazon Incentives Documentation](https://developer.amazon.com/docs/incentives-api/digital-gift-cards.html)

## How to install

`composer require gullevek/amazon-incentives`

## _ENV variables needed

Uses .env file to load configuration data

The below keys are searched in the _ENV file for loading

* AWS_GIFT_CARD_KEY
* AWS_GIFT_CARD_SECRET
* AWS_GIFT_CARD_PARTNER_ID
* AWS_GIFT_CARD_ENDPOINT
* AWS_GIFT_CARD_CURRENCY
* AWS_DEBUG (1/0)

## How to use

The above _ENV variables must be set (Except AWS_DEBUG, defaults to off).

### create gift card

```php
use gullevek\AmazonIncentives\AmazonIncentives;
// buy a gift card with a value
$value = 500;
$aws_gc = AmazonIncentives::make()->buyGiftCard((float)$value);
// the two below are need if we want to cancel the card
// get gift card id (gcID)
$aws_gc->getId();
// get creation request id (creationRequestId)
$aws_gc->getCreationRequestId();
// the one below must be printed to the user
$aws_gc->getClaimCode();
// check status (SUCCESS/RESEND/FAILURE)
$aws_gc->getStatus();
// others:
// getAmount, getCurrency
```

#### Throttle Rates

Note that you can only send 10 requests per second. On a Throttle Excepion you need to wait about 10s to create another request.

Recommended to pool requests. Or check when last requests where sent and then process them.

#### On F400 errors

1) try again
2) if failed run cancel gift card
3) if cance ok try create again with different request id
4) if 2) failed, wait a view seconds and try again
5) if 10s elapse, we need to wait a full day
6) if >24h call Amazon

### cancel gift card

```php
// use getCreationRequestId() and getId() from request
$aws_gc = gullevek\AmazonIncentives\AmazonIncentives::make()->cancelGiftCard($creation_request_id, $gift_card_id);
// return is as above
```

### check balance

```php
$aws_gc = gullevek\AmazonIncentives\AmazonIncentives::make()->getAvailableFunds();
```

## Exceptions

If the HTTPS request does not return 220 OK it will throw an exception.

The error code is the curl handler error code.
The error message is json encoded array with the layout

Use

```php
$exception_array = gullevek\AmazonIncentives\AmazonIncentives::decodeExceptionMessage($exception_message);
```

to extract the below array from the thrown exception

```php
[
    'status' => 'AWS Status FAILURE or RESEND',
    'code' => 'AWS Error Code Fnnn',
    'type' => 'AWS Error info',
    'message' => 'AWS long error message',
    'log_id' => 'If logging is on the current log id',
    'log' => 'The complete log collected over all calls',
]
```

`status`, `code` and `type` must be checked on a failure.

## Other Errors from exceptions

### T001

if code is T001 then this is a request flood error:
In this case the request has to be resend after a certain waiting period.

### E999

if code is E999 some other critical error has happened

### E001

if code is E001 if the return create/cancel/check calls is not an array

### C001

fif code is C001 curl failed to init

### C002

if code is C002 a curl error has happened

### J-number

if a JSON error was encountered during some encoding this error will be found.
The number is the json error code.

### empty error code

any other NON amazon error will have only 'message' set if run through decode

## Debugging

If AWS_DEBUG is set to 1 and internal array will be written with debug info.

The gulleek\AmazonIncentives\Debug\AmazonDebug class handles all this.

In the gulleek\AmazonIncentives\AmazonIncentives main class the debugger gets set

* setDebug that turns debugger on/off and if on sets unique id (getId to check)

New entries can be written with

`AmazonDebug::writeLog(['foo' => 'bar']);`

On sucessful run the log data is accessable with `$aws->getLog()`
On exception the log data is in the error message json (see exceptions)
