<?php
/**
 * Tests for TextGenerationModel.
 *
 * @package MiniMax\MiniMaxAiProvider\Tests\Models
 */

declare(strict_types=1);

namespace MiniMax\MiniMaxAiProvider\Tests\Models;

use MiniMax\MiniMaxAiProvider\Models\TextGenerationModel;
use MiniMax\MiniMaxAiProvider\Provider\Provider;
use MiniMax\MiniMaxAiProvider\Tests\AbstractTextGenerationModelTest;

/**
 * Class TextGenerationModelTest
 *
 * @since 1.0.0
 */
class TextGenerationModelTest extends AbstractTextGenerationModelTest {

	protected function getModelClass(): string {
		return TextGenerationModel::class;
	}

	protected function getProviderModelId(): string {
		return 'MiniMax-M3';
	}

	protected function getProviderName(): string {
		return 'MiniMax';
	}

	protected function getCustomHeaderName(): string {
		return 'MiniMax-Provider';
	}

	protected function createModel( string $modelId ): object {
		return Provider::model( $modelId );
	}

	/**
	 * Test all 8 fallback models can be instantiated.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function test_all_fallback_models_are_creatable(): void {
		$model_ids = array(
			'MiniMax-M3',
			'MiniMax-M2.7',
			'MiniMax-M2.7-highspeed',
			'MiniMax-M2.5',
			'MiniMax-M2.5-highspeed',
			'MiniMax-M2.1',
			'MiniMax-M2.1-highspeed',
			'MiniMax-M2',
		);

		foreach ( $model_ids as $model_id ) {
			$model = Provider::model( $model_id );
			$this->assertInstanceOf( TextGenerationModel::class, $model );
			$this->assertEquals( $model_id, $model->metadata()->getId() );
		}
	}
	/**
	 * Invoke the protected params builder with a mocked settings option.
	 *
	 * @param array<string, mixed> $settings Saved plugin settings.
	 * @return array<string, mixed>
	 */
	private function paramsWithSettings( array $settings ): array {
		\Brain\Monkey\Functions\when( 'get_option' )->alias(
			static function ( $key, $default = false ) use ( $settings ) {
				return 'minimax_settings' === $key ? $settings : $default;
			}
		);
		\Brain\Monkey\Functions\when( 'apply_filters' )->returnArg( 2 );

		$model  = $this->createModel( $this->getProviderModelId() );
		$method = new \ReflectionMethod( TextGenerationModel::class, 'prepareGenerateTextParams' );
		$method->setAccessible( true );

		return $method->invoke( $model, array( new \WordPress\AiClient\Messages\DTO\Message(
			\WordPress\AiClient\Messages\Enums\MessageRoleEnum::user(),
			array( new \WordPress\AiClient\Messages\DTO\MessagePart( 'Hello' ) )
		) ) );
	}

	/**
	 * Saved settings reach the request.
	 *
	 * Before 1.5.0 nothing read `minimax_settings`, so every value on the
	 * settings screen was decorative.
	 *
	 * @since 1.5.0
	 *
	 * @return void
	 */
	public function test_saved_settings_are_applied_to_params(): void {
		$params = $this->paramsWithSettings(
			array(
				'temperature' => 0.25,
				'max_tokens'  => 1234,
				'top_p'       => 0.5,
			)
		);

		$this->assertSame( 0.25, $params['temperature'] );
		$this->assertSame( 1234, $params['max_tokens'] );
		$this->assertSame( 0.5, $params['top_p'] );
	}

	/**
	 * Thinking is only sent when it is being switched off.
	 *
	 * `adaptive` is already the default for M3 and cannot be selected at all on
	 * the M2 series, so sending it is at best redundant.
	 *
	 * @since 1.5.0
	 *
	 * @return void
	 */
	public function test_thinking_is_sent_only_when_disabled(): void {
		$this->assertArrayNotHasKey(
			'thinking',
			$this->paramsWithSettings( array( 'thinking' => 'adaptive' ) )
		);

		$this->assertSame(
			array( 'type' => 'disabled' ),
			$this->paramsWithSettings( array( 'thinking' => 'disabled' ) )['thinking']
		);
	}

	/**
	 * Priority service tier is opt-in, because it costs 1.5x.
	 *
	 * @since 1.5.0
	 *
	 * @return void
	 */
	public function test_service_tier_is_sent_only_when_priority(): void {
		$this->assertArrayNotHasKey(
			'service_tier',
			$this->paramsWithSettings( array( 'service_tier' => 'standard' ) )
		);

		$this->assertSame(
			'priority',
			$this->paramsWithSettings( array( 'service_tier' => 'priority' ) )['service_tier']
		);
	}

}
