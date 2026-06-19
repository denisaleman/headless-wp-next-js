<?php
namespace Gafotas\HeadlessNewsTheme\Media;

use Gafotas\HeadlessNewsTheme\Shared\Contracts\ModuleInterface;

class MediaModule implements ModuleInterface {
	public function register(): void {
		( new Setup\ThumbnailSizesSetup() )->register();
	}
}
