<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Jetpack;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Jetpack;
use Brain\Monkey\Functions;

class Test_AddJetpackSitemap extends TestCase
{
	protected $option;
	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->option = Mockery::mock(Options_Data::class);
		$this->subscriber = new Jetpack($this->option);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->option->expects()->get('jetpack_xml_sitemap', false)->andReturn($config['is_enabled']);
		if($config['is_enabled']) {
			Functions\expect('jetpack_sitemap_uri')->andReturn($config['jetpack_sitemap']);
		}
		$this->assertSame($expected, $this->subscriber->add_jetpack_sitemap($config['sitemaps']));
	}
}
