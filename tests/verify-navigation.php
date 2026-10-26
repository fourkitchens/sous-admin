<?php

/**
 * Verify Sous Admin's Navigation menu after recipe application.
 *
 * Run from an installed site's project root:
 * ddev drush php:script web/recipes/sous-admin/tests/verify-navigation.php
 */

$menu = \Drupal::entityTypeManager()->getStorage('menu')->load('content');
if (!$menu || !$menu->status()) {
  throw new \RuntimeException('Navigation requires its enabled Content menu.');
}
$manager = \Drupal::service('plugin.manager.block');
if (!$manager->hasDefinition('navigation_menu:content')) {
  throw new \RuntimeException('The Navigation Content block plugin is missing.');
}
$sections = \Drupal::config('navigation.block_layout')->get('sections') ?? [];
foreach ($sections as $section) {
  foreach ($section['components'] ?? [] as $component) {
    $id = $component['configuration']['id'];
    if (!$manager->hasDefinition($id)) {
      throw new \RuntimeException('Navigation references a missing block plugin: ' . $id);
    }
  }
}
print "PASS: Navigation Content menu and every configured block plugin are available.\n";
