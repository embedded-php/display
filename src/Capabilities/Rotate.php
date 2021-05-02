<?php
declare(strict_types = 1);

namespace EmbeddedPhp\Display\Capabilities;

final class Rotate {
  /**
   * No rotation
   * @var int
   */
  public const NONE = 0;
  /**
   * Rotate 90 degrees clockwise
   * @var int
   */
  public const R90 = 1;
  /**
   * Rotate 180 degrees clockwise
   * @var int
   */
  public const R180 = 2;
  /**
   * Rotate 270 degrees clockwise
   * @var int
   */
  public const R270 = 3;

  public static function validOption(int $option): bool {
    return in_array($option, [self::NONE, self::R90, self::R180, self::R270]);
  }
}
