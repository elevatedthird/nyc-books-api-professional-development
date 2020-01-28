<?php

namespace Drupal\nyc\Plugin\QueueWorker;

/**
 * Provides base functionality for the Upsert Queue Workers.
 */
/**
 * Save Resource nodes nodes on CRON run.
 *
 * @QueueWorker(
 *   id = "nyc_upsert_book_cron",
 *   title = @Translation("Upsert Book Cron"),
 *   cron = {"time" = 10}
 * )
 */
class UpsertBookCron extends UpsertBookBase {

}
