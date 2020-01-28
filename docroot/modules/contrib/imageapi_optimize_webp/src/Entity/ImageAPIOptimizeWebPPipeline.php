<?php

namespace Drupal\imageapi_optimize_webp\Entity;

use Drupal\imageapi_optimize\Entity\ImageAPIOptimizePipeline;

/**
 * Wrap ImageAPIOptimizePipeline to copy webp derivative to proper directory.
 *
 * This wrapper allows for .webp image derivatives to be copied
 * to the correct directory after the webp image_api handler takes place.
 *
 * Class ImageAPIOptimizeWebPPipeline
 *
 * @package Drupal\imageapi_optimize_webp\Entity
 */
class ImageAPIOptimizeWebPPipeline extends ImageAPIOptimizePipeline {

  /**
   * {@inheritdoc}
   */
  public function applyToImage($image_uri) {
    parent::applyToImage($image_uri);
    // If the source file doesn't exist, return FALSE.
    $image = \Drupal::service('image.factory')->get($image_uri);
    if (!$image->isValid()) {
      return FALSE;
    }
    if (count($this->getProcessors())) {
      $path_info = pathinfo($image_uri);
      $webp_uri = $path_info['dirname'] . '/' . $path_info['filename'] . '.webp';
      $temp_webp_uri = $this->temporaryFiles[0] . '.webp';
      file_unmanaged_move($temp_webp_uri, $webp_uri, FILE_EXISTS_REPLACE);
    }
  }
}