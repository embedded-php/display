<?php
declare(strict_types = 1);

namespace EmbeddedPhp\Display\Oled\GrayScale;

use EmbeddedPhp\Core\Protocol\ProtocolInterface;
use EmbeddedPhp\Display\Capabilities\ColorMode;
use EmbeddedPhp\Display\Capabilities\Rotate;
use EmbeddedPhp\Display\FrameBuffer\FrameBufferInterface;
use EmbeddedPhp\Display\FrameBuffer\FullFrame;
use EmbeddedPhp\Display\Utils\Dimension;

class Ssd1322 extends AbstractGrayScaleDisplay {
  public const SETCONTRAST = 0xC1;

  private int $columnOffset;

  protected function sendCommand(int ...$commands): void {
    $params = [];
    if (count($commands) > 1) {
      $params   = array_slice($commands, 1);
      $commands = array_slice($commands, 0, 1);
    }

    $this->protocol->sendCommand(...$commands);
    if (count($params) > 0) {
      $this->protocol->sendData(...$params);
    }
  }

  protected function initSequence(): void {
    $this->sendCommand(0xFD, 0x12);       // Unlock IC
    $this->sendCommand(0xA4);             // Display off (all pixels off)
    $this->sendCommand(0xB3, 0xF2);       // Display divide clockratio/freq
    $this->sendCommand(0xCA, 0x3F);       // Set MUX ratio
    $this->sendCommand(0xA2, 0x00);       // Display offset
    $this->sendCommand(0xA1, 0x00);       // Display start Line
    $this->sendCommand(0xA0, 0x14, 0x11); // Set remap & dual COM Line
    $this->sendCommand(0xB5, 0x00);       // Set GPIO (disabled)
    $this->sendCommand(0xAB, 0x01);       // Function select (internal Vdd)
    $this->sendCommand(0xB4, 0xA0, 0xFD); // Display enhancement A (External VSL)
    $this->sendCommand(0xC7, 0x0F);       // Master contrast (reset)
    $this->sendCommand(0xB9);             // Set default grayscale table
    $this->sendCommand(0xB1, 0xF0);       // Phase length
    $this->sendCommand(0xD1, 0x82, 0x20); // Display enhancement B (reset)
    $this->sendCommand(0xBB, 0x0D);       // Pre-charge voltage
    $this->sendCommand(0xB6, 0x08);       // 2nd precharge period
    $this->sendCommand(0xBE, 0x00);       // Set VcomH
    $this->sendCommand(0xA6);             // Normal display (reset)
    $this->sendCommand(0xA9);             // Exit partial display
  }

  protected function clearRam(): void {
    $this->sendCommand(0x15, 0x00, 0x77); // reset column addr
    $this->sendCommand(0x75, 0x00, 0x7F); // reset row addr
    $this->sendCommand(0x5C);             // Enable MCU to write data into RAM

    for ($y = 0; $y < 256; $y++) {
      for ($x = 0; $x < 64; $x++) {
        $this->sendData(0x00);
      }
    }
  }

  public function __construct(
    ProtocolInterface $protocol,
    int $width = 256,
    int $height = 64,
    int $rotate = Rotate::NONE,
    string $colorMode = ColorMode::RGB,
    FrameBufferInterface $frameBuffer = null
  ) {
    $this->columnOffset = (int)((480 - $width) / 2);
    parent::__construct(
      $protocol,
      $width,
      $height,
      $rotate,
      $colorMode,
      $frameBuffer ?: new FullFrame(),
      0
    );
  }

  public function supportedDimensions(): array {
    return [
      new Dimension(256, 64),
      new Dimension(256, 48),
      new Dimension(256, 32),
      new Dimension(128, 64),
      new Dimension(128, 48),
      new Dimension(128, 32),
      new Dimension(64, 64),
      new Dimension(64, 48),
      new Dimension(64, 32)
    ];
  }

  public function setPosition(int $top, int $right, int $bottom, int $left): void {
    $width = $right - $left;
    $pixStart = $this->columnOffset + $left;
    $colAddrStart = $pixStart >> 2;
    $colAddrEnd = ($pixStart + $width >> 2) - 1;

    $this->sendCommand(0x15, $colAddrStart, $colAddrEnd); // set column addr
    $this->sendCommand(0x75, $top, $bottom - 1);          // set row addr
    $this->sendCommand(0x5C);                             // Enable MCU to write data into RAM
  }
}
