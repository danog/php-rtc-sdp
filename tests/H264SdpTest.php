<?php

namespace Tests\Webrtc\SDP;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Webrtc\Exception\InvalidArgumentException;
use Webrtc\SDP\BitPattern;
use Webrtc\SDP\Enum\H264Level;
use Webrtc\SDP\Enum\H264Profile;
use Webrtc\SDP\H264Sdp;
use PHPUnit\Framework\TestCase;

#[UsesClass(BitPattern::class)]
#[CoversClass(H264Sdp::class)]
class H264SdpTest extends TestCase
{
    private function assertParseFails($value, $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);
        H264Sdp::parseH264ProfileLevelId($value);
    }

    public function testParseInvalid()
    {
        // Invalid hexadecimal
        $this->assertParseFails(null, "Expected a 6-character hexadecimal string");
        $this->assertParseFails("", "Expected a 6-character hexadecimal string");
        $this->assertParseFails("xyzxyz", "Expected a 6-character hexadecimal string");

        // Invalid level
        $this->assertParseFails("42E000", "0 is not a valid H264Level");
        $this->assertParseFails("42E00F", "15 is not a valid H264Level");
        $this->assertParseFails("42E0FF", "255 is not a valid H264Level");

        // Invalid profile
        $this->assertParseFails("42E11F", "Unrecognized profile_iop = 225, profile_idc = 66");
        $this->assertParseFails("58601F", "Unrecognized profile_iop = 96, profile_idc = 88");
        $this->assertParseFails("64E01F", "Unrecognized profile_iop = 224, profile_idc = 100");
    }

    public function testParseConstrainedBaseline()
    {
        $this->assertEquals([
            H264Profile::PROFILE_CONSTRAINED_BASELINE, H264Level::LEVEL3_1
        ], H264Sdp::parseH264ProfileLevelId("42E01F"));

        $this->assertEquals([
            H264Profile::PROFILE_CONSTRAINED_BASELINE, H264Level::LEVEL1_1
        ], H264Sdp::parseH264ProfileLevelId("42E00B"));

        $this->assertEquals([
            H264Profile::PROFILE_CONSTRAINED_BASELINE, H264Level::LEVEL1_B
        ], H264Sdp::parseH264ProfileLevelId("42F00B"));

        $this->assertEquals([
            H264Profile::PROFILE_CONSTRAINED_BASELINE, H264Level::LEVEL4_2
        ], H264Sdp::parseH264ProfileLevelId("42C02A"));

        $this->assertEquals([
            H264Profile::PROFILE_CONSTRAINED_BASELINE, H264Level::LEVEL3_1
        ], H264Sdp::parseH264ProfileLevelId("58F01F"));
    }

    public function testParseBaseline()
    {
        $this->assertEquals([
            H264Profile::PROFILE_BASELINE, H264Level::LEVEL3_1
        ], H264Sdp::parseH264ProfileLevelId("42001F"));

        $this->assertEquals([
            H264Profile::PROFILE_BASELINE, H264Level::LEVEL3_1
        ], H264Sdp::parseH264ProfileLevelId("42A01F"));

        $this->assertEquals([
            H264Profile::PROFILE_BASELINE, H264Level::LEVEL3_1
        ], H264Sdp::parseH264ProfileLevelId("58A01F"));
    }

    public function testParseMain()
    {
        $this->assertEquals([
            H264Profile::PROFILE_MAIN, H264Level::LEVEL3_1
        ], H264Sdp::parseH264ProfileLevelId("4D401F"));
    }

    public function testParseHigh()
    {
        $this->assertEquals([
            H264Profile::PROFILE_HIGH, H264Level::LEVEL3_1
        ], H264Sdp::parseH264ProfileLevelId("64001F"));
    }

    public function testParseConstrainedHigh()
    {
        $this->assertEquals([
            H264Profile::PROFILE_CONSTRAINED_HIGH, H264Level::LEVEL3_1
        ], H264Sdp::parseH264ProfileLevelId("640C1F"));
    }
}

