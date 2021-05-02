<?php
declare(strict_types = 1);

namespace EmbeddedPhp\Display\Utils;

use EmbeddedPhp\Display\Utils\Dimension;

final class Dimension {
  private int $width;
  private int $height;

  public function __construct(int $width, int $height) {
    $this->width  = $width;
    $this->height = $height;
  }

  public function getWidth(): int {
    return $this->width;
  }

  public function getHeight(): int {
    return $this->height;
  }

  /**
   * @param \EmbeddedPhp\Display\Utils\Dimension[] $supportedDimensions
   */
  public function isSupported(array $supportedDimensions): bool {
    $supportedDimensions = array_map(
      function (Dimension $dimension): array {
        return $dimension->toArray();
      },
      $supportedDimensions
    );

    return in_array([$this->width, $this->height], $supportedDimensions);
  }

  /**
   * @return int[]
   */
  public function toArray(): array {
    return [
      $this->width,
      $this->height
    ];
  }
}
