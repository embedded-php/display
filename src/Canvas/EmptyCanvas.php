<?php
declare(strict_types = 1);

namespace EmbeddedPhp\Display\Canvas;

use EmbeddedPhp\Display\Utils\Dimension;

final class EmptyCanvas implements CanvasInterface {
  private Dimension $dimension;

  public function __construct(Dimension $dimension) {
    $this->dimension = $dimension;
  }

  public function rotate(float $angle): CanvasInterface {
    return $this;
  }

  public function cropSection(int $top, int $right, int $bottom, int $left): EmptyCanvas {
    return new EmptyCanvas(
      new Dimension(
        (int)abs($right - $left),
        (int)abs($top - $bottom)
      )
    );
  }

  public function getDimension(): Dimension {
    return $this->dimension;
  }

  public function getData(): array {
    return array_fill(0, ($this->dimension->getWidth() * $this->dimension->getHeight()), 0);
  }
}
