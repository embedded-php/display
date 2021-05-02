<?php
declare(strict_types = 1);

namespace EmbeddedPhp\Display\Utils;

final class BoundingBox {
  private int $top;
  private int $right;
  private int $bottom;
  private int $left;

  public function __construct(int $top, int $right, int $bottom, int $left) {
    $this->top    = $top;
    $this->right  = $right;
    $this->bottom = $bottom;
    $this->left   = $left;
  }

  public function getTop(): int {
    return $this->top;
  }

  public function getRight(): int {
    return $this->right;
  }

  public function getBottom(): int {
    return $this->bottom;
  }

  public function getLeft(): int {
    return $this->left;
  }

  public function getWidth(): int {
    return $this->right - $this->left;
  }

  public function getHeight(): int {
    return $this->bottom - $this->top;
  }

  /**
   * @return int[]
   */
  public function toArray(): array {
    return [
      $this->top,
      $this->right,
      $this->bottom,
      $this->left
    ];
  }
}
