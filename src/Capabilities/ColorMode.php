<?php
declare(strict_types = 1);

namespace EmbeddedPhp\Display\Capabilities;

final class ColorMode {
  /**
   * 1-bit monochrome
   * @var string
   */
  public const MONO = '1';
  /**
   * 24-bit RGB
   * @var string
   */
  public const RGB = 'RGB';
  /**
   * 24-bit RGBA
   * @var string
   */
  public const RGBA = 'RGBA';

  public static function validOption(string $option): bool {
    return in_array($option, [self::MONO, self::RGB, self::RGBA]);
  }
}
