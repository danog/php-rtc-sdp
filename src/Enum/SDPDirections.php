<?php

/**
 * This file is part of the PHP WebRTC package.
 *
 * (c) Amin Yazdanpanah <https://www.aminyazdanpanah.com/#contact>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webrtc\SDP\Enum;

enum SDPDirections : int
{
    case inactive = 0;
    case sendonly = 1;
    case recvonly = 2;
    case sendrecv = 3;
    case unknown = -1;

    public static function fromString(string $direction): SDPDirections
    {
        return match ($direction) {
            'inactive' => self::inactive,
            'sendonly' => self::sendonly,
            'recvonly' => self::recvonly,
            'sendrecv' => self::sendrecv,
            default => self::unknown,
        };
    }
}
