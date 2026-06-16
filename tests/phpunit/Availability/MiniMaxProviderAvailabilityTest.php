<?php
/**
 * Tests for MiniMaxProviderAvailability.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Tests\Availability
 */

declare(strict_types=1);

namespace AlAminAhamed\MiniMaxAiProvider\Tests\Availability;

use AlAminAhamed\MiniMaxAiProvider\Availability\MiniMaxProviderAvailability;
use AlAminAhamed\MiniMaxAiProvider\Tests\AbstractProviderAvailabilityTest;

/**
 * Class MiniMaxProviderAvailabilityTest
 *
 * @since 1.3.2
 */
class MiniMaxProviderAvailabilityTest extends AbstractProviderAvailabilityTest {

	protected function getAvailabilityClass(): string {
		return MiniMaxProviderAvailability::class;
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
