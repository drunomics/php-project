<?php

/**
 * @file
 * Loads dotenv files.
 *
 * This file is included very early with the composer autoloader or invoked
 * via CLI.
 *
 * See loader.sh and autoload.files in composer.json.
 */

use Symfony\Component\Dotenv\Dotenv;

/**
 * Takes care of initializes the phapp environment.
 */
class PhappEnvironmentLoader {

  /**
   * Gets the content of all .*env files to load.
   *
   * @return string
   *   The content of all files. Can be sourced by bash or a dotenv parser.
   */
  public static function getDotenvFiles() {
    $files = [];

    foreach (glob(__DIR__ . '/../.*env') as $filename) {
      $files[] = file_get_contents($filename);
    }
    return implode("\n", $files);
  }

  /**
   * Determines the id of the currently active environment.
   *
   * After parsing the found dotenv files, the currently active environment gets
   * determined. Usually, this is defined by the PHAPP_ENV variable which is
   * defined in some .env files. However, this method can be used to detect
   * other non-standard environments based on some other criteria.
   *
   * @return string|null
   *   A key-value assignmenet as suiting for putenv() or NULL.
   */
  public static function determineEnvironment() {
    // For Acquia we don't have a per environment .env file, but need to parse
    // the AH_SITE_ENVIRONMENT variable.
    if ($siteEnvironment = getenv('AH_SITE_ENVIRONMENT')) {
      // Make use of live - test - dev names.
      $phapp_env = $siteEnvironment == 'prod' ? 'live' : ($siteEnvironment == 'stg' ? 'test' : $siteEnvironment);
      return "PHAPP_ENV=$phapp_env";
    }
  }

  /**
   * Prepares the determined environment by loading any env-specific files.
   *
   * @return string
   *   The content of all files. Can be sourced by bash or a dotenv parser.
   */
  public static function prepareDeterminedEnvironment() {
    $phapp_env = getenv('PHAPP_ENV');
    return file_get_contents(__DIR__ . '/' . $phapp_env . '.env');
  }

  /**
   * Prepare app environment with project-specific dotenv files.
   *
   * @param string $site
   *   The site to load. If none is given the SITE env variable is respected.
   *   Otherwise it defaults to the 'default' site.
   *
   * @return string
   *   The content of all files. Can be sourced by bash or a dotenv parser.
   */
  public static function prepareAppEnvironment($site = NULL) {
    // Nothing todo by default.
    // If your app supports multiple sites or sub-apps, adjust per app
    // environment here.
  }

}

// Allow using the loader via direct CLI execution.
// @see loader.sh
if (php_sapi_name() == "cli" && isset($argv[0]) && strpos($argv[0], '/loader.php') !== 0) {
  if (!method_exists(PhappEnvironmentLoader::class, $argv[1])) {
    die('Unable to find method ' . $argv[1]);
  }
  echo call_user_func(array(PhappEnvironmentLoader::class, $argv[1]));
}
// Else we are loaded via the composer autoloader.
else {

  // The following process must follow the same logic as loader.sh, but instead
  // evaluating .env content with bash we use dotenv to parse it.
  $dotenv = new Dotenv();
  $dotenv->populate($dotenv->parse(PhappEnvironmentLoader::getDotenvFiles()));
  $dotenv->populate($dotenv->parse(PhappEnvironmentLoader::determineEnvironment()));
  if (!getenv('PHAPP_ENV')) {
    die("Missing .env file or PHAPP_ENV environment variable. Did you run phapp setup?");
  }
  $dotenv->populate($dotenv->parse(PhappEnvironmentLoader::prepareDeterminedEnvironment()));

  // For CLI invocations like drush always prepare the app environment also.
  // For regular requests this gets invoked via the app, e.g. via Drupal's
  // settings.php files.
  if (php_sapi_name() == "cli") {
    $dotenv->populate($dotenv->parse(PhappEnvironmentLoader::prepareAppEnvironment()));
  }
}
