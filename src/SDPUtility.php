<?php

/**
 * This file is part of the PHP WebRTC package.
 *
 * (c) Amin Yazdanpanah <https://www.aminyazdanpanah.com/#contact>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webrtc\SDP;

use Webrtc\Exception\InvalidArgumentException;

final class SDPUtility
{
    private const IP_REGEX = "/^IN (IP4|IP6) ([^ ]+)$/";
    public const FMTP_INT_PARAMETERS = ["cname", "msid", "mslabel", "label"];

    /**
     * Parses a non-negative integer field taken from untrusted remote SDP text.
     *
     * Bare (int)/intval casts silently turn malformed tokens ("abc", "12xyz") into 0, which then
     * slips past range checks and surfaces much later as an opaque failure (port 0, clock rate 0,
     * two distinct SSRCs collapsing to 0, ...). This rejects non-numeric input up front instead.
     *
     * @param string $value The raw token from the SDP line.
     * @param string $field Human-readable field name for the error message.
     * @return int
     * @throws InvalidArgumentException When $value is not a base-10 non-negative integer.
     */
    public static function parseInt(string $value, string $field): int
    {
        if (!ctype_digit($value)) {
            throw new InvalidArgumentException("Invalid $field in SDP, expected a non-negative integer: \"$value\"");
        }
        return (int)$value;
    }

    /**
     * Extracts the IP address from an SDP string.
     *
     * @param string $sdp
     * @return string
     * @throws InvalidArgumentException
     */
    public static function ipAddressFromSDP(string $sdp): string
    {
        if (!preg_match(self::IP_REGEX, $sdp, $matches)) {
            throw new InvalidArgumentException("Invalid SDP format: IP address not found.");
        }
        return $matches[2];
    }

    /**
     * Converts an IP address to an SDP string.
     *
     * @param string $addr
     * @return string
     */
    public static function ipAddressToSDP(string $addr): string
    {
        $version = filter_var($addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? 6 : 4;
        return "IN IP$version $addr";
    }

    /**
     * Extracts parameters from an SDP string.
     *
     * @param string $sdp
     * @return array<string, int|null|string>
     */
    public static function parametersFromSDP(string $sdp): array
    {
        $parameters = [];
        $params = explode(';', $sdp);

        foreach ($params as $param) {
            $parts = explode('=', $param, 2);
            if (isset($parts[1])) {
                $parameters[$parts[0]] = in_array($parts[0], self::FMTP_INT_PARAMETERS) ? (int)$parts[1] : $parts[1];
            } else {
                $parameters[$param] = null;
            }
        }

        return $parameters;
    }

    /**
     * Converts parameters to an SDP string.
     *
     * @param array<string, int|null|string> $parameters
     * @return string
     */
    public static function parametersToSDP(array $parameters): string
    {
        $params = [];
        foreach ($parameters as $k => $v) {
            $params[] = $v !== null ? "$k=$v" : $k;
        }
        return implode(';', $params);
    }

    /**
     * Parses a group description from an SDP string.
     *
     * @param string $value
     * @param string $type
     * @return GroupDescription
     */
    public static function parseGroup(string $value, string $type = 'string'): GroupDescription
    {
        $bits = explode(' ', trim($value));
        $items = array_map(
            fn(string $item) => $type === 'int' ? self::parseInt($item, 'group item') : $item,
            array_slice($bits, 1)
        );
        return new GroupDescription(semantic: $bits[0], items: $items);
    }
}