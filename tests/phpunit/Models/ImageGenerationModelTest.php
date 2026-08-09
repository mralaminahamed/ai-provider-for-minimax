<?php
/**
 * Tests for ImageGenerationModel.
 *
 * @package MiniMax\Tests\Models
 */

declare(strict_types=1);

namespace MiniMax\Tests\Models;

use Brain\Monkey;
use Brain\Monkey\Functions;
use MiniMax\MiniMaxAiProvider\Metadata\ModelMetadataDirectory;
use MiniMax\MiniMaxAiProvider\Models\ImageGenerationModel;
use MiniMax\MiniMaxAiProvider\Models\TextGenerationModel;
use MiniMax\MiniMaxAiProvider\Provider\Provider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use WordPress\AiClient\Providers\Http\DTO\Response;

/**
 * Exercises MiniMax's image endpoint, which is not OpenAI-compatible.
 *
 * @since 1.5.0
 */
class ImageGenerationModelTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'get_option' )->justReturn( array() );
		Functions\when( 'wp_json_encode' )->alias( 'json_encode' );
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * `image-01` is offered, and is not described as a chat model.
	 *
	 * Declaring `textGeneration` on it would route prompts to an endpoint that
	 * answers with a picture.
	 *
	 * @return void
	 */
	public function test_image_model_declares_only_image_generation(): void {
		$directory = new ModelMetadataDirectory();
		$model     = $directory->getModelMetadata( ImageGenerationModel::MODEL_ID );

		$has_image = false;
		$has_text  = false;

		foreach ( $model->getSupportedCapabilities() as $cap ) {
			$has_image = $has_image || $cap->isImageGeneration();
			$has_text  = $has_text || $cap->isTextGeneration();
		}

		$this->assertTrue( $has_image, 'image-01 does not declare imageGeneration' );
		$this->assertFalse( $has_text, 'image-01 must not declare textGeneration' );
	}

	/**
	 * The provider hands back the right model class for each capability.
	 *
	 * @return void
	 */
	public function test_provider_builds_the_right_model_class(): void {
		$this->assertInstanceOf(
			ImageGenerationModel::class,
			Provider::model( ImageGenerationModel::MODEL_ID )
		);

		$this->assertInstanceOf(
			TextGenerationModel::class,
			Provider::model( 'MiniMax-M3' )
		);
	}

	/**
	 * Invoke the protected params builder.
	 *
	 * @return array<string, mixed>
	 */
	private function params(): array {
		Functions\when( 'apply_filters' )->returnArg( 2 );

		$model  = Provider::model( ImageGenerationModel::MODEL_ID );
		$method = new ReflectionMethod( ImageGenerationModel::class, 'prepareGenerateImageParams' );
		$method->setAccessible( true );

		return $method->invoke(
			$model,
			array(
				new \WordPress\AiClient\Messages\DTO\Message(
					\WordPress\AiClient\Messages\Enums\MessageRoleEnum::user(),
					array( new \WordPress\AiClient\Messages\DTO\MessagePart( 'A red bicycle' ) )
				),
			)
		);
	}

	/**
	 * The request speaks MiniMax, not OpenAI.
	 *
	 * `size` and `b64_json` are OpenAI's spellings; MiniMax rejects both.
	 *
	 * @return void
	 */
	public function test_params_use_minimax_spelling(): void {
		$params = $this->params();

		$this->assertSame( ImageGenerationModel::MODEL_ID, $params['model'] );
		$this->assertSame( 'A red bicycle', $params['prompt'] );
		$this->assertSame( 'base64', $params['response_format'] );
		$this->assertArrayNotHasKey( 'size', $params );
		$this->assertArrayNotHasKey( 'output_format', $params );
	}

	/**
	 * Orientation and explicit ratio both resolve to an aspect ratio.
	 *
	 * @return void
	 */
	public function test_aspect_ratio_resolution(): void {
		$model  = Provider::model( ImageGenerationModel::MODEL_ID );
		$method = new ReflectionMethod( ImageGenerationModel::class, 'prepare_aspect_ratio' );
		$method->setAccessible( true );

		$landscape = \WordPress\AiClient\Files\Enums\MediaOrientationEnum::landscape();
		$portrait  = \WordPress\AiClient\Files\Enums\MediaOrientationEnum::portrait();

		// An explicit ratio wins over orientation.
		$this->assertSame( '4:3', $method->invoke( $model, $landscape, '4:3' ) );

		$this->assertSame( '16:9', $method->invoke( $model, $landscape, null ) );
		$this->assertSame( '9:16', $method->invoke( $model, $portrait, null ) );
		$this->assertSame( '1:1', $method->invoke( $model, null, null ) );
	}

	/**
	 * Build a Response the way the transporter would.
	 *
	 * @param array<string, mixed> $body Response body.
	 * @return Response
	 */
	private function response( array $body ): Response {
		return new Response( 200, array( 'Content-Type' => array( 'application/json' ) ), (string) json_encode( $body ) );
	}

	/**
	 * Invoke the protected normaliser.
	 *
	 * @param array<string, mixed> $body Response body.
	 * @return array<string, mixed>|null
	 */
	private function normalize( array $body ): ?array {
		$model  = Provider::model( ImageGenerationModel::MODEL_ID );
		$method = new ReflectionMethod( ImageGenerationModel::class, 'normalize_response' );
		$method->setAccessible( true );

		return $method->invoke( $model, $this->response( $body ) )->getData();
	}

	/**
	 * MiniMax's object-of-lists becomes OpenAI's list-of-objects.
	 *
	 * This is the shape difference the parent parser cannot see past.
	 *
	 * @return void
	 */
	public function test_base64_response_is_reshaped(): void {
		$data = $this->normalize(
			array(
				'id'   => 'img-1',
				'data' => array( 'image_base64' => array( 'AAAA', 'BBBB' ) ),
			)
		);

		$this->assertSame(
			array(
				array( 'b64_json' => 'AAAA' ),
				array( 'b64_json' => 'BBBB' ),
			),
			$data['data']
		);
		$this->assertSame( 'img-1', $data['id'] );
	}

	/**
	 * The URL variant is reshaped the same way.
	 *
	 * @return void
	 */
	public function test_url_response_is_reshaped(): void {
		$data = $this->normalize(
			array( 'data' => array( 'image_urls' => array( 'https://example.test/a.png' ) ) )
		);

		$this->assertSame( array( array( 'url' => 'https://example.test/a.png' ) ), $data['data'] );
	}

	/**
	 * A response already in the expected shape is left alone.
	 *
	 * Cheap insurance against MiniMax converging on OpenAI's format.
	 *
	 * @return void
	 */
	public function test_openai_shaped_response_is_untouched(): void {
		$body = array( 'data' => array( array( 'b64_json' => 'AAAA' ) ) );

		$this->assertSame( $body['data'], $this->normalize( $body )['data'] );
	}

	/**
	 * A failure reported with a 200 is still a failure.
	 *
	 * MiniMax puts API-level errors in `base_resp` and answers 200 anyway, so
	 * the HTTP status check passes and the caller would otherwise be handed an
	 * empty result instead of an error.
	 *
	 * @return void
	 */
	public function test_error_reported_with_http_200_still_throws(): void {
		$model  = Provider::model( ImageGenerationModel::MODEL_ID );
		$method = new ReflectionMethod( ImageGenerationModel::class, 'throwIfNotSuccessful' );
		$method->setAccessible( true );

		$this->expectException( \WordPress\AiClient\Common\Exception\RuntimeException::class );
		$this->expectExceptionMessageMatches( '/1008/' );

		$method->invoke(
			$model,
			$this->response(
				array(
					'base_resp' => array(
						'status_code' => 1008,
						'status_msg'  => 'insufficient balance',
					),
				)
			)
		);
	}

	/**
	 * A successful `base_resp` is not mistaken for an error.
	 *
	 * @return void
	 */
	public function test_zero_status_code_does_not_throw(): void {
		$model  = Provider::model( ImageGenerationModel::MODEL_ID );
		$method = new ReflectionMethod( ImageGenerationModel::class, 'throwIfNotSuccessful' );
		$method->setAccessible( true );

		$method->invoke(
			$model,
			$this->response(
				array(
					'base_resp' => array(
						'status_code' => 0,
						'status_msg'  => 'success',
					),
					'data'      => array( 'image_base64' => array( 'AAAA' ) ),
				)
			)
		);

		$this->addToAssertionCount( 1 );
	}
}
