<?php
/**
 * Tests for ProviderAvailability.
 *
 * @package MiniMax\MiniMaxAiProvider\Tests\Availability
 */

declare(strict_types=1);

namespace MiniMax\MiniMaxAiProvider\Tests\Availability;

use MiniMax\MiniMaxAiProvider\Availability\ProviderAvailability;
use MiniMax\MiniMaxAiProvider\Tests\AbstractProviderAvailabilityTest;

/**
 * Class ProviderAvailabilityTest
 *
 * @since 1.3.2
 */
class ProviderAvailabilityTest extends AbstractProviderAvailabilityTest {

	protected function getAvailabilityClass(): string {
		return ProviderAvailability::class;
	}

	protected function getEnvVarName(): string {
		return 'MINIMAX_API_KEY';
	}

	protected function getConnectorsOptionKey(): string {
		return 'connectors_ai_minimax_api_key';
	}

	protected function getLegacyCredentialsKey(): string {
		return 'minimax';
	}
}
