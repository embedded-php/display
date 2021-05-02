<?php
declare(strict_types = 1);

namespace EmbeddedPhp\Display\FrameBuffer;

use EmbeddedPhp\Display\Canvas\CanvasInterface;
use Generator;

interface FrameBufferInterface {
  /**
   * Return the boundingbox that must be updated.
   *
   * @param \EmbeddedPhp\Display\Canvas\CanvasInterface $canvas
   *
   * @return Generator<\EmbeddedPhp\Display\Utils\BoundingBox>
   */
  public function redraw(CanvasInterface $canvas): Generator;
}
