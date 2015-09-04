Items to improve upon or implement:

* Add PHP version checks for versions that use the deprecated OpenSSL extension RAND_pseudo_bytes() function
  * Patched versions are: 5.6.12+, 5.5.28+, 5.4.44+
* Implement deterministic keypairs & signatures
* Replace Double-And-Add algorithm with a more optimized scalar multiplication algorithm
* Multisignature addresses and signatures


Items partially or fully completed:

* Protection against side-channel attacks
  * Added Montgomery Ladder method to protect against simple power/timing analysis
* Better functional tests
  * Test coverage increasing (greatly improved in 1.0.4 release)
