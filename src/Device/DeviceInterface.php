<?php
declare(strict_types = 1);

namespace EmbeddedPhp\Display\Device;

use EmbeddedPhp\Display\Canvas\CanvasInterface;
use EmbeddedPhp\Display\Utils\Dimension;

interface DeviceInterface {
  /**
   * Enumerates the screen resolutions that the specific device supports, as a list of EmbeddedPhp\Display\Utils\Dimension objects.
   *
   * @return \EmbeddedPhp\Display\Utils\Dimension[]
   */
  public function supportedDimensions(): array;

  public function getDimension(): Dimension;

  /**
   * Invoked once as part of the devices display refresh. The four coordinates form a bounding box that determines the
   * area of the screen that will get get redrawn; thus the concrete implementations should send the correct command
   * sequence to the device to set that bounding box.
   */
  public function setPosition(int $top, int $right, int $bottom, int $left): void;

  public function display(CanvasInterface $canvas): void;
}
