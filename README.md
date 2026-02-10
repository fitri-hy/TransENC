# TransENC

**TransENC** is a secure encrypted transport library for Laravel that implements hybrid encryption (AES + RSA), payload signing, and anti-replay attack protection (nonce) for secure API communication between client and server.

## Features

* AES-256-GCM for payload encryption
* RSA-2048 / RSA-4096 for key exchange
* Payload signing for integrity & authentication
* Replay attack protection using nonce
* Artisan commands for key lifecycle management
* Fully integrated with Laravel Service Container

<img src="./ss.png" alt="flow">

## Requirements

* PHP >= 8.1
* Laravel >= 10
* OpenSSL extension enabled

## Installation

### Install via Packagist (Recommended)

```bash
composer require fhylabs/transenc
```

### Install via Local Path

Add a path repository to `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "TransENC",
        "options": {
            "symlink": true
        }
    }
]
```

Require the package:

```bash
composer require fhylabs/transenc:@dev
```

## Publish Config

```bash
php artisan vendor:publish --provider="TransENC\Providers\EncryptedTransportServiceProvider" --tag=config
```

The config file will be available at:

```bash
config/transenc.php
```

## Key Management

### Generate keys for a client

```bash
php artisan transenc:generate-key testclient
```

Generates:

* RSA private key (server)
* RSA public key (client)
* Client-isolated key storage

### Rotate keys

```bash
php artisan transenc:rotate-keys
```

> Old keys remain valid during the grace period (configurable).

### Encrypt Payload

```bash
php artisan transenc:encrypt-payload testclient '{"foo":"bar"}'
```

## API Reference

### Encryption Service

| Method    | Parameters                                       | Return   | Description                                                                                |
| --------- | ------------------------------------------------ | -------- | ------------------------------------------------------------------------------------------ |
| `encrypt` | `string $payload`<br>`string $clientId`          | `string` | Encrypts JSON payload using **AES** and secures the key with the client **RSA public key** |
| `decrypt` | `string $encryptedPayload`<br>`string $clientId` | `string` | Decrypts the payload using the server **RSA private key**, then decrypts the AES payload   |

### Payload Signer

| Method   | Parameters                                                     | Return   | Description                                                              |
| -------- | -------------------------------------------------------------- | -------- | ------------------------------------------------------------------------ |
| `sign`   | `string $payload`<br>`string $clientId`                        | `string` | Creates a signature to ensure **payload integrity**                      |
| `verify` | `string $payload`<br>`string $signature`<br>`string $clientId` | `bool`   | Verifies that the payload was not modified and comes from a valid client |

### Nonce Manager

| Method   | Parameters      | Return | Description                                                     |
| -------- | --------------- | ------ | --------------------------------------------------------------- |
| `verify` | `string $nonce` | `bool` | Ensures a nonce is used **only once** to prevent replay attacks |

### Artisan Commands

| Command                    | Arguments           | Description                        |
| -------------------------- | ------------------- | ---------------------------------- |
| `transenc:generate-key`    | `{clientId}`        | Generate RSA key pair for a client |
| `transenc:rotate-keys`     | –                   | Securely rotate encryption keys    |
| `transenc:encrypt-payload` | `{clientId} {json}` | Test payload encryption via CLI    |

## Example of Use

#### Server Side

```
use TransENC\Services\EncryptionService;
use TransENC\Services\PayloadSigner;
use TransENC\Services\NonceManager;

$clientId  = 'testclient';
$encryptor = app(EncryptionService::class);
$signer    = app(PayloadSigner::class);

/*
|--------------------------------------------------------------------------
| 1. Decrypt Incoming Payload
|--------------------------------------------------------------------------
*/
$payload = json_decode(
    $encryptor->decrypt($encryptedPayload, $clientId),
    true
);

/*
|--------------------------------------------------------------------------
| 2. Verify Signature
|--------------------------------------------------------------------------
*/
if (!$signer->verify(json_encode($payload), $signature, $clientId)) {
    abort(401, 'Invalid signature');
}

/*
|--------------------------------------------------------------------------
| 3. Verify Nonce (Anti Replay)
|--------------------------------------------------------------------------
*/
if (!NonceManager::verify($payload['nonce'])) {
    abort(409, 'Replay attack detected');
}

/*
|--------------------------------------------------------------------------
| 4. Prepare Response
|--------------------------------------------------------------------------
*/
$response = json_encode([
    'status' => 'success',
    'data'   => $payload,
    'nonce'  => bin2hex(random_bytes(16)),
]);

/*
|--------------------------------------------------------------------------
| 5. Encrypt Response
|--------------------------------------------------------------------------
*/
return $encryptor->encrypt($response, $clientId);
```

#### Client Side

```
use TransENC\Services\EncryptionService;
use TransENC\Services\PayloadSigner;

$clientId  = 'testclient';
$encryptor = app(EncryptionService::class);
$signer    = app(PayloadSigner::class);

/*
|--------------------------------------------------------------------------
| 1. Create Payload + Nonce
|--------------------------------------------------------------------------
*/
$payload = [
    'name'  => 'FHY',
    'role'  => 'admin',
    'time'  => time(),
    'nonce' => bin2hex(random_bytes(16)),
];

$jsonPayload = json_encode($payload);

/*
|--------------------------------------------------------------------------
| 2. Sign Payload
|--------------------------------------------------------------------------
*/
$signature = $signer->sign($jsonPayload, $clientId);

/*
|--------------------------------------------------------------------------
| 3. Encrypt Payload
|--------------------------------------------------------------------------
*/
$encryptedPayload = $encryptor->encrypt($jsonPayload, $clientId);

/*
|--------------------------------------------------------------------------
| 4. Send Request
|--------------------------------------------------------------------------
*/
$request = [
    'client_id' => $clientId,
    'payload'   => $encryptedPayload,
    'signature' => $signature,
];

/*
|--------------------------------------------------------------------------
| 5. Decrypt Response
|--------------------------------------------------------------------------
*/
$response = json_decode(
    $encryptor->decrypt($encryptedResponse, $clientId),
    true
);
```

> See complete usage examples: [tests/examples.php](./tests/examples.php)

## Folder Structure

```
TransENC/
│
├── src/
│   ├── Console/
│   │   ├── GenerateClientKey.php
│   │   ├── RotateKeys.php
│   │   └── EncryptPayload.php
│   │
│   ├── Config/
│   │   └── encrypted_transport.php
│   │
│   ├── Exceptions/
│   │   ├── DecryptionException.php
│   │   └── EncryptionException.php
│   │
│   ├── Http/
│   │   └── Middleware/
│   │       ├── DecryptRequest.php
│   │       └── EncryptResponse.php
│   │
│   ├── Services/
│   │   ├── EncryptionService.php
│   │   ├── KeyManager.php
│   │   ├── PayloadSigner.php
│   │   └── NonceManager.php
│   │
│   ├── Support/
│   │   └── PayloadHelper.php
│   │
│   ├── Providers/
│   │   └── EncryptedTransportServiceProvider.php
│   │
│   └── Traits/
│       └── Encryptable.php
│
└── composer.json
```