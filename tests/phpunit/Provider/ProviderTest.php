<?php
/**
 * Tests for Provider.
 *
 * @package MiniMax\MiniMaxAiProvider\Tests\Provider
 */

declare(strict_types=1);

namespace MiniMax\MiniMaxAiProvider\Tests\Provider;

use MiniMax\MiniMaxAiProvider\Provider\Provider;
use MiniMax\MiniMaxAiProvider\Tests\AbstractProviderTest;

/**
 * Class ProviderTest
 *
 * @since 1.0.0
 */
class ProviderTest extends AbstractProviderTest {

	protected function getProviderClass(): string {
		return Provider::class;
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
