<?php

namespace Drupal\nyc\Plugin\QueueWorker;

/**
 * Provides base functionality for the Upsert Queue Workers.
 */
/**
 * Save Resource nodes nodes on CRON run.
 *
 * @QueueWorker(
 *   id = "nyc_upsert_book_manual",
 *   title = @Translation("Upsert Book Manual"),
 *   cron = {"time" = 10}
 * )
 */
class UpsertBookManual extends UpsertBookBase {

}
