# Phactor

## Description

Phactor is a high-performance PHP implementation of the elliptic curve math functions required to generate private/public EC keypairs and ECDSA signatures based on the secp256k1 curve parameters. Also includes a class to generate Service Identification Numbers (SINs) based on the published Identity Protocol v1 spec. These PHP classes are designed to be used in conjunction with software used for Bitcoin-related cryptographic operations right now but the ultimate, long-range goal is to become a more general-purpose mathematics library that can also be used for scientific computing and other non-ecc cryptography projects - basically anywhere you need a convenient interface to arbitrary precision math functions implemented in PHP.

**Note:** These classes require either the BC or GMP math PHP extension (GMP is preferred but will use BC as the fallback).  You can read more about the GMP extention here: http://www.php.net/manual/en/book.gmp.php

## Installation

Installation of this project is very easy using composer:

```php
php composer.phar require ionux/phactor:1.0.0
```

Or you can install manually by downloading the zip file and extracting the contents into your project's source directory by hand.


## Usage

Integrating these classes with your project is very simple.  For example, to just generate keypairs:

```php
$key = new \Phactor\Key;

$info = $key->GenerateKeypair();
```

An associative array will be returned upon success:

```sh
  Array
(
    [private_key_hex] => 7a4fbece43963538cb8f9149b094906168d71be36cfb405e6930fddb42da2c7d
    [private_key_dec] => 55323065337948610870652254548527896513063178460294714145329611159009536650365
    [public_key] => 043fbbf44c3da3fec12bf7bac254fd176adc3eaed79470932b574d8d60728eb206fb7ac7ac6959f75a6859a1a8d745db7e825a3c5c826e5b2e4950892b35772313
    [public_key_compressed] => 033fbbf44c3da3fec12bf7bac254fd176adc3eaed79470932b574d8d60728eb206
    [public_key_x] => 3fbbf44c3da3fec12bf7bac254fd176adc3eaed79470932b574d8d60728eb206
    [public_key_y] => fb7ac7ac6959f75a6859a1a8d745db7e825a3c5c826e5b2e4950892b35772313
)

```

Depending on the speed of your hardware, keys can be generated in approximately 10ms or less.

And to generate/validate ECDSA signatures:

```php
  $sig = new \Phactor\Signature;

  $signature = $sig->generate('my message to sign...', $info['private_key_hex']);
```

Which will return the signature encoded in the ASN.1 DER format:

```sh
30440220421cfa96cb4f735cc768e8e2acd6bdf87c9b731ded3184f05a146ba0709cf24802204a21831926b140c1fd41b4bae037a0e56df935904f14cf701705d7ad120632c7
```

The class to generate Service Identification Numbers (SINs) works in a similar fashion. Pass the compressed public key in hex form, for example:

```php
$sin = new \Phactor\Sin;

print_r($sin->Generate($info['public_key_compressed']));
```

Which will return a single, BASE-58 encoded value beginning with the letter 'T' (specific value to SINs).  For example:

```sh
Tf61EPoJDSjbp6tGoyjbTKq7XLABPVcyUwY
```

**Note:** When using this class to generate SINs for use in a Bitcoin-related project, the usage of uncompressed public keys is deprecated. Use only the compressed public key when generating a SIN for this purpose!


## License

```
 Copyright (c) 2015 Rich Morgan, rich.l.morgan@gmail.com

 This program is free software; you can redistribute it and/or modify it under
 the terms of the GNU General Public License as published by the Free Software
 Foundation; either version 2 of the License, or (at your option) any later
 version.

 This program is distributed in the hope that it will be useful, but WITHOUT
 ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

 You should have received a copy of the GNU General Public License along with
 this program; if not, write to the Free Software Foundation, Inc., 51 Franklin
 Street, Fifth Floor, Boston, MA 02110-1301 USA.
```
