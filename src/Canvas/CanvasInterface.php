<?php
declare(strict_types = 1);

namespace EmbeddedPhp\Display\Canvas;

use EmbeddedPhp\Display\Utils\Dimension;

interface CanvasInterface {
  public function rotate(float $angle): CanvasInterface;

  public function cropSection(int $top, int $right, int $bottom, int $left): CanvasInterface;

  public function getDimension(): Dimension;

  /**
   * @return int[]
   */
  public function getData(): array;
}
