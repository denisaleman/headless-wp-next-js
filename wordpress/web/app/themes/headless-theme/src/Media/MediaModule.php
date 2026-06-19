<?php
namespace Gafotas\HeadlessNewsTheme\Media;

class MediaModule {
	public function register() {
		( new Setup\ThumbnailSizesSetup() )->register();
	}
}
