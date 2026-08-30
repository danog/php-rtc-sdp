<?php

namespace Tests\Webrtc\SDP;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Webrtc\RTPParameter\RTCRtcpFeedback;
use Webrtc\RTPParameter\RTCRtpCodecParameters;
use Webrtc\RTPParameter\RTCRtpHeaderExtensionParameters;
use Webrtc\SDP\DtlsParameter\RTCDtlsFingerprint;
use Webrtc\SDP\DtlsParameter\RTCDtlsParameters;
use Webrtc\SDP\Enum\DtlsRole;
use Webrtc\SDP\Enum\SDPDirections;
use Webrtc\SDP\GroupDescription;
use Webrtc\SDP\MediaDescription;
use Webrtc\SDP\SctpParameter\RTCSctpCapabilities;
use Webrtc\SDP\SDPUtility;
use Webrtc\SDP\SessionDescription;
use Webrtc\SDP\SsrcDescription;

#[UsesClass(RTCDtlsFingerprint::class)]
#[UsesClass(RTCDtlsParameters::class)]
#[UsesClass(MediaDescription::class)]
#[UsesClass(SDPUtility::class)]
#[UsesClass(GroupDescription::class)]
#[UsesClass(SsrcDescription::class)]
#[UsesClass(RTCSctpCapabilities::class)]
#[UsesClass(\Webrtc\SDP\Enum\SDPDirections::class)]
#[CoversClass(SessionDescription::class)]
class SessionDescriptionTest extends TestCase
{
    public function testAudioChrome()
    {
        $sdp = SessionDescription::decode($this->getSdpContent("audio_chrome"));

        $this->assertEquals([new GroupDescription("BUNDLE", ["audio"])], $sdp->getGroup());
        $this->assertEquals([new GroupDescription("WMS", ["TF6VRif1dxuAfe5uefrV2953LhUZt1keYvxU"])], $sdp->getMsidSemantic());

        $this->assertNull($sdp->getHost());
        $this->assertEquals("-", $sdp->getName());
        $this->assertEquals("- 863426017819471768 2 IN IP4 127.0.0.1", $sdp->getOrigin());
        $this->assertEquals("0 0", $sdp->getTime());
        $this->assertEquals(0, $sdp->getVersion());

        $this->assertCount(1, $sdp->getMedia());
        $this->assertEquals("audio", $sdp->getMedia()[0]->getKind());
        $this->assertEquals("192.168.99.58", $sdp->getMedia()[0]->getHost());
        $this->assertEquals(45076, $sdp->getMedia()[0]->getPort());
        $this->assertEquals("UDP/TLS/RTP/SAVPF", $sdp->getMedia()[0]->getProfile());
        $this->assertEquals(SDPDirections::sendrecv, $sdp->getMedia()[0]->getDirection());
        $this->assertNull($sdp->getMedia()[0]->getMsid());
        $this->assertEquals(
            [
                new RTCRtpCodecParameters("audio/opus", 48000, 2, 111, [
                    new RTCRtcpFeedback("transport-cc")],
                    ["minptime" => 10, "useinbandfec" => 1]
                ),
                new RTCRtpCodecParameters("audio/ISAC", 16000, 1, 103),
                new RTCRtpCodecParameters("audio/ISAC", 32000, 1, 104),
                new RTCRtpCodecParameters("audio/G722", 8000, 1, 9),
                new RTCRtpCodecParameters("audio/PCMU", 8000, 1, 0),
                new RTCRtpCodecParameters("audio/PCMA", 8000, 1, 8),
                new RTCRtpCodecParameters("audio/CN", 32000, 1, 106),
                new RTCRtpCodecParameters("audio/CN", 16000, 1, 105),
                new RTCRtpCodecParameters("audio/CN", 8000, 1, 13),
                new RTCRtpCodecParameters("audio/telephone-event", 48000, 1, 110),
                new RTCRtpCodecParameters("audio/telephone-event", 32000, 1, 112),
                new RTCRtpCodecParameters("audio/telephone-event", 16000, 1, 113),
                new RTCRtpCodecParameters("audio/telephone-event", 8000, 1, 126),
            ],
            $sdp->getMedia()[0]->getRtp()->codecs
        );
        $this->assertEquals(
            [new RTCRtpHeaderExtensionParameters(1, "urn:ietf:params:rtp-hdrext:ssrc-audio-level")],
            $sdp->getMedia()[0]->getRtp()->headerExtensions
        );
        $this->assertEquals("audio", $sdp->getMedia()[0]->getRtp()->muxId);
        $this->assertEquals("0.0.0.0", $sdp->getMedia()[0]->getRtcpHost());
        $this->assertEquals(9, $sdp->getMedia()[0]->getRtcpPort());
        $this->assertTrue($sdp->getMedia()[0]->isRtcpMux());

        // ssrc
        $this->assertEquals(
            [
                new SsrcDescription(
                    1944796561,
                    "/vC4ULAr8vHNjXmq",
                    "TF6VRif1dxuAfe5uefrV2953LhUZt1keYvxU ec1eb8de-8df8-4956-ae81-879e5d062d12",
                    "TF6VRif1dxuAfe5uefrV2953LhUZt1keYvxU",
                    "ec1eb8de-8df8-4956-ae81-879e5d062d12"
                )
            ],
            $sdp->getMedia()[0]->getSsrc()
        );
        $this->assertEquals([], $sdp->getMedia()[0]->getSsrcGroup());

        // formats
        $this->assertEquals(
            [111, 103, 104, 9, 0, 8, 106, 105, 13, 110, 112, 113, 126], $sdp->getMedia()[0]->getFmt()
        );
        $this->assertEquals([], $sdp->getMedia()[0]->getSctpmap());
        $this->assertNull($sdp->getMedia()[0]->getSctpPort());

        // ice
        $this->assertCount(4, $sdp->getMedia()[0]->getIceCandidates());
        $this->assertFalse($sdp->getMedia()[0]->isIceCandidatesComplete());
        $this->assertEquals("trickle", $sdp->getMedia()[0]->getIceOptions());
        $this->assertFalse($sdp->getMedia()[0]->getIce()->iceLite);
        $this->assertEquals("5+Ix", $sdp->getMedia()[0]->getIce()->usernameFragment);
        $this->assertEquals("uK8IlylxzDMUhrkVzdmj0M+v", $sdp->getMedia()[0]->getIce()->password);

        // dtls
        $this->assertCount(1, $sdp->getMedia()[0]->getDtls()->fingerprints);
        $this->assertEquals("sha-256", $sdp->getMedia()[0]->getDtls()->fingerprints[0]->algorithm);
        $this->assertEquals(
            "6B:8B:5D:EA:59:04:20:23:29:C8:87:1C:CC:87:32:BE:DD:8C:66:A5:8E:50:55:EA:8C:D3:B6:5C:09:5E:D6:BC",
            $sdp->getMedia()[0]->getDtls()->fingerprints[0]->value
        );
        $this->assertEquals(DtlsRole::Auto, $sdp->getMedia()[0]->getDtls()->role);

        $this->assertEqualsIgnoringCase($this->getSdpContent("audio_chrome", true), trim((string)$sdp));
    }

    public function testAudioFirefox()
    {
        $sdp = SessionDescription::decode($this->getSdpContent("audio_firefox"));

        $this->assertEquals([new GroupDescription("BUNDLE", ["sdparta_0"])], $sdp->getGroup());
        $this->assertEquals([new GroupDescription("WMS", ["*"])], $sdp->getMsidSemantic());

        $this->assertNull($sdp->getHost());
        $this->assertEquals("-", $sdp->getName());
        $this->assertEquals("mozilla...THIS_IS_SDPARTA-58.0.1 4934139885953732403 1 IN IP4 0.0.0.0", $sdp->getOrigin());
        $this->assertEquals("0 0", $sdp->getTime());
        $this->assertEquals(0, $sdp->getVersion());

        $this->assertCount(1, $sdp->getMedia());
        $this->assertEquals("audio", $sdp->getMedia()[0]->getKind());
        $this->assertEquals("192.168.99.58", $sdp->getMedia()[0]->getHost());
        $this->assertEquals(45274, $sdp->getMedia()[0]->getPort());
        $this->assertEquals("UDP/TLS/RTP/SAVPF", $sdp->getMedia()[0]->getProfile());
        $this->assertEquals(SDPDirections::sendrecv, $sdp->getMedia()[0]->getDirection());
        $this->assertEquals("{dee771c7-671a-451e-b847-f86f8e87c7d8} {12692dea-686c-47ca-b3e9-48f38fc92b78}", $sdp->getMedia()[0]->getMsid());
        $this->assertEquals(
            [
                new RTCRtpCodecParameters(
                    "audio/opus",
                    48000,
                    2,
                    109,
                    [],
                    ["maxplaybackrate" => 48000, "stereo" => 1, "useinbandfec" => 1]
                ),
                new RTCRtpCodecParameters("audio/G722", 8000, 1, 9),
                new RTCRtpCodecParameters("audio/PCMU", 8000, 1, 0),
                new RTCRtpCodecParameters("audio/PCMA", 8000, 1, 8),
                new RTCRtpCodecParameters("audio/telephone-event", 8000, 1, 101, parameters: ["0-15" => null]),
            ],
            $sdp->getMedia()[0]->getRtp()->codecs
        );
        $this->assertEquals(
            [
                new RTCRtpHeaderExtensionParameters(1, "urn:ietf:params:rtp-hdrext:ssrc-audio-level"),
                new RTCRtpHeaderExtensionParameters(2, "urn:ietf:params:rtp-hdrext:sdes:mid"),
            ],
            $sdp->getMedia()[0]->getRtp()->headerExtensions
        );
        $this->assertEquals("sdparta_0", $sdp->getMedia()[0]->getRtp()->muxId);
        $this->assertEquals("192.168.99.58", $sdp->getMedia()[0]->getRtcpHost());
        $this->assertEquals(38612, $sdp->getMedia()[0]->getRtcpPort());
        $this->assertTrue($sdp->getMedia()[0]->isRtcpMux());
        $this->assertEquals(
            "{12692dea-686c-47ca-b3e9-48f38fc92b78}", $sdp->webrtcTrackId($sdp->getMedia()[0])
        );

        // ssrc
        $this->assertEquals(
            [
                new SsrcDescription(
                    882128807, "{ed463ac5-dabf-44d4-8b9f-e14318427b2b}"
                )
            ],
            $sdp->getMedia()[0]->getSsrc()
        );
        $this->assertEquals([], $sdp->getMedia()[0]->getSsrcGroup());

        // formats
        $this->assertEquals([109, 9, 0, 8, 101], $sdp->getMedia()[0]->getFmt());
        $this->assertEquals([], $sdp->getMedia()[0]->getSctpmap());
        $this->assertNull($sdp->getMedia()[0]->getSctpPort());

        // ice
        $this->assertCount(10, $sdp->getMedia()[0]->getIceCandidates());
        $this->assertTrue($sdp->getMedia()[0]->isIceCandidatesComplete());
        $this->assertEquals("trickle", $sdp->getMedia()[0]->getIceOptions());
        $this->assertFalse($sdp->getMedia()[0]->getIce()->iceLite);
        $this->assertEquals("403a81e1", $sdp->getMedia()[0]->getIce()->usernameFragment);
        $this->assertEquals("f9b83487285016f7492197a5790ceee5", $sdp->getMedia()[0]->getIce()->password);

        // dtls
        $this->assertCount(1, $sdp->getMedia()[0]->getDtls()->fingerprints);
        $this->assertEquals("sha-256", $sdp->getMedia()[0]->getDtls()->fingerprints[0]->algorithm);
        $this->assertEquals(
            "EB:A9:3E:50:D7:E3:B3:86:0F:7B:01:C1:EB:D6:AF:E4:97:DE:15:05:A8:DE:7B:83:56:C7:4B:6E:9D:75:D4:17",
            $sdp->getMedia()[0]->getDtls()->fingerprints[0]->value
        );
        $this->assertEquals(DtlsRole::Auto, $sdp->getMedia()[0]->getDtls()->role);

        $this->assertEqualsIgnoringCase($this->getSdpContent("audio_firefox", true), trim((string)$sdp));
    }

    public function testAudioFreeswitch()
    {
        $sdp = SessionDescription::decode($this->getSdpContent("audio_freeswitch"));

        $this->assertEquals([], $sdp->getGroup());
        $this->assertEquals([new GroupDescription("WMS", ["lyNSTe6w2ijnMrDEiqTHFyhqjdAag3ys"])], $sdp->getMsidSemantic());

        $this->assertEquals("1.2.3.4", $sdp->getHost());
        $this->assertEquals("FreeSWITCH", $sdp->getName());
        $this->assertEquals("FreeSWITCH 1538380016 1538380017 IN IP4 1.2.3.4", $sdp->getOrigin());
        $this->assertEquals("0 0", $sdp->getTime());
        $this->assertEquals(0, $sdp->getVersion());

        $this->assertCount(1, $sdp->getMedia());
        $this->assertEquals("audio", $sdp->getMedia()[0]->getKind());
        $this->assertNull($sdp->getMedia()[0]->getHost());
        $this->assertEquals(16628, $sdp->getMedia()[0]->getPort());
        $this->assertEquals("UDP/TLS/RTP/SAVPF", $sdp->getMedia()[0]->getProfile());
        $this->assertNull($sdp->getMedia()[0]->getDirection());
        $this->assertEquals(
            [
                new RTCRtpCodecParameters("audio/PCMA", 8000, 1, 8),
                new RTCRtpCodecParameters("audio/telephone-event", 8000, 1, 101),
            ],
            $sdp->getMedia()[0]->getRtp()->codecs
        );
        $this->assertEquals([], $sdp->getMedia()[0]->getRtp()->headerExtensions);
        $this->assertEquals("", $sdp->getMedia()[0]->getRtp()->muxId);
        $this->assertEquals("1.2.3.4", $sdp->getMedia()[0]->getRtcpHost());
        $this->assertEquals(16628, $sdp->getMedia()[0]->getRtcpPort());
        $this->assertTrue($sdp->getMedia()[0]->isRtcpMux());

        // ssrc
        $this->assertEquals(
            [
                new SsrcDescription(
                    2690029308,
                    "rbaag6w9fGmRXQm6",
                    "lyNSTe6w2ijnMrDEiqTHFyhqjdAag3ys a0",
                    "lyNSTe6w2ijnMrDEiqTHFyhqjdAag3ys",
                    "lyNSTe6w2ijnMrDEiqTHFyhqjdAag3ysa0"
                )
            ],
            $sdp->getMedia()[0]->getSsrc()
        );
        $this->assertEquals([], $sdp->getMedia()[0]->getSsrcGroup());

        // formats
        $this->assertEquals([8, 101], $sdp->getMedia()[0]->getFmt());
        $this->assertEquals([], $sdp->getMedia()[0]->getSctpmap());
        $this->assertNull($sdp->getMedia()[0]->getSctpPort());

        // ice
        $this->assertCount(1, $sdp->getMedia()[0]->getIceCandidates());
        $this->assertTrue($sdp->getMedia()[0]->isIceCandidatesComplete());
        $this->assertNull($sdp->getMedia()[0]->getIceOptions());
        $this->assertFalse($sdp->getMedia()[0]->getIce()->iceLite);
        $this->assertEquals("75EDuLTEOkEUd3cu", $sdp->getMedia()[0]->getIce()->usernameFragment);
        $this->assertEquals("5dvb9SbfooWc49814CupdeTS", $sdp->getMedia()[0]->getIce()->password);

        // dtls
        $this->assertCount(1, $sdp->getMedia()[0]->getDtls()->fingerprints);
        $this->assertEquals("sha-256", $sdp->getMedia()[0]->getDtls()->fingerprints[0]->algorithm);
        $this->assertEquals(
            "35:5A:BC:8E:CD:F8:CD:EB:36:00:BB:C4:C3:33:54:B5:9B:70:3C:E9:C4:33:8F:39:3C:4B:5B:5C:AD:88:12:2B",
            $sdp->getMedia()[0]->getDtls()->fingerprints[0]->value
        );
        $this->assertEquals(DtlsRole::Client, $sdp->getMedia()[0]->getDtls()->role);

        $this->assertEqualsIgnoringCase($this->getSdpContent("audio_freeswitch", true), trim((string)$sdp));
    }

    public function testAudioFreeswitchNoDtls()
    {
        $sdp = SessionDescription::decode($this->getSdpContent("audio_freeswitch_no_dtls"));

        $this->assertEquals([], $sdp->getGroup());
        $this->assertEquals([new GroupDescription("WMS", ["lyNSTe6w2ijnMrDEiqTHFyhqjdAag3ys"])], $sdp->getMsidSemantic());

        $this->assertEquals("1.2.3.4", $sdp->getHost());
        $this->assertEquals("FreeSWITCH", $sdp->getName());
        $this->assertEquals("FreeSWITCH 1538380016 1538380017 IN IP4 1.2.3.4", $sdp->getOrigin());
        $this->assertEquals("0 0", $sdp->getTime());
        $this->assertEquals(0, $sdp->getVersion());

        $this->assertCount(1, $sdp->getMedia());
        $this->assertEquals("audio", $sdp->getMedia()[0]->getKind());
        $this->assertNull($sdp->getMedia()[0]->getHost());
        $this->assertEquals(16628, $sdp->getMedia()[0]->getPort());
        $this->assertEquals("UDP/TLS/RTP/SAVPF", $sdp->getMedia()[0]->getProfile());
        $this->assertNull($sdp->getMedia()[0]->getDirection());
        $this->assertEquals(
            [
                new RTCRtpCodecParameters("audio/PCMA", 8000, 1, 8),
                new RTCRtpCodecParameters("audio/telephone-event", 8000, 1, 101),
            ],
            $sdp->getMedia()[0]->getRtp()->codecs
        );
        $this->assertEquals([], $sdp->getMedia()[0]->getRtp()->headerExtensions);
        $this->assertEquals("", $sdp->getMedia()[0]->getRtp()->muxId);
        $this->assertEquals("1.2.3.4", $sdp->getMedia()[0]->getRtcpHost());
        $this->assertEquals(16628, $sdp->getMedia()[0]->getRtcpPort());
        $this->assertTrue($sdp->getMedia()[0]->isRtcpMux());

        // ssrc
        $this->assertEquals(
            [
                new SsrcDescription(
                    2690029308,
                    "rbaag6w9fGmRXQm6",
                    "lyNSTe6w2ijnMrDEiqTHFyhqjdAag3ys a0",
                    "lyNSTe6w2ijnMrDEiqTHFyhqjdAag3ys",
                    "lyNSTe6w2ijnMrDEiqTHFyhqjdAag3ysa0"
                )
            ],
            $sdp->getMedia()[0]->getSsrc()
        );
        $this->assertEquals([], $sdp->getMedia()[0]->getSsrcGroup());

        // formats
        $this->assertEquals([8, 101], $sdp->getMedia()[0]->getFmt());
        $this->assertEquals([], $sdp->getMedia()[0]->getSctpmap());
        $this->assertNull($sdp->getMedia()[0]->getSctpPort());

        // ice
        $this->assertCount(1, $sdp->getMedia()[0]->getIceCandidates());
        $this->assertTrue($sdp->getMedia()[0]->isIceCandidatesComplete());
        $this->assertNull($sdp->getMedia()[0]->getIceOptions());
        $this->assertFalse($sdp->getMedia()[0]->getIce()->iceLite);
        $this->assertEquals("75EDuLTEOkEUd3cu", $sdp->getMedia()[0]->getIce()->usernameFragment);
        $this->assertEquals("5dvb9SbfooWc49814CupdeTS", $sdp->getMedia()[0]->getIce()->password);

        // dtls
        $this->assertEmpty($sdp->getMedia()[0]->getDtls()->fingerprints);

        $this->assertEqualsIgnoringCase($this->getSdpContent("audio_freeswitch_no_dtls", true), trim((string)$sdp));
    }

    public function testAudioDtlsSessionLevel()
    {
        $sdp = SessionDescription::decode($this->getSdpContent("audio_dtls_session_level"));

        $this->assertEmpty($sdp->getGroup());
        $this->assertEmpty($sdp->getMsidSemantic());

        $this->assertNull($sdp->getHost());
        $this->assertEquals("-", $sdp->getName());
        $this->assertEquals("- 863426017819471768 2 IN IP4 127.0.0.1", $sdp->getOrigin());
        $this->assertEquals("0 0", $sdp->getTime());
        $this->assertEquals(0, $sdp->getVersion());

        $this->assertCount(1, $sdp->getMedia());
        $this->assertEquals("audio", $sdp->getMedia()[0]->getKind());
        $this->assertEquals("192.168.99.58", $sdp->getMedia()[0]->getHost());
        $this->assertEquals(45076, $sdp->getMedia()[0]->getPort());
        $this->assertEquals("UDP/TLS/RTP/SAVPF", $sdp->getMedia()[0]->getProfile());
        $this->assertEquals(SDPDirections::sendrecv, $sdp->getMedia()[0]->getDirection());
        $this->assertNull($sdp->getMedia()[0]->getMsid());
        $this->assertEquals(
            [
                new RTCRtpCodecParameters("audio/PCMU", 8000, 1, 0),
                new RTCRtpCodecParameters("audio/PCMA", 8000, 1, 8),
            ],
            $sdp->getMedia()[0]->getRtp()->codecs
        );
        $this->assertEmpty($sdp->getMedia()[0]->getRtp()->headerExtensions);
        $this->assertEquals("audio", $sdp->getMedia()[0]->getRtp()->muxId);
        $this->assertEquals("0.0.0.0", $sdp->getMedia()[0]->getRtcpHost());
        $this->assertEquals(9, $sdp->getMedia()[0]->getRtcpPort());
        $this->assertTrue($sdp->getMedia()[0]->isRtcpMux());

        // ssrc
        $this->assertEmpty($sdp->getMedia()[0]->getSsrc());
        $this->assertEmpty($sdp->getMedia()[0]->getSsrcGroup());

        // formats
        $this->assertEquals([0, 8], $sdp->getMedia()[0]->getFmt());
        $this->assertEmpty($sdp->getMedia()[0]->getSctpmap());
        $this->assertNull($sdp->getMedia()[0]->getSctpPort());

        // ice
        $this->assertCount(2, $sdp->getMedia()[0]->getIceCandidates());
        $this->assertFalse($sdp->getMedia()[0]->isIceCandidatesComplete());
        $this->assertNull($sdp->getMedia()[0]->getIceOptions());
        $this->assertFalse($sdp->getMedia()[0]->getIce()->iceLite);
        $this->assertEquals("5+Ix", $sdp->getMedia()[0]->getIce()->usernameFragment);
        $this->assertEquals("uK8IlylxzDMUhrkVzdmj0M+v", $sdp->getMedia()[0]->getIce()->password);

        // dtls
        $this->assertCount(1, $sdp->getMedia()[0]->getDtls()->fingerprints);
        $this->assertEquals("sha-256", $sdp->getMedia()[0]->getDtls()->fingerprints[0]->algorithm);
        $this->assertEquals(
            "6B:8B:5D:EA:59:04:20:23:29:C8:87:1C:CC:87:32:BE:DD:8C:66:A5:8E:50:55:EA:8C:D3:B6:5C:09:5E:D6:BC",
            $sdp->getMedia()[0]->getDtls()->fingerprints[0]->value
        );
        $this->assertEquals(DtlsRole::Auto, $sdp->getMedia()[0]->getDtls()->role);

        $this->assertEqualsIgnoringCase($this->getSdpContent("audio_dtls_session_level", true), trim((string)$sdp));
    }

    public function testAudioIceLite()
    {
        $sdp = SessionDescription::decode($this->getSdpContent("audio_ice_lite"));

        $this->assertEmpty($sdp->getGroup());
        $this->assertEmpty($sdp->getMsidSemantic());

        $this->assertNull($sdp->getHost());
        $this->assertEquals("-", $sdp->getName());
        $this->assertEquals("- 863426017819471768 2 IN IP4 127.0.0.1", $sdp->getOrigin());
        $this->assertEquals("0 0", $sdp->getTime());
        $this->assertEquals(0, $sdp->getVersion());

        $this->assertCount(1, $sdp->getMedia());
        $this->assertEquals("audio", $sdp->getMedia()[0]->getKind());
        $this->assertEquals("192.168.99.58", $sdp->getMedia()[0]->getHost());
        $this->assertEquals(45076, $sdp->getMedia()[0]->getPort());
        $this->assertEquals("UDP/TLS/RTP/SAVPF", $sdp->getMedia()[0]->getProfile());
        $this->assertEquals(SDPDirections::sendrecv, $sdp->getMedia()[0]->getDirection());
        $this->assertNull($sdp->getMedia()[0]->getMsid());
        $this->assertEquals(
            [
                new RTCRtpCodecParameters("audio/PCMU", 8000, 1, 0),
                new RTCRtpCodecParameters("audio/PCMA", 8000, 1, 8),
            ],
            $sdp->getMedia()[0]->getRtp()->codecs
        );
        $this->assertEmpty($sdp->getMedia()[0]->getRtp()->headerExtensions);
        $this->assertEquals("audio", $sdp->getMedia()[0]->getRtp()->muxId);
        $this->assertEquals("0.0.0.0", $sdp->getMedia()[0]->getRtcpHost());
        $this->assertEquals(9, $sdp->getMedia()[0]->getRtcpPort());
        $this->assertTrue($sdp->getMedia()[0]->isRtcpMux());

        // ssrc
        $this->assertEmpty($sdp->getMedia()[0]->getSsrc());
        $this->assertEmpty($sdp->getMedia()[0]->getSsrcGroup());

        // formats
        $this->assertEquals([0, 8], $sdp->getMedia()[0]->getFmt());
        $this->assertEmpty($sdp->getMedia()[0]->getSctpmap());
        $this->assertNull($sdp->getMedia()[0]->getSctpPort());

        // ice
        $this->assertCount(2, $sdp->getMedia()[0]->getIceCandidates());
        $this->assertFalse($sdp->getMedia()[0]->isIceCandidatesComplete());
        $this->assertNull($sdp->getMedia()[0]->getIceOptions());
        $this->assertTrue($sdp->getMedia()[0]->getIce()->iceLite);
        $this->assertEquals("5+Ix", $sdp->getMedia()[0]->getIce()->usernameFragment);
        $this->assertEquals("uK8IlylxzDMUhrkVzdmj0M+v", $sdp->getMedia()[0]->getIce()->password);

        // dtls
        $this->assertCount(1, $sdp->getMedia()[0]->getDtls()->fingerprints);
        $this->assertEquals("sha-256", $sdp->getMedia()[0]->getDtls()->fingerprints[0]->algorithm);
        $this->assertEquals(
            "6B:8B:5D:EA:59:04:20:23:29:C8:87:1C:CC:87:32:BE:DD:8C:66:A5:8E:50:55:EA:8C:D3:B6:5C:09:5E:D6:BC",
            $sdp->getMedia()[0]->getDtls()->fingerprints[0]->value
        );
        $this->assertEquals(DtlsRole::Auto, $sdp->getMedia()[0]->getDtls()->role);

        $this->assertEqualsIgnoringCase($this->getSdpContent("audio_ice_lite", true), trim((string)$sdp));
    }

    public function testAudioIceSessionLevelCredentials()
    {
        $sdp = SessionDescription::decode($this->getSdpContent("audio_ice_session_level_credentials"));

        $this->assertEmpty($sdp->getGroup());
        $this->assertEmpty($sdp->getMsidSemantic());

        $this->assertNull($sdp->getHost());
        $this->assertEquals("-", $sdp->getName());
        $this->assertEquals("- 863426017819471768 2 IN IP4 127.0.0.1", $sdp->getOrigin());
        $this->assertEquals("0 0", $sdp->getTime());
        $this->assertEquals(0, $sdp->getVersion());

        $this->assertCount(1, $sdp->getMedia());
        $this->assertEquals("audio", $sdp->getMedia()[0]->getKind());
        $this->assertEquals("192.168.99.58", $sdp->getMedia()[0]->getHost());
        $this->assertEquals(45076, $sdp->getMedia()[0]->getPort());
        $this->assertEquals("UDP/TLS/RTP/SAVPF", $sdp->getMedia()[0]->getProfile());
        $this->assertEquals(SDPDirections::sendrecv, $sdp->getMedia()[0]->getDirection());
        $this->assertNull($sdp->getMedia()[0]->getMsid());
        $this->assertEquals(
            [
                new RTCRtpCodecParameters("audio/PCMU", 8000, 1, 0),
                new RTCRtpCodecParameters("audio/PCMA", 8000, 1, 8),
            ],
            $sdp->getMedia()[0]->getRtp()->codecs
        );
        $this->assertEmpty($sdp->getMedia()[0]->getRtp()->headerExtensions);
        $this->assertEquals("audio", $sdp->getMedia()[0]->getRtp()->muxId);
        $this->assertEquals("0.0.0.0", $sdp->getMedia()[0]->getRtcpHost());
        $this->assertEquals(9, $sdp->getMedia()[0]->getRtcpPort());
        $this->assertTrue($sdp->getMedia()[0]->isRtcpMux());

        // ssrc
        $this->assertEmpty($sdp->getMedia()[0]->getSsrc());
        $this->assertEmpty($sdp->getMedia()[0]->getSsrcGroup());

        // formats
        $this->assertEquals([0, 8], $sdp->getMedia()[0]->getFmt());
        $this->assertEmpty($sdp->getMedia()[0]->getSctpmap());
        $this->assertNull($sdp->getMedia()[0]->getSctpPort());

        // ice
        $this->assertCount(2, $sdp->getMedia()[0]->getIceCandidates());
        $this->assertFalse($sdp->getMedia()[0]->isIceCandidatesComplete());
        $this->assertNull($sdp->getMedia()[0]->getIceOptions());
        $this->assertFalse($sdp->getMedia()[0]->getIce()->iceLite);
        $this->assertEquals("5+Ix", $sdp->getMedia()[0]->getIce()->usernameFragment);
        $this->assertEquals("uK8IlylxzDMUhrkVzdmj0M+v", $sdp->getMedia()[0]->getIce()->password);

        // dtls
        $this->assertCount(1, $sdp->getMedia()[0]->getDtls()->fingerprints);
        $this->assertEquals("sha-256", $sdp->getMedia()[0]->getDtls()->fingerprints[0]->algorithm);
        $this->assertEquals(
            "6B:8B:5D:EA:59:04:20:23:29:C8:87:1C:CC:87:32:BE:DD:8C:66:A5:8E:50:55:EA:8C:D3:B6:5C:09:5E:D6:BC",
            $sdp->getMedia()[0]->getDtls()->fingerprints[0]->value
        );
        $this->assertEquals(DtlsRole::Auto, $sdp->getMedia()[0]->getDtls()->role);

        $this->assertEqualsIgnoringCase($this->getSdpContent("audio_ice_session_level_credentials", true), trim((string)$sdp));
    }

    public function testDatachannelFirefox()
    {
        $sdp = SessionDescription::decode($this->getSdpContent("datachannel_firefox"));

        $this->assertEquals([new GroupDescription("BUNDLE", ["sdparta_0"])], $sdp->getGroup());
        $this->assertEquals([new GroupDescription("WMS", ["*"])], $sdp->getMsidSemantic());

        $this->assertNull($sdp->getHost());
        $this->assertEquals("-", $sdp->getName());
        $this->assertEquals("mozilla...THIS_IS_SDPARTA-58.0.1 7514673380034989017 0 IN IP4 0.0.0.0", $sdp->getOrigin());
        $this->assertEquals("0 0", $sdp->getTime());
        $this->assertEquals(0, $sdp->getVersion());

        $this->assertCount(1, $sdp->getMedia());
        $this->assertEquals("application", $sdp->getMedia()[0]->getKind());
        $this->assertEquals("192.168.99.58", $sdp->getMedia()[0]->getHost());
        $this->assertEquals(45791, $sdp->getMedia()[0]->getPort());
        $this->assertEquals("DTLS/SCTP", $sdp->getMedia()[0]->getProfile());
        $this->assertEquals(["5000"], $sdp->getMedia()[0]->getFmt());

        // sctp
        $this->assertEquals([5000 => "webrtc-datachannel 256"], $sdp->getMedia()[0]->getSctpmap());
        $this->assertNull($sdp->getMedia()[0]->getSctpPort());
        $this->assertNotEmpty($sdp->getMedia()[0]->getSctpCapabilities());
        $this->assertEquals(1073741823, $sdp->getMedia()[0]->getSctpCapabilities()->maxMessageSize);

        // ice
        $this->assertCount(4, $sdp->getMedia()[0]->getIceCandidates());
        $this->assertTrue($sdp->getMedia()[0]->isIceCandidatesComplete());
        $this->assertEquals("trickle", $sdp->getMedia()[0]->getIceOptions());
        $this->assertFalse($sdp->getMedia()[0]->getIce()->iceLite);
        $this->assertEquals("9889e0c4", $sdp->getMedia()[0]->getIce()->usernameFragment);
        $this->assertEquals("d30a5aec4dd81f07d4ff3344209400ab", $sdp->getMedia()[0]->getIce()->password);

        // dtls
        $this->assertCount(1, $sdp->getMedia()[0]->getDtls()->fingerprints);
        $this->assertEquals("sha-256", $sdp->getMedia()[0]->getDtls()->fingerprints[0]->algorithm);
        $this->assertEquals(
            "39:4A:09:1E:0E:33:32:85:51:03:49:95:54:0B:41:09:A2:10:60:CC:39:8F:C0:C4:45:FC:37:3A:55:EA:11:74",
            $sdp->getMedia()[0]->getDtls()->fingerprints[0]->value
        );
        $this->assertEquals(DtlsRole::Auto, $sdp->getMedia()[0]->getDtls()->role);

        $this->assertEqualsIgnoringCase($this->getSdpContent("datachannel_firefox", true), trim((string)$sdp));
    }

    public function testDatachannelFirefox63()
    {
        $sdp = SessionDescription::decode($this->getSdpContent("datachannel_firefox_63"));

        $this->assertEquals([new GroupDescription("BUNDLE", ["sdparta_0"])], $sdp->getGroup());
        $this->assertEquals([new GroupDescription("WMS", ["*"])], $sdp->getMsidSemantic());

        $this->assertNull($sdp->getHost());
        $this->assertEquals("-", $sdp->getName());
        $this->assertEquals("mozilla...THIS_IS_SDPARTA-58.0.1 7514673380034989017 0 IN IP4 0.0.0.0", $sdp->getOrigin());
        $this->assertEquals("0 0", $sdp->getTime());
        $this->assertEquals(0, $sdp->getVersion());

        $this->assertCount(1, $sdp->getMedia());
        $this->assertEquals("application", $sdp->getMedia()[0]->getKind());
        $this->assertEquals("192.168.99.58", $sdp->getMedia()[0]->getHost());
        $this->assertEquals(45791, $sdp->getMedia()[0]->getPort());
        $this->assertEquals("UDP/DTLS/SCTP", $sdp->getMedia()[0]->getProfile());
        $this->assertEquals(["webrtc-datachannel"], $sdp->getMedia()[0]->getFmt());

        // sctp
        $this->assertEmpty($sdp->getMedia()[0]->getSctpmap());
        $this->assertEquals(5000, $sdp->getMedia()[0]->getSctpPort());
        $this->assertNotEmpty($sdp->getMedia()[0]->getSctpCapabilities());
        $this->assertEquals(1073741823, $sdp->getMedia()[0]->getSctpCapabilities()->maxMessageSize);

        // ice
        $this->assertCount(4, $sdp->getMedia()[0]->getIceCandidates());
        $this->assertTrue($sdp->getMedia()[0]->isIceCandidatesComplete());
        $this->assertEquals("trickle", $sdp->getMedia()[0]->getIceOptions());
        $this->assertFalse($sdp->getMedia()[0]->getIce()->iceLite);
        $this->assertEquals("9889e0c4", $sdp->getMedia()[0]->getIce()->usernameFragment);
        $this->assertEquals("d30a5aec4dd81f07d4ff3344209400ab", $sdp->getMedia()[0]->getIce()->password);

        // dtls
        $this->assertCount(1, $sdp->getMedia()[0]->getDtls()->fingerprints);
        $this->assertEquals("sha-256", $sdp->getMedia()[0]->getDtls()->fingerprints[0]->algorithm);
        $this->assertEquals(
            "39:4A:09:1E:0E:33:32:85:51:03:49:95:54:0B:41:09:A2:10:60:CC:39:8F:C0:C4:45:FC:37:3A:55:EA:11:74",
            $sdp->getMedia()[0]->getDtls()->fingerprints[0]->value
        );
        $this->assertEquals(DtlsRole::Auto, $sdp->getMedia()[0]->getDtls()->role);

        $this->assertEqualsIgnoringCase($this->getSdpContent("datachannel_firefox_63", true), trim((string)$sdp));
    }

    public function testVideoChrome()
    {
        $sdp = SessionDescription::decode($this->getSdpContent("video_chrome"));

        $this->assertEquals([new GroupDescription("BUNDLE", ["video"])], $sdp->getGroup());
        $this->assertEquals([new GroupDescription("WMS", ["bbgewhUzS6hvFDlSlrhQ6zYlwW7ttRrK8QeQ"])], $sdp->getMsidSemantic());

        $this->assertNull($sdp->getHost());
        $this->assertEquals("-", $sdp->getName());
        $this->assertEquals("- 5195484278799753993 2 IN IP4 127.0.0.1", $sdp->getOrigin());
        $this->assertEquals("0 0", $sdp->getTime());
        $this->assertEquals(0, $sdp->getVersion());

        $this->assertCount(1, $sdp->getMedia());
        $this->assertEquals("video", $sdp->getMedia()[0]->getKind());
        $this->assertEquals("10.101.2.67", $sdp->getMedia()[0]->getHost());
        $this->assertEquals(34955, $sdp->getMedia()[0]->getPort());
        $this->assertEquals("UDP/TLS/RTP/SAVPF", $sdp->getMedia()[0]->getProfile());
        $this->assertEquals(SDPDirections::sendrecv, $sdp->getMedia()[0]->getDirection());
        $this->assertNull($sdp->getMedia()[0]->getMsid());
        $this->assertEquals(
            [
                new RTCRtpCodecParameters("video/VP8", 90000, payloadType: 96, rtcpFeedback: [
                    new RTCRtcpFeedback("goog-remb"),
                    new RTCRtcpFeedback("transport-cc"),
                    new RTCRtcpFeedback("ccm", "fir"),
                    new RTCRtcpFeedback("nack"),
                    new RTCRtcpFeedback("nack", "pli"),
                ]),
                new RTCRtpCodecParameters("video/rtx", 90000, payloadType: 97, parameters: ["apt" => 96]),
                new RTCRtpCodecParameters("video/VP9", 90000, payloadType: 98, rtcpFeedback: [
                    new RTCRtcpFeedback("goog-remb"),
                    new RTCRtcpFeedback("transport-cc"),
                    new RTCRtcpFeedback("ccm", "fir"),
                    new RTCRtcpFeedback("nack"),
                    new RTCRtcpFeedback("nack", "pli"),
                ]
                ),
                new RTCRtpCodecParameters("video/rtx", 90000, payloadType: 99, parameters: ["apt" => 98]),
                new RTCRtpCodecParameters("video/red", 90000, payloadType: 100),
                new RTCRtpCodecParameters("video/rtx", 90000, payloadType: 101, parameters: ["apt" => 100]),
                new RTCRtpCodecParameters("video/ulpfec", 90000, payloadType: 102),
            ],
            $sdp->getMedia()[0]->getRtp()->codecs
        );
        $this->assertEquals(
            [
                new RTCRtpHeaderExtensionParameters(2, "urn:ietf:params:rtp-hdrext:toffset"),
                new RTCRtpHeaderExtensionParameters(3, "http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time"),
                new RTCRtpHeaderExtensionParameters(4, "urn:3gpp:video-orientation"),
                new RTCRtpHeaderExtensionParameters(5, "http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01"),
                new RTCRtpHeaderExtensionParameters(6, "http://www.webrtc.org/experiments/rtp-hdrext/playout-delay"),
                new RTCRtpHeaderExtensionParameters(7, "http://www.webrtc.org/experiments/rtp-hdrext/video-content-type"),
                new RTCRtpHeaderExtensionParameters(8, "http://www.webrtc.org/experiments/rtp-hdrext/video-timing"),
            ],
            $sdp->getMedia()[0]->getRtp()->headerExtensions
        );
        $this->assertEquals("video", $sdp->getMedia()[0]->getRtp()->muxId);
        $this->assertEquals("0.0.0.0", $sdp->getMedia()[0]->getRtcpHost());
        $this->assertEquals(9, $sdp->getMedia()[0]->getRtcpPort());
        $this->assertTrue($sdp->getMedia()[0]->isRtcpMux());
        $this->assertNull($sdp->webrtcTrackId($sdp->getMedia()[0]));

        // ssrc
        $this->assertEquals(
            [
                new SsrcDescription(
                    1845476211,
                    "9iW3jspLCZJ5WjOZ",
                    "bbgewhUzS6hvFDlSlrhQ6zYlwW7ttRrK8QeQ 420c6f28-439d-4ead-b93c-94e14c0a16b4",
                    "bbgewhUzS6hvFDlSlrhQ6zYlwW7ttRrK8QeQ",
                    "420c6f28-439d-4ead-b93c-94e14c0a16b4"
                ),
                new SsrcDescription(
                    3305256354,
                    "9iW3jspLCZJ5WjOZ",
                    "bbgewhUzS6hvFDlSlrhQ6zYlwW7ttRrK8QeQ 420c6f28-439d-4ead-b93c-94e14c0a16b4",
                    "bbgewhUzS6hvFDlSlrhQ6zYlwW7ttRrK8QeQ",
                    "420c6f28-439d-4ead-b93c-94e14c0a16b4"
                ),
            ],
            $sdp->getMedia()[0]->getSsrc()
        );
        $this->assertEquals([new GroupDescription("FID", [1845476211, 3305256354])], $sdp->getMedia()[0]->getSsrcGroup());

        // formats
        $this->assertEquals([96, 97, 98, 99, 100, 101, 102], $sdp->getMedia()[0]->getFmt());
        $this->assertEmpty($sdp->getMedia()[0]->getSctpmap());
        $this->assertNull($sdp->getMedia()[0]->getSctpPort());

        // ice
        $this->assertCount(2, $sdp->getMedia()[0]->getIceCandidates());
        $this->assertFalse($sdp->getMedia()[0]->isIceCandidatesComplete());
        $this->assertEquals("trickle", $sdp->getMedia()[0]->getIceOptions());
        $this->assertFalse($sdp->getMedia()[0]->getIce()->iceLite);
        $this->assertEquals("9KhP", $sdp->getMedia()[0]->getIce()->usernameFragment);
        $this->assertEquals("mlPea2xBCmFmNLfmy/jlqw1D", $sdp->getMedia()[0]->getIce()->password);

        // dtls
        $this->assertCount(1, $sdp->getMedia()[0]->getDtls()->fingerprints);
        $this->assertEquals("sha-256", $sdp->getMedia()[0]->getDtls()->fingerprints[0]->algorithm);
        $this->assertEquals(
            "30:4A:BF:65:23:D1:99:AB:AE:9F:FD:5D:B1:08:4F:09:7C:9F:F2:CC:50:16:13:81:1B:5D:DD:D0:98:45:81:1E",
            $sdp->getMedia()[0]->getDtls()->fingerprints[0]->value
        );
        $this->assertEquals(DtlsRole::Auto, $sdp->getMedia()[0]->getDtls()->role);

        $this->assertEqualsIgnoringCase($this->getSdpContent("video_chrome", true), trim((string)$sdp));
    }

    public function testVideoFirefox()
    {
        $sdp = SessionDescription::decode($this->getSdpContent("video_firefox"));

        $this->assertEquals([new GroupDescription("BUNDLE", ["sdparta_0"])], $sdp->getGroup());
        $this->assertEquals([new GroupDescription("WMS", ["*"])], $sdp->getMsidSemantic());

        $this->assertNull($sdp->getHost());
        $this->assertEquals("-", $sdp->getName());
        $this->assertEquals("mozilla...THIS_IS_SDPARTA-61.0 8964514366714082732 0 IN IP4 0.0.0.0", $sdp->getOrigin());
        $this->assertEquals("0 0", $sdp->getTime());
        $this->assertEquals(0, $sdp->getVersion());

        $this->assertCount(1, $sdp->getMedia());
        $this->assertEquals("video", $sdp->getMedia()[0]->getKind());
        $this->assertEquals("192.168.99.7", $sdp->getMedia()[0]->getHost());
        $this->assertEquals(42738, $sdp->getMedia()[0]->getPort());
        $this->assertEquals("UDP/TLS/RTP/SAVPF", $sdp->getMedia()[0]->getProfile());
        $this->assertEquals(SDPDirections::sendrecv, $sdp->getMedia()[0]->getDirection());
        $this->assertEquals("{38c9a1f0-d360-4ad8-afe3-4d7f6d4ae4e1} {d27161f3-ab5d-4aff-9dd8-4a24bfbe56d4}", $sdp->getMedia()[0]->getMsid());
        $this->assertEquals(
            [
                new RTCRtpCodecParameters("video/VP8", 90000, payloadType: 120,
                    rtcpFeedback: [
                        new RTCRtcpFeedback("nack"),
                        new RTCRtcpFeedback("nack", "pli"),
                        new RTCRtcpFeedback("ccm", "fir"),
                        new RTCRtcpFeedback("goog-remb"),
                    ],
                    parameters: ["max-fs" => 12288, "max-fr" => 60]
                ),
                new RTCRtpCodecParameters("video/VP9", 90000, payloadType: 121,
                    rtcpFeedback: [
                        new RTCRtcpFeedback("nack"),
                        new RTCRtcpFeedback("nack", "pli"),
                        new RTCRtcpFeedback("ccm", "fir"),
                        new RTCRtcpFeedback("goog-remb"),
                    ],
                    parameters: ["max-fs" => 12288, "max-fr" => 60]
                )
            ],
            $sdp->getMedia()[0]->getRtp()->codecs
        );
        $this->assertEquals(
            [
                new RTCRtpHeaderExtensionParameters(3, "urn:ietf:params:rtp-hdrext:sdes:mid"),
                new RTCRtpHeaderExtensionParameters(4, "http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time"),
                new RTCRtpHeaderExtensionParameters(5, "urn:ietf:params:rtp-hdrext:toffset")
            ],
            $sdp->getMedia()[0]->getRtp()->headerExtensions
        );
        $this->assertEquals("sdparta_0", $sdp->getMedia()[0]->getRtp()->muxId);
        $this->assertEquals("192.168.99.7", $sdp->getMedia()[0]->getRtcpHost());
        $this->assertEquals(52914, $sdp->getMedia()[0]->getRtcpPort());
        $this->assertTrue($sdp->getMedia()[0]->isRtcpMux());
        $this->assertEquals("{d27161f3-ab5d-4aff-9dd8-4a24bfbe56d4}", $sdp->webrtcTrackId($sdp->getMedia()[0]));

        // formats
        $this->assertEquals([120, 121], $sdp->getMedia()[0]->getFmt());
        $this->assertEmpty($sdp->getMedia()[0]->getSctpmap());
        $this->assertNull($sdp->getMedia()[0]->getSctpPort());

        // ice
        $this->assertCount(4, $sdp->getMedia()[0]->getIceCandidates());
        $this->assertTrue($sdp->getMedia()[0]->isIceCandidatesComplete());
        $this->assertEquals("trickle", $sdp->getMedia()[0]->getIceOptions());
        $this->assertFalse($sdp->getMedia()[0]->getIce()->iceLite);
        $this->assertEquals("1a0e6b24", $sdp->getMedia()[0]->getIce()->usernameFragment);
        $this->assertEquals("c43b0306087bb4de15f70e4405c4dafe", $sdp->getMedia()[0]->getIce()->password);

        // dtls
        $this->assertCount(1, $sdp->getMedia()[0]->getDtls()->fingerprints);
        $this->assertEquals("sha-256", $sdp->getMedia()[0]->getDtls()->fingerprints[0]->algorithm);
        $this->assertEquals(
            "AF:9E:29:99:AC:F6:F6:A2:86:A7:2E:A5:83:94:21:7F:F1:39:C5:E3:8F:E4:08:04:D9:D8:70:6D:6C:A2:A1:D5",
            $sdp->getMedia()[0]->getDtls()->fingerprints[0]->value
        );
        $this->assertEquals(DtlsRole::Auto, $sdp->getMedia()[0]->getDtls()->role);

        $this->assertEqualsIgnoringCase($this->getSdpContent("video_firefox", true), trim((string)$sdp));
    }

    public function testVideoSessionStarRtcpFb()
    {
        $sdp = SessionDescription::decode($this->getSdpContent("video_session_star_rtcp_fb"));

        $this->assertEquals(
            [
                new RTCRtpCodecParameters("video/VP8", 90000, payloadType: 120,
                    rtcpFeedback: [
                        new RTCRtcpFeedback("nack"),
                        new RTCRtcpFeedback("nack", "pli"),
                        new RTCRtcpFeedback("goog-remb"),
                    ],
                    parameters: ["max-fs" => 12288, "max-fr" => 60]
                ),
                new RTCRtpCodecParameters("video/VP9", 90000, payloadType: 121,
                    rtcpFeedback: [
                        new RTCRtcpFeedback("nack"),
                        new RTCRtcpFeedback("nack", "pli"),
                        new RTCRtcpFeedback("goog-remb"),
                    ],
                    parameters: ["max-fs" => 12288, "max-fr" => 60]
                )
            ],
            $sdp->getMedia()[0]->getRtp()->codecs
        );
    }


    public function testSafari()
    {
        $sdp = SessionDescription::decode($this->getSdpContent("safari"));

        $this->assertEquals([
            new GroupDescription("BUNDLE", ["audio", "video", "data"])
        ], $sdp->getGroup());

        $this->assertEquals([
            new GroupDescription("WMS", ["cb7e185b-6110-4f65-b027-ddb8b5fa78c7"])
        ], $sdp->getMsidSemantic());

        $this->assertNull($sdp->getHost());
        $this->assertEquals("-", $sdp->getName());
        $this->assertEquals("- 8148572839875102105 2 IN IP4 127.0.0.1", $sdp->getOrigin());
        $this->assertEquals("0 0", $sdp->getTime());
        $this->assertEquals(0, $sdp->getVersion());

        $this->assertCount(3, $sdp->getMedia());

        $this->assertEquals("audio", $sdp->getMedia()[0]->getKind());
        $this->assertEquals("1.2.3.4", $sdp->getMedia()[0]->getHost());
        $this->assertEquals(61015, $sdp->getMedia()[0]->getPort());
        $this->assertEquals("UDP/TLS/RTP/SAVPF", $sdp->getMedia()[0]->getProfile());
        $this->assertEquals(SDPDirections::sendrecv, $sdp->getMedia()[0]->getDirection());
        $this->assertNull($sdp->getMedia()[0]->getMsid());
        $this->assertNull($sdp->webrtcTrackId($sdp->getMedia()[0]));

        $this->assertEquals("video", $sdp->getMedia()[1]->getKind());
        $this->assertEquals("1.2.3.4", $sdp->getMedia()[1]->getHost());
        $this->assertEquals(51044, $sdp->getMedia()[1]->getPort());
        $this->assertEquals("UDP/TLS/RTP/SAVPF", $sdp->getMedia()[1]->getProfile());
        $this->assertEquals(SDPDirections::sendrecv, $sdp->getMedia()[1]->getDirection());
        $this->assertNull($sdp->getMedia()[1]->getMsid());
        $this->assertNull($sdp->webrtcTrackId($sdp->getMedia()[1]));

        $this->assertEquals("application", $sdp->getMedia()[2]->getKind());
        $this->assertEquals("1.2.3.4", $sdp->getMedia()[2]->getHost());
        $this->assertEquals(60277, $sdp->getMedia()[2]->getPort());
        $this->assertEquals("DTLS/SCTP", $sdp->getMedia()[2]->getProfile());
        $this->assertNull($sdp->getMedia()[2]->getDirection());
        $this->assertNull($sdp->getMedia()[2]->getMsid());
    }

    private function getSdpContent(string $filename, bool $creation = false): string
    {
        $creation = $creation ? "_creation" : "";
        return str_replace("\n", "\r\n", file_get_contents(__DIR__ . "/fixture/sdp_$filename$creation.txt"));
    }
}