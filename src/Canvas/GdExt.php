<?php
declare(strict_types = 1);

namespace EmbeddedPhp\Display\Canvas;

use GdImage;
use EmbeddedPhp\Display\Utils\Dimension;
use RuntimeException;

final class GdExt implements CanvasInterface {
  private GdImage $gdImage;
  private Dimension $dimension;

  public function __construct(Dimension $dimension, GdImage $gdImage = null) {
    if (! extension_loaded('gd')) {
      throw new RuntimeException('The "gd" extension must be loaded to use EmbeddedPhp\Display\Canvas\GdExt');
    }

    if ($gdImage === null) {
      $gdImage = imagecreatetruecolor($dimension->getWidth(), $dimension->getHeight());
      if ($gdImage === false) {
        throw new RuntimeException('Failed to create gd image');
      }
    }

    $this->gdImage   = $gdImage;
    $this->dimension = $dimension;
  }

  public function __destruct() {
    imagedestroy($this->gdImage);
  }

  public function rotate(float $angle): CanvasInterface {
    $rotate = imagerotate($this->gdImage, $angle, 0);
    if ($rotate === false) {
      throw new RuntimeException('Failed to rotate gd image');
    }

    return new GdExt(
      $this->dimension,
      $rotate
    );
  }

  public function cropSection(int $top, int $right, int $bottom, int $left): GdExt {
    $section = imagecrop(
      $this->gdImage,
      [
        'x'      => $left,
        'y'      => $top,
        'width'  => (int)abs($right - $left),
        'height' => (int)abs($top - $bottom)
      ]
    );
    if ($section === false) {
      throw new RuntimeException('Failed to crop gd image');
    }

    return new GdExt(
      new Dimension(
        (int)abs($right - $left),
        (int)abs($top - $bottom)
      ),
      $section
    );
  }

  public function getGdImage(): GdImage {
    return $this->gdImage;
  }

  public function getDimension(): Dimension {
    return $this->dimension;
  }

  public function getData(): array {
    $w = $this->dimension->getWidth();
    $h = $this->dimension->getHeight();

    $buffer = array_fill(0, ($w * $h), 0);
    for ($x = 0; $x < $w; $x++) {
      for ($y = 0; $y < $h; $y++) {
        $idx = ($w * $y) + $x;
        $buffer[$idx] = imagecolorat($this->gdImage, $x, $y);
      }
    }

    return $buffer;
  }
}
