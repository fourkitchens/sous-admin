<?php

declare(strict_types=1);

/**
 * Verify authorization ownership only in a disposable installation.
 *
 * SOUS_DISPOSABLE_TEST=1 SOUS_AUTHORIZATION_CASE=fresh \
 *   ddev drush php:script web/recipes/sous-admin/tests/verify-authorization.php
 * Cases: theme-only, fresh, prepare-legacy, verify-legacy, migration.
 */

use Drupal\Core\Recipe\Recipe;
use Drupal\Core\Recipe\RecipeRunner;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;

if (getenv('SOUS_DISPOSABLE_TEST') !== '1') {
  throw new RuntimeException('Set SOUS_DISPOSABLE_TEST=1 only for a disposable installation.');
}
$scenario = getenv('SOUS_AUTHORIZATION_CASE') ?: 'fresh';
$check = static function (bool $condition, string $message): void {
  if (!$condition) {
    throw new RuntimeException($message);
  }
};
$role_data = static fn (): array => \Drupal::config('user.role.superuser')->getRawData();
$new_account = static function (): User {
  $account = User::create([
    'name' => 'sous_authorization_' . bin2hex(random_bytes(6)),
    'status' => 1,
    'roles' => ['superuser'],
  ]);
  $account->save();
  return $account;
};
$verify_access = static function (User $account) use ($check): void {
  $check((int) $account->id() > 1, 'Authorization checks must use a real role member, not UID 1.');
  $check($account->isActive() && $account->hasRole('superuser'), 'Administrator account or its existing role membership was lost.');
  foreach (['access administration pages', 'administer users', 'administer permissions'] as $permission) {
    $check($account->hasPermission($permission), 'Administrator lost permission: ' . $permission);
  }
  foreach (['system.admin', 'entity.user.collection', 'user.admin_permissions'] as $route) {
    $check(\Drupal::service('access_manager')->checkNamedRoute($route, [], $account, TRUE)->isAllowed(), 'Administrator lost route access: ' . $route);
  }
};
$role = Role::load('superuser');
if ($scenario === 'theme-only') {
  $check(\Drupal::service('theme_handler')->themeExists('emulsify'), 'Apply Sous Emulsify before its standalone check.');
  $check($role === NULL, 'The theme recipe must not create an authorization role.');
}
elseif ($scenario === 'fresh') {
  $check($role !== NULL && $role->isAdmin(), 'Fresh Sous Admin must supply its administrator role.');
  $account = $new_account();
  try {
    $verify_access($account);
  }
  finally {
    $account->delete();
  }
}
elseif ($scenario === 'prepare-legacy') {
  $check($role === NULL, 'Seed the existing-site fixture before applying Sous Admin.');
  $role = Role::create([
    'id' => 'superuser',
    'label' => 'Project administrator',
    'weight' => 17,
    'is_admin' => TRUE,
  ]);
  $role->save();
  $account = $new_account();
  $verify_access($account);
  \Drupal::state()->set('sous_admin.authorization_regression', [
    'role' => $role_data(),
    'uid' => $account->id(),
    'roles' => $account->getRoles(),
  ]);
}
elseif ($scenario === 'verify-legacy') {
  $before = \Drupal::state()->get('sous_admin.authorization_regression');
  $check(is_array($before), 'Run prepare-legacy on this disposable site first.');
  $check($before['role'] === $role_data(), 'Applying updated Admin/Emulsify changed the project-owned legacy role.');
  $account = User::load($before['uid']);
  $check($account !== NULL && $account->getRoles() === $before['roles'], 'Applying recipes changed administrator assignments.');
  $verify_access($account);
}
elseif ($scenario === 'migration') {
  $check($role === NULL, 'Test the missing-role migration on a baseline without the role.');
  $directory = dirname(__DIR__) . '/migrations/2026-10-superuser-role';
  $apply = static function () use ($directory): void {
    RecipeRunner::processRecipe(Recipe::createFromDirectory($directory));
  };
  $account = NULL;
  try {
    $apply();
    $role = Role::load('superuser');
    $check($role !== NULL && $role->isAdmin(), 'The explicit migration did not create the missing administrator role.');
    $account = $new_account();
    $verify_access($account);
    $before = $role_data();
    $apply();
    $check($before === $role_data(), 'Repeating the migration changed the existing role.');

    // A site's legacy explicit-permission role is not silently promoted.
    $role->set('label', 'Project administrator')->setWeight(17)->setIsAdmin(FALSE);
    foreach (['access administration pages', 'administer users', 'administer permissions'] as $permission) {
      $role->grantPermission($permission);
    }
    $role->save();
    $before = $role_data();
    $apply();
    $check($before === $role_data(), 'Migration overwrote a customized existing role.');
    \Drupal::entityTypeManager()->getStorage('user')->resetCache([$account->id()]);
    $account = User::load($account->id());
    $verify_access($account);
  }
  finally {
    if ($account) {
      $account->delete();
    }
    if ($role = Role::load('superuser')) {
      $role->delete();
    }
  }
}
else {
  throw new InvalidArgumentException('Unknown authorization scenario: ' . $scenario);
}
print 'PASS: Sous authorization scenario ' . $scenario . ".\n";
