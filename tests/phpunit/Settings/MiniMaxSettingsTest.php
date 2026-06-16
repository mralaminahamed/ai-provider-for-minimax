<?php
/**
 * Tests for MiniMaxSettings.
 *
 * @package AlAminAhamed\MiniMaxAiProvider\Tests\Settings
 */

declare(strict_types=1);

namespace AlAminAhamed\MiniMaxAiProvider\Tests\Settings;

use AlAminAhamed\MiniMaxAiProvider\Settings\MiniMaxSettings;
use AlAminAhamed\MiniMaxAiProvider\Tests\AbstractSettingsTest;

/**
 * Class MiniMaxSettingsTest
 *
 * @since 1.0.0
 */
class MiniMaxSettingsTest extends AbstractSettingsTest {

	protected function getSettingsClass(): string {
		return MiniMaxSettings::class;
	}

	protected function getOptionKey(): string {
		return 'minimax_settings';
	}
}
