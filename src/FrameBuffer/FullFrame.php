<?php
declare(strict_types = 1);

namespace EmbeddedPhp\Display\FrameBuffer;

use EmbeddedPhp\Display\Canvas\CanvasInterface;
use EmbeddedPhp\Display\Utils\BoundingBox;
use Generator;

/**
 * Always renders the full frame every time. This is slower than \EmbeddedPhp\Display\FrameBuffer\FrameDiff as there are
 * generally more pixels to update on every render, but it has a more consistent render time.
 * Not all display drivers may be able to use the differencing framebuffer, so this is provided as a drop-in
 * replacement.
 */
final class FullFrame implements FrameBufferInterface {
  public function redraw(CanvasInterface $canvas): Generator {
    $dimension = $canvas->getDimension();

    yield new BoundingBox(0, $dimension->getWidth(), $dimension->getHeight(), 0);
  }
}
