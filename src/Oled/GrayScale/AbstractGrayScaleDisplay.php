<?php
declare(strict_types = 1);

namespace EmbeddedPhp\Display\Oled\GrayScale;

use EmbeddedPhp\Core\Protocol\ProtocolInterface;
use EmbeddedPhp\Display\Canvas\CanvasInterface;
use EmbeddedPhp\Display\Device\AbstractDevice;
use EmbeddedPhp\Display\FrameBuffer\FrameBufferInterface;
use EmbeddedPhp\Display\Utils\BoundingBox;

/**
 * @link https://github.com/rm-hull/luma.oled/blob/master/luma/oled/device/grayscale.py
 */
abstract class AbstractGrayScaleDisplay extends AbstractDevice {
  protected FrameBufferInterface $frameBuffer;
  protected string $renderingMethod;
  protected int $nibbleOrder;

  /**
   * Concrete implementations should call the initiation sequence for the specific device. Invoked from the
   * constructor, but no assumptions should be made about what has been initialized so far.
   */
  abstract protected function initSequence(): void;

  /**
   * Concrete implementations should call the clear ram for the specific device. Invoked from the
   * constructor, assuming that the device has been initialized.
   */
  abstract protected function clearRam(): void;

  abstract public function supportedDimensions(): array;
  abstract public function setPosition(int $top, int $right, int $bottom, int $left): void;

  /**
   * @param int[] $buffer
   * @param int[] $pixelData
   */
  protected function renderMono(array &$buffer, array $pixelData): void {
    $i = 0;
    foreach ($pixelData as $pixel) {
      if ($pixel > 0) {
        $idx = (int)($i / 2);
        if ($i % 2 === $this->nibbleOrder) {
          $buffer[$idx] |= 0xF0;
          $i++;

          continue;
        }

        $buffer[$idx] |= 0x0F;
        $i++;
      }
    }
  }

  /**
   * @param int[] $buffer
   * @param array<array<int>> $pixelData
   */
  protected function renderGrayscale(array &$buffer, array $pixelData): void {
    $i = 0;
    foreach ($pixelData as $pixel) {
      // decomposition into rgb components
      $r = ($pixel >> 16) & 0xFF;
      $g = ($pixel >> 8) & 0xFF;
      $b = $pixel & 0xFF;
      // RGB to Grayscale luma calculation into 4-bits
      $gray = (($r * 306) + ($g * 601) + ($b * 117)) >> 14;

      if ($gray > 0) {
        $idx = (int)($i / 2);
        if ($i % 2 === $this->nibbleOrder) {
          $buffer[$idx] |= ($gray << 4);
          $i++;

          continue;
        }

        $buffer[$idx] |= $gray;
        $i++;
      }
    }
  }

  /**
   * Realign the left and right edges of the bounding box such that they are inflated to align modulo 4.
   * This method is optional, and used mainly to accommodate devices with COM/SEG GDDRAM structures that store pixels
   * in 4-bit nibbles.
   *
   * @param \EmbeddedPhp\Display\Utils\BoundingBox $boundingBox
   *
   * @return \EmbeddedPhp\Display\Utils\BoundingBox
   */
  protected function inflateBoundingBox(BoundingBox $boundingBox): BoundingBox {
    [$top, $right, $bottom, $left] = $boundingBox->toArray();

    return new BoundingBox(
      $top,
      ($right % 4 === 0 ? $right : ($right & 0xFFFC) + 0x04),
      $bottom,
      $left & 0xFFFC
    );
  }

  public function __construct(
    ProtocolInterface $protocol,
    int $width,
    int $height,
    int $rotate,
    string $colorMode,
    FrameBufferInterface $frameBuffer,
    int $nibbleOrder
  ) {
    parent::__construct($protocol, $width, $height, $rotate, $colorMode);

    $this->renderingMethod = 'renderGrayscale';
    if ($colorMode === '1') {
      $this->renderingMethod = 'renderMono';
    }

    $this->nibbleOrder = $nibbleOrder;
    $this->frameBuffer = $frameBuffer;

    $this->initSequence();
    $this->displayOn();
    $this->setContrast(0x7F);
    $this->clearRam();
  }

  public function cleanup(): void {
    parent::cleanup();
    $this->clearRam();
  }

  /**
   * Takes a 1-bit monochrome or 24-bit RGB image and renders it to the grayscale OLED display. RGB pixels are
   * converted to 4-bit grayscale values using a simplified Luma calculation.
   */
  public function display(CanvasInterface $canvas): void {
    $canvas = $this->preprocess($canvas);
    foreach ($this->frameBuffer->redraw($canvas) as $boundingBox) {
      $boundingBox = $this->inflateBoundingBox($boundingBox);

      $section = $canvas->cropSection(
        $boundingBox->getTop(),
        $boundingBox->getRight(),
        $boundingBox->getBottom(),
        $boundingBox->getLeft()
      );

      $buffer = array_fill(0, ($boundingBox->getWidth() * $boundingBox->getHeight()) >> 1, 0);
      $this->setPosition(
        $boundingBox->getTop(),
        $boundingBox->getRight(),
        $boundingBox->getBottom(),
        $boundingBox->getLeft()
      );
      call_user_func_array([$this, $this->renderingMethod], [&$buffer, $section->getData()]);
      $this->sendData(...$buffer);
    }
  }
}
