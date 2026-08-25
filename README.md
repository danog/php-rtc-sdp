# SDP Library for PHP

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-BSD-blue.svg)](LICENSE)

A PHP library for parsing and generating SDP (Session Description Protocol). This package supports media negotiation, ICE parameters, DTLS fingerprints, and codec configurations used in WebRTC.

## About this fork

This is the `danog/php-rtc-sdp` PHP 8.2+ fork used by MadelineProto. It is published under the `danog/php-rtc-sdp` Composer package name.

All internal Composer dependencies use their `danog/php-rtc-*` package names directly, so installing a component selects the maintained danog packages throughout the dependency graph.

## Features

- Parse and build SDP offers and answers
- Support for ICE, DTLS, RTP, SCTP, and media attributes
- Codec negotiation helpers


## Requirements

- PHP ≥ 8.2

## Documentation

This package is part of the PHP WebRTC library. For complete documentation, examples, and API reference, visit:

[PHP WebRTC Documentation](https://www.quasarstream.com/php-webrtc)

## Credits

### Authors

- **Amin Yazdanpanah**  
  - Website: [aminyazdanpanah.com](https://www.aminyazdanpanah.com)
  - Email: [github@aminyazdanpanah.com](mailto:github@aminyazdanpanah.com)

- **Sana Moniri**  
  - GtiHub: [sanamoniri](https://github.com/sanamoniri)

## Reporting Issues

Found a bug? Please report it on our [issues](https://github.com/php-webrtc/sdp/issues).

## License

BSD 3-Clause License. See [LICENSE](LICENSE) for details.

## References

- [RFC 4566 – SDP: Session Description Protocol](https://datatracker.ietf.org/doc/html/rfc4566)
- [RFC 8829 – WebRTC SDP Usage](https://datatracker.ietf.org/doc/html/rfc8829)
