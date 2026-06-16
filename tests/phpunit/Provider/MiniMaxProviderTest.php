<?php
/**
 * Tests for MiniMaxProvider.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Tests\Provider
 */

declare(strict_types=1);

namespace AlAminAhamed\MiniMaxAiProvider\Tests\Provider;

use AlAminAhamed\MiniMaxAiProvider\Provider\MiniMaxProvider;
use AlAminAhamed\MiniMaxAiProvider\Tests\AbstractProviderTest;

/**
 * Class MiniMaxProviderTest
 *
 * @since 1.0.0
 */
class MiniMaxProviderTest extends AbstractProviderTest {

	protected function getProviderClass(): string {
		return MiniMaxProvider::class;
	}

	protected function getExpectedBaseUrl(): string {
		return 'https://api.minimax.io/v1';
	}

	protected function getExpectedProviderId(): string {
		return 'minimax';
	}

	protected function getExpectedProviderName(): string {
		return 'MiniMax';
	}
}
