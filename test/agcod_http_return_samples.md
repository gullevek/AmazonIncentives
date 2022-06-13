# Return data from AWS as json string

## Mock tests self

Tests run in local mock tests without connection to AWS

### funds

```json
{"availableFunds":{"amount":0.0,"currencyCode":"JPY"},"status":"SUCCESS","timestamp":"20220610T085450Z"}
```

### buy

```json
{"cardInfo":{"cardNumber":null,"cardStatus":"Fulfilled","expirationDate":null,"value":{"amount":1000.0,"currencyCode":"JPY"}},"creationRequestId":"PartnerId_62a309167e7a4","gcClaimCode":"LJ49-AKDUV6-UYCP","gcExpirationDate":"Thu Jun 10 14:59:59 UTC 2032","gcId":"5535125272070255","status":"SUCCESS"}
```

### cancel

```json
{"creationRequestId":"EG3bd_62a309167e7a4","gcId":"5535125272070255","status":"SUCCESS"}
```

### buy other 1

```json
{"cardInfo":{"cardNumber":null,"cardStatus":"RefundedToPurchaser","expirationDate":null,"value":{"amount":1000.0,"currencyCode":"JPY"}},"creationRequestId":"PartnerId_62a309167e7a4","gcClaimCode":"LJ49-AKDUV6-UYCP","gcExpirationDate":"Thu Jun 10 14:59:59 UTC 2032","gcId":"5535125272070255","status":"SUCCESS"}
```

### buy aother 2

```json
{"cardInfo":{"cardNumber":null,"cardStatus":"Fulfilled","expirationDate":null,"value":{"amount":1000.0,"currencyCode":"JPY"}},"creationRequestId":"PartnerId_62a30923e9705","gcClaimCode":"UM97-FD5QKK-WKCT","gcExpirationDate":"Thu Jun 10 14:59:59 UTC 2032","gcId":"5540334324324221","status":"SUCCESS"}
```

### buy other 2 (same create request id)

```json
{"cardInfo":{"cardNumber":null,"cardStatus":"Fulfilled","expirationDate":null,"value":{"amount":1000.0,"currencyCode":"JPY"}},"creationRequestId":"PartnerId_62a30923e9705","gcClaimCode":"UM97-FD5QKK-WKCT","gcExpirationDate":"Thu Jun 10 14:59:59 UTC 2032","gcId":"5540334324324221","status":"SUCCESS"}
```

## AWS GCOD Mocks

Mocking on AWS side, these will be returned if given request ID codes are sent.
A working test account must exist for this

## success

### buy gift card F0000

```json
{"cardInfo":{"cardNumber":null,"cardStatus":"Fulfilled","expirationDate":null,"value":{"amount":500.0,"currencyCode":"JPY"}},"creationRequestId":"F0000","gcClaimCode":"ZYXW-VUTS-RQPO","gcExpirationDate":null,"gcId":"ABC123ZYX987","status":"SUCCESS"}
```

## errors

### F1000 -> F100

```json
{"agcodResponse":{"__type":"CreateGiftCardResponse:http://internal.amazon.com/coral/com.amazonaws.agcod/","cardInfo":null,"creationRequestId":null,"gcClaimCode":null,"gcExpirationDate":null,"gcId":null,"status":"FAILURE"},"errorCode":"F100","errorType":"GeneralError","message":"General Error"}
```

### F2003 -> F200

```json
{"agcodResponse":{"__type":"CreateGiftCardResponse:http://internal.amazon.com/coral/com.amazonaws.agcod/","cardInfo":null,"creationRequestId":null,"gcClaimCode":null,"gcExpirationDate":null,"gcId":null,"status":"FAILURE"},"errorCode":"F200","errorType":"InvalidAmountInput","message":"Amount can't be null"}
```

### F2004 -> F200

```json
{"agcodResponse":{"__type":"CreateGiftCardResponse:http://internal.amazon.com/coral/com.amazonaws.agcod/","cardInfo":null,"creationRequestId":null,"gcClaimCode":null,"gcExpirationDate":null,"gcId":null,"status":"FAILURE"},"errorCode":"F200","errorType":"InvalidAmountValue","message":"Amount must be larger than 0"}
```

### F2005 -> F200

```json
{"agcodResponse":{"__type":"CreateGiftCardResponse:http://internal.amazon.com/coral/com.amazonaws.agcod/","cardInfo":null,"creationRequestId":null,"gcClaimCode":null,"gcExpirationDate":null,"gcId":null,"status":"FAILURE"},"errorCode":"F200","errorType":"InvalidCurrencyCodeInput","message":"Currency Code can't be null or empty"}
```

### F2010 -> F200

```json
{"agcodResponse":{"__type":"CreateGiftCardResponse:http://internal.amazon.com/coral/com.amazonaws.agcod/","cardInfo":null,"creationRequestId":null,"gcClaimCode":null,"gcExpirationDate":null,"gcId":null,"status":"FAILURE"},"errorCode":"F200","errorType":"CardAlreadyActivatedWithDifferentRequestId","message":"The card was already activated with a different request id"}
```

### F2015 -> F200

```json
{"agcodResponse":{"__type":"CreateGiftCardResponse:http://internal.amazon.com/coral/com.amazonaws.agcod/","cardInfo":null,"creationRequestId":null,"gcClaimCode":null,"gcExpirationDate":null,"gcId":null,"status":"FAILURE"},"errorCode":"F200","errorType":"MaxAmountExceeded","message":"Max Amount Exceeded"}
```

### F2016 -> F200

```json
{"agcodResponse":{"__type":"CreateGiftCardResponse:http://internal.amazon.com/coral/com.amazonaws.agcod/","cardInfo":null,"creationRequestId":null,"gcClaimCode":null,"gcExpirationDate":null,"gcId":null,"status":"FAILURE"},"errorCode":"F200","errorType":"CurrencyCodeMismatch","message":"Currency Code Mismatch"}
```

### F2017 -> F200

```json
{"agcodResponse":{"__type":"CreateGiftCardResponse:http://internal.amazon.com/coral/com.amazonaws.agcod/","cardInfo":null,"creationRequestId":null,"gcClaimCode":null,"gcExpirationDate":null,"gcId":null,"status":"FAILURE"},"errorCode":"F200","errorType":"FractionalAmountNotAllowed","message":"Fractional Amount Not Allowed"}
```

### F2047 -> F200

```json
{"agcodResponse":{"__type":"CreateGiftCardResponse:http://internal.amazon.com/coral/com.amazonaws.agcod/","cardInfo":null,"creationRequestId":null,"gcClaimCode":null,"gcExpirationDate":null,"gcId":null,"status":"FAILURE"},"errorCode":"F200","errorType":"CancelRequestArrivedAfterTimeLimit","message":"Cancellation cannot be processed as too much time has elapsed since creation"}
```

### F3003 -> F300

```json
{"agcodResponse":{"__type":"CreateGiftCardResponse:http://internal.amazon.com/coral/com.amazonaws.agcod/","cardInfo":null,"creationRequestId":null,"gcClaimCode":null,"gcExpirationDate":null,"gcId":null,"status":"FAILURE"},"errorCode":"F300","errorType":"InsufficientFunds","message":"Insufficient Funds"}
```

### F3005 -> F300

```json
{"agcodResponse":{"__type":"CreateGiftCardResponse:http://internal.amazon.com/coral/com.amazonaws.agcod/","cardInfo":null,"creationRequestId":null,"gcClaimCode":null,"gcExpirationDate":null,"gcId":null,"status":"FAILURE"},"errorCode":"F300","errorType":"GeneralError","message":"General Error"}
```

### F3010 -> F300

```json
{"agcodResponse":{"__type":"CreateGiftCardResponse:http://internal.amazon.com/coral/com.amazonaws.agcod/","cardInfo":null,"creationRequestId":null,"gcClaimCode":null,"gcExpirationDate":null,"gcId":null,"status":"FAILURE"},"errorCode":"F300","errorType":"CustomerSurpassedDailyVelocityLimit","message":"Customer has exceeded daily velocity limit"}
```

### F4000 -> F400

```json
{"agcodResponse":{"__type":"CreateGiftCardResponse:http://internal.amazon.com/coral/com.amazonaws.agcod/","cardInfo":null,"creationRequestId":null,"gcClaimCode":null,"gcExpirationDate":null,"gcId":null,"status":"RESEND"},"errorCode":"F400","errorType":"SystemTemporarilyUnavailable","message":"System Temporarily Unavailable"}
```

### F5000 -> F500

```json
{"agcodResponse":{"__type":"CreateGiftCardResponse:http://internal.amazon.com/coral/com.amazonaws.agcod/","cardInfo":null,"creationRequestId":null,"gcClaimCode":null,"gcExpirationDate":null,"gcId":null,"status":"FAILURE"},"errorCode":"F500","errorType":"GeneralError","message":"General Error"}
```
