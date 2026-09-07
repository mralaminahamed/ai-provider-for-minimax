<?php
/**
 * Tests for TextToSpeechConversionModel.
 *
 * @package MiniMax\Tests\Models
 */

declare(strict_types=1);

namespace MiniMax\Tests\Models;

use Brain\Monkey;
use Brain\Monkey\Functions;
use MiniMax\MiniMaxAiProvider\Models\TextGenerationModel;
use MiniMax\MiniMaxAiProvider\Models\TextToSpeechConversionModel;
use MiniMax\MiniMaxAiProvider\Provider\Provider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use WordPress\AiClient\Common\Exception\InvalidArgumentException;
use WordPress\AiClient\Common\Exception\RuntimeException;
use WordPress\AiClient\Files\Enums\FileTypeEnum;
use WordPress\AiClient\Messages\DTO\Message;
use WordPress\AiClient\Messages\DTO\MessagePart;
use WordPress\AiClient\Messages\Enums\MessageRoleEnum;
use WordPress\AiClient\Providers\Http\DTO\Response;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\Models\DTO\ModelConfig;

/**
 * Exercises MiniMax's T2A endpoint, which is not OpenAI-compatible in any respect.
 *
 * @since 1.6.0
 */
class TextToSpeechConversionModelTest extends TestCase {

	private const MODEL_ID = 'speech-2.8-hd';

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'get_option' )->justReturn( array() );
		Functions\when( 'wp_json_encode' )->alias( 'json_encode' );
		Functions\when( 'apply_filters' )->returnArg( 2 );
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * The provider hands back a speech model for a speech id.
	 *
	 * @return void
	 */
	public function test_provider_builds_the_speech_model(): void {
		$this->assertInstanceOf( TextToSpeechConversionModel::class, Provider::model( self::MODEL_ID ) );
		$this->assertInstanceOf( TextGenerationModel::class, Provider::model( 'MiniMax-M3' ) );
	}

	/**
	 * The request goes to MiniMax's speech endpoint, absolutely addressed.
	 *
	 * @return void
	 */
	public function test_request_uri_is_absolute(): void {
		$method = new ReflectionMethod( TextToSpeechConversionModel::class, 'createRequest' );
		$method->setAccessible( true );

		$request = $method->invoke(
			Provider::model( self::MODEL_ID ),
			HttpMethodEnum::POST(),
			't2a_v2',
			array( 'Content-Type' => 'application/json' ),
			null
		);

		$this->assertSame( 'https://api.minimax.io/v1/t2a_v2', $request->getUri() );
		$this->assertTrue( $request->hasHeader( 'MiniMax-Provider' ) );
	}

	/**
	 * The request speaks MiniMax, not OpenAI.
	 *
	 * OpenAI takes `input`, `voice` and `response_format` at the top level.
	 * MiniMax takes `text` and nests the rest.
	 *
	 * @return void
	 */
	public function test_params_use_minimax_field_names(): void {
		$params = $this->params( 'Good morning.' );

		$this->assertSame( self::MODEL_ID, $params['model'] );
		$this->assertSame( 'Good morning.', $params['text'] );
		$this->assertSame( TextToSpeechConversionModel::DEFAULT_VOICE, $params['voice_setting']['voice_id'] );
		$this->assertSame( 'mp3', $params['audio_setting']['format'] );
		$this->assertSame( 'hex', $params['output_format'] );

		$this->assertArrayNotHasKey( 'input', $params );
		$this->assertArrayNotHasKey( 'voice', $params );
		$this->assertArrayNotHasKey( 'response_format', $params );
	}

	/**
	 * A caller's voice and format win over the defaults.
	 *
	 * @return void
	 */
	public function test_config_choices_reach_the_request(): void {
		$config = ModelConfig::fromArray(
			array(
				'outputSpeechVoice' => 'English_Graceful_Lady',
				'outputMimeType'    => 'audio/flac',
				'outputFileType'    => FileTypeEnum::remote()->value,
			)
		);

		$params = $this->params( 'Hello.', $config );

		$this->assertSame( 'English_Graceful_Lady', $params['voice_setting']['voice_id'] );
		$this->assertSame( 'flac', $params['audio_setting']['format'] );
		$this->assertSame( 'url', $params['output_format'] );
	}

	/**
	 * The 10,000-character ceiling is refused before the request, not after.
	 *
	 * MiniMax answers an over-long text with "invalid input parameters", which
	 * does not say what the limit is.
	 *
	 * @return void
	 */
	public function test_over_long_text_is_refused_locally(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'at most 10000 characters' );

		$this->params( str_repeat( 'a', TextToSpeechConversionModel::MAX_CHARACTERS + 1 ) );
	}

	/**
	 * An empty prompt is refused rather than sent.
	 *
	 * @return void
	 */
	public function test_empty_text_is_refused(): void {
		$this->expectException( InvalidArgumentException::class );

		$this->params( '   ' );
	}

	/**
	 * MiniMax returns audio as hex; the SDK wants base64.
	 *
	 * Getting this backwards would hand callers a file of unusable bytes rather
	 * than failing, so it is asserted on the decoded content.
	 *
	 * @return void
	 */
	public function test_hex_audio_is_decoded_to_a_data_uri(): void {
		$audio  = 'ID3 fake audio bytes';
		$result = $this->parse(
			array(
				'data'      => array( 'audio' => bin2hex( $audio ), 'status' => 2 ),
				'trace_id'  => 'trace-1',
				'extra_info' => array( 'audio_length' => 1234 ),
			)
		);

		// `toAudioFile()` is the SDK's own accessor: it finds the candidate only
		// if the part really is audio, so this asserts the MIME type as well.
		$file = $result->toAudioFile();

		$this->assertTrue( $file->isInline() );
		$this->assertSame( 'audio/mpeg', (string) $file->getMimeType() );
		$this->assertSame( $audio, base64_decode( $file->getBase64Data(), true ) );
		$this->assertSame( 'trace-1', $result->getId() );
	}

	/**
	 * A URL result is passed through rather than decoded.
	 *
	 * @return void
	 */
	public function test_url_audio_is_kept_as_a_url(): void {
		$result = $this->parse(
			array( 'data' => array( 'audio' => 'https://example.com/speech.mp3' ) )
		);

		$file = $result->toAudioFile();

		$this->assertTrue( $file->isRemote() );
		$this->assertSame( 'https://example.com/speech.mp3', $file->getUrl() );
	}

	/**
	 * A failure that arrives with HTTP 200 is still a failure.
	 *
	 * MiniMax reports API-level errors in `base_resp` while answering 200, so
	 * without this the caller gets an empty result instead of an error.
	 *
	 * @return void
	 */
	public function test_base_resp_error_is_thrown(): void {
		$method = new ReflectionMethod( TextToSpeechConversionModel::class, 'throw_if_minimax_reported_an_error' );
		$method->setAccessible( true );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'invalid params' );

		$method->invoke(
			Provider::model( self::MODEL_ID ),
			new Response(
				200,
				array( 'Content-Type' => array( 'application/json' ) ),
				(string) json_encode(
					array( 'base_resp' => array( 'status_code' => 2013, 'status_msg' => 'invalid params' ) )
				)
			)
		);
	}

	/**
	 * Invoke the protected params builder.
	 *
	 * @param string           $text   The text to speak.
	 * @param ModelConfig|null $config Optional model config.
	 * @return array<string, mixed>
	 */
	private function params( string $text, ?ModelConfig $config = null ): array {
		$model = Provider::model( self::MODEL_ID, $config );

		$method = new ReflectionMethod( TextToSpeechConversionModel::class, 'prepare_convert_text_to_speech_params' );
		$method->setAccessible( true );

		return $method->invoke(
			$model,
			array( new Message( MessageRoleEnum::user(), array( new MessagePart( $text ) ) ) )
		);
	}

	/**
	 * Invoke the protected response parser.
	 *
	 * @param array<string, mixed> $body Response body.
	 * @return \WordPress\AiClient\Results\DTO\GenerativeAiResult
	 */
	private function parse( array $body ) {
		$method = new ReflectionMethod( TextToSpeechConversionModel::class, 'parse_response_to_result' );
		$method->setAccessible( true );

		return $method->invoke(
			Provider::model( self::MODEL_ID ),
			new Response( 200, array( 'Content-Type' => array( 'application/json' ) ), (string) json_encode( $body ) ),
			'audio/mpeg'
		);
	}
}
