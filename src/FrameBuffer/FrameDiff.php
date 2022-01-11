<?php
declare(strict_types = 1);

namespace EmbeddedPhp\Display\FrameBuffer;

use EmbeddedPhp\Display\Canvas\CanvasInterface;
use EmbeddedPhp\Display\Utils\BoundingBox;
use Generator;

/**
 * Compare the current frame to the previous frame and tries to calculate the differences: this will either yield
 * nothing for a perfect match or else the iterator will yield one or more \EmbeddedPhp\Display\Utils\BoundingBox that describes
 * the areas that are different, up to the size of the entire image.
 * The image data for the difference is then be passed to a device for rendering just those small changes. This can
 * be very quick for small screen updates, but suffers from variable render times, depending on the changes applied.
 */
final class FrameDiff implements FrameBufferInterface {
  private ?CanvasInterface $previousCanvas = null;
  private int $segments;

  /**
   * @param int $segments The number of segments to partition the image into. This generally must be a square number
   * (1, 4, 9, 16, ...) and must be able to segment the image entirely in both width and height. i.e setting to 9 will
   * subdivide the image into a 3x3 grid when comparing to the previous image.
   */
  public function __construct(int $segments = 4) {
    $this->segments = $segments;
  }

  public function redraw(CanvasInterface $canvas): Generator {
    # Force a full redraw on the first frame
    if ($this->previousCanvas === null) {
      $dimension = $canvas->getDimension();

      yield new BoundingBox(0, $dimension->getWidth() - 1, $dimension->getHeight() - 1, 0);

      $this->previousCanvas = clone $canvas;

      return;
    }

    // image_width, image_height = image.size
    // segment_width = int(image_width / self.__n)
    // segment_height = int(image_height / self.__n)
    // assert segment_width * self.__n == image_width, "Total segment width does not cover full image width"
    // assert segment_height * self.__n == image_height, "Total segment height does not cover full image height"

    $changes = 0;
    // for y in range(0, image_height, segment_height) {
    //   for x in range(0, image_width, segment_width) {
    //     bounding_box = (x, y, x + segment_width, y + segment_height)
    //     prev_segment = self.prev_image.crop(bounding_box)
    //     curr_segment = image.crop(bounding_box)
    //     segment_bounding_box = ImageChops.difference(prev_segment, curr_segment).getbbox()
    //     if segment_bounding_box is not None {
    //       $changes++;
    //     }

    //     segment_bounding_box_from_origin = (
    //       x + segment_bounding_box[0],
    //       y + segment_bounding_box[1],
    //       x + segment_bounding_box[2],
    //       y + segment_bounding_box[3]
    //     )
    //   }
    // }

    // yield segment_bounding_box_from_origin

    if ($changes > 0) {
      $this->previousCanvas = clone $canvas;
    }
  }
}
