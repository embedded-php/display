<?php
declare(strict_types = 1);

namespace EmbeddedPhp\Display\Device;

use InvalidArgumentException;
use EmbeddedPhp\Display\Canvas\CanvasInterface;
use EmbeddedPhp\Display\Canvas\EmptyCanvas;
use EmbeddedPhp\Display\Capabilities\ColorMode;
use EmbeddedPhp\Display\Capabilities\Rotate;
use EmbeddedPhp\Core\Protocol\ProtocolInterface;
use EmbeddedPhp\Display\Utils\BoundingBox;
use EmbeddedPhp\Display\Utils\Dimension;
use Webmozart\Assert\Assert;

/**
 * @link https://github.com/rm-hull/luma.core/blob/master/luma/core/device.py
 * @link https://github.com/rm-hull/luma.core/blob/master/luma/core/const.py
 */
abstract class AbstractDevice implements DeviceInterface {
  protected ProtocolInterface $protocol;
  protected int $originalWidth;
  protected int $originalHeight;
  protected int $width;
  protected int $height;
  protected int $rotate;
  protected string $colorMode;
  protected Dimension $size;
  protected BoundingBox $boundingBox;

  public const DISPLAYOFF          = 0xAE;
  public const DISPLAYON           = 0xAF;
  public const SETCONTRAST         = 0x81;

  abstract public function supportedDimensions(): array;
  abstract public function setPosition(int $top, int $right, int $bottom, int $left): void;
  abstract public function display(CanvasInterface $canvas): void;

  /**
   * Provides a preprocessing facility (which may be overridden) whereby the supplied image is rotated according to
   * the device's rotate capability. If this method is overridden, it is important to call the parent method.
   *
   * @param  \EmbeddedPhp\Display\Canvas\CanvasInterface $canvas
   *
   * @return \EmbeddedPhp\Display\Canvas\CanvasInterface
   */
  protected function preprocess(CanvasInterface $canvas): CanvasInterface {
    if ($this->rotate === 0) {
      return $canvas;
    }

    $angle = $this->rotate * -90;

    return $canvas->rotate($angle)->cropSection(0, 0, $this->originalWidth, $this->originalHeight);
  }

  /**
   * Sends a command or sequence of commands through to the delegated serial interface.
   */
  protected function sendCommand(int ...$commands): void {
    $this->protocol->sendCommand(...$commands);
  }

  /**
   * Sends a data byte or sequence of data bytes through to the delegated serial interface.
   */
  protected function sendData(int ...$data): void {
    $this->protocol->sendData(...$data);
  }

  public function __construct(
    ProtocolInterface $protocol,
    int $width,
    int $height,
    int $rotate,
    string $colorMode
  ) {
    if (Rotate::validOption($rotate) === false) {
      throw new InvalidArgumentException(
        sprintf(
          'Unsupported rotate: %d',
          $rotate
        )
      );
    }

    if (ColorMode::validOption($colorMode) === false) {
      throw new InvalidArgumentException(
        sprintf(
          'Unsupported color mode: %s',
          $colorMode
        )
      );
    }

    if ((new Dimension($width, $height))->isSupported($this->supportedDimensions()) === false) {
      throw new InvalidArgumentException(
        sprintf(
          'Unsupported display mode: %d x %d',
          $width,
          $height
        )
      );
    }

    $this->protocol = $protocol;
    $this->originalWidth = $width;
    $this->originalHeight = $height;
    $this->width = match ($rotate % 2) {
      0 => $width,
      1 => $height
    };
    $this->height = match ($rotate % 2) {
      0 => $height,
      1 => $width
    };
    $this->size = new Dimension($this->width, $this->height);
    $this->boundingBox = new BoundingBox(0, 0, $this->width - 1, $this->height - 1);
    $this->rotate = $rotate;
    $this->colorMode = $colorMode;
  }

  public function __destruct() {
    $this->cleanup();
  }

  public function getDimension(): Dimension {
    return $this->size;
  }

  /**
   * Initializes the device memory with an empty (blank) image.
   */
  public function clear(): void {
    $this->display(new EmptyCanvas($this->size));
  }

  /**
   * Attempt to switch the device off or put into low power mode (this helps prolong the life of the device), clear
   * the screen and close resources associated with the underlying serial interface.
   */
  public function cleanup(): void {
    $this->displayOff();
    $this->clear();
    $this->protocol->cleanup();
  }

  /**
   * Switches the display contrast to the desired level, in the range 0-255.
   * Note that setting the level to a low (or zero) value will not necessarily dim the display to nearly off.
   * In other words, this method is **NOT** suitable for fade-in/out animation.
   *
   * @param int $level Desired contrast level in the range of 0-255.
   */
  public function setContrast(int $level): void {
    Assert::greaterThanEq(
      $level,
      0,
      '$level must be greater than or equal to 0, got %s'
    );

    Assert::lessThanEq(
      $level,
      255,
      '$level must be less than or equal to 255, got %s'
    );

    $this->sendCommand(self::SETCONTRAST, $level);
  }

  /**
   * Sets the display mode ON, waking the device out of a prior low-power sleep mode.
   */
  public function displayOn(): void {
    $this->sendCommand(self::DISPLAYON);
  }

  /**
   * Switches the display mode OFF, putting the device in low-power sleep mode.
   */
  public function displayOff(): void {
    $this->sendCommand(self::DISPLAYOFF);
  }
}
