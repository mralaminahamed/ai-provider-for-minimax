<?php
/**
 * Tests for Settings.
 *
 * @package MiniMax\MiniMaxAiProvider\Tests\Settings
 */

declare(strict_types=1);

namespace MiniMax\MiniMaxAiProvider\Tests\Settings;

use MiniMax\MiniMaxAiProvider\Settings\Settings;
use MiniMax\MiniMaxAiProvider\Tests\AbstractSettingsTest;

/**
 * Class SettingsTest
 *
 * @since 1.0.0
 */
class SettingsTest extends AbstractSettingsTest {

	protected function getSettingsClass(): string {
		return Settings::class;
	}

	protected function getOptionKey(): string {
		return 'minimax_settings';
	}
}
