# Sous Admin Drupal Recipe

A recipe that contains modules, themes, and configuration for the Sous administration experience.

## Supported baseline and recipe combinations

Apply once to the supported Minimal baseline in the [platform guide](https://github.com/fourkitchens/sous-drupal-project/blob/main/docs/PLATFORM.md),
with its Composer dependencies installed. Sous Admin installs Node, Media, and Media Library
explicitly, along with its administration modules. It does not apply Sous
Content Types, the full Sous Formats recipe, or Sous Media. Navigation's Content
menu is imported explicitly so its navigation block is available on Minimal.

Sous Formats and Sous Content Types can be applied before or after Sous Admin.
Both recipes consume the same Linkit defaults from the Sous Formats package.
Admin applies only that small subrecipe, so it does not add CKEditor or text
formats. A fresh default profile has node, media, and external matchers in either
order. An existing profile, including its custom matcher settings, is retained
unchanged; updating a recipe package does not modify it automatically.

Install recipe packages in the documented Drupal-root `recipes/` directory
(`web/recipes/` in Sous). The native include
`recipes/sous-formats/recipes/linkit` is relative to the Drupal root, not to this
repository. The Formats package is an explicit Composer dependency. Its current
`dev-main` exception should become a bounded stable constraint only after the
coordinated release actually exists.

Legacy Gin blocks are disabled when present; newer Gin versions that omit them
are supported without creating obsolete blocks.

Sous Admin checks its strict Gin baseline before running configuration actions.
Reapplying the full recipe after its Gin settings actions, or after later site
customization, can fail that check. Apply it once to the supported baseline.

## Administration defaults and optional conveniences

The administration contract is the Superuser authorization role, Gin, core
Navigation and its Content menu, and using the administration theme for Node
forms. Node, Media, and Media Library provide the explicit content-administration
baseline. The remaining modules are installed defaults that a project can
choose to replace or omit; they are not all prerequisites for authorization.

| Group | Included defaults and purpose |
| --- | --- |
| Site-building and content-model support | Field UI is the site-building UI. Field Group, Publication Date, Scheduler, and Tagify also support the independent Content Types/Media recipes; their field/display dependencies must be retained when those features are used. |
| Editorial conveniences | Autosave Form, Save and Edit, Media Library Edit, and the shared Linkit profile enhance authoring. Media Library Edit is independently required by Sous Media. |
| Administration and operations conveniences | Config Log, Entity Usage, Login History, Login Destination, Navigation Extra Tools, Views AJAX History, Views Bulk Operations, and Trash add audit, navigation, reference tracking, bulk editing, login, and deletion behavior. |
| Scheduled operation policy | Automated Cron supplies the default cron trigger; a project with a managed external cron service can deliberately replace it. |

All existing defaults remain enabled for compatibility. No separate Admin-lite
package is required. For a lean new project, use a project-owned recipe that
selects the required modules and administration configuration. On an existing
site, review dependencies and retained data before using Drupal's module
uninstaller; deleting an `install` entry or removing a Composer requirement does
not uninstall the module. Config Log, Login History, Entity Usage, Scheduler,
and Trash may contain operational data or affect published/deleted content.
Uninstall safely first, export and review the complete site configuration, then
remove a package only when no remaining feature requires it. The Admin recipe
also supplies settings for Entity Usage, Save and Edit, and Login Destination;
review those project-owned settings when changing the corresponding feature.

## Authorization belongs to administration

Sous Admin supplies the `superuser` role for new installations. It is Drupal's
administrator role (`is_admin: true`), so assignments grant every permission,
including permissions introduced by later modules. Assign it only to trusted
site administrators. The theme recipe no longer supplies authorization.

The existing machine ID is unchanged. A site that previously applied Sous
Emulsify keeps its active role, UUID, project customization, and user assignments;
removing the role file from the theme recipe does not delete active Drupal
configuration. Sous Admin creates its default only when the role is missing and
does not overwrite an existing role. Export and commit the site's complete
configuration after the reviewed upgrade. Do not delete the existing role from
the site's export just because it moved between recipe source repositories.

Updating a recipe package does not run configuration migrations on existing
sites. Do not reapply the full Admin recipe as an update mechanism. If a site
actually lacks this administrator role and intends to adopt it, review and apply
the narrowly scoped native migration in
[`migrations/2026-10-superuser-role`](migrations/2026-10-superuser-role/README.md).
An existing explicit-permission or customized role remains unchanged; converting
it into a full administrator role requires a separate project-owned, reviewed
authorization change.

## Installing this Recipe

These examples select the current unreleased implementation on `dev-main`.
Run them in a configured consuming site, not in this recipe source checkout.
[Setup and recipe prerequisites](https://github.com/fourkitchens/sous-drupal-project/blob/main/docs/SETUP.md) define the required
repositories, installer paths, core unpacking plugin, and explicit root stability
exceptions. Use reviewed stable tags once the coordinated releases exist.

`ddev composer require fourkitchens/sous-admin:dev-main`

## Applying this Recipe

If you used the Sous Project as your starterkit:

- `ddev install-recipe fourkitchens/sous-admin`

Without the Sous DDEV command, use a configured consuming project and run from its root:

- `ddev drush recipe /var/www/html/web/recipes/sous-admin`
- `ddev drush cache:rebuild`

Use the [current native unpacking workflow](https://github.com/fourkitchens/sous-drupal-project/blob/main/docs/SETUP.md) before application.

## Regression check

After applying the recipe, verify that Navigation's menus and configured block
plugins are available:

```sh
ddev drush php:script web/recipes/sous-admin/tests/verify-navigation.php
```

On a disposable installation, the authorization check also verifies that a role
member with a user ID greater than 1 retains actual administrative route access:

```sh
ddev exec env SOUS_DISPOSABLE_TEST=1 SOUS_AUTHORIZATION_CASE=fresh \
  vendor/bin/drush php:script web/recipes/sous-admin/tests/verify-authorization.php
```

The shared integration fixtures additionally exercise standalone theme
application, customized legacy roles, user assignments, and the missing-role
migration. These are destructive fixture checks, never a production update.

## Development, integration, and releases

This repository is reusable recipe source, not a generated Drupal application.
Its Composer requirements and `recipe.yml` describe installation defaults; the
consuming project owns its runtime, complete configuration export, custom
theme, and application deployment. Requiring `dev-main` downloads remote source;
it does not test uncommitted sibling checkout changes.

Use the shared documentation instead of maintaining another setup procedure:

- [Supported runtime and package relationships](https://github.com/fourkitchens/sous-drupal-project/blob/main/docs/PLATFORM.md)
- [Fresh installation, contributor clones, restoration, and recovery](https://github.com/fourkitchens/sous-drupal-project/blob/main/docs/SETUP.md)
- [Sibling-source development and integration checks](https://github.com/fourkitchens/sous-drupal-project/blob/main/docs/INTEGRATION.md)
- [Configuration ownership and existing-site upgrades](https://github.com/fourkitchens/sous-drupal-project/blob/main/docs/MAINTENANCE.md)
- [Frontend development and production assets](https://github.com/fourkitchens/sous-drupal-project/blob/main/docs/BUILD_TOOLS.md)
- [Production deployment](https://github.com/fourkitchens/sous-drupal-project/blob/main/docs/DEPLOYMENT.md)
- [Coordinated releases and Conventional Commits](https://github.com/fourkitchens/sous-drupal-project/blob/main/docs/RELEASES.md)
- [Maintenance ownership and review cadence](https://github.com/fourkitchens/sous-drupal-project/blob/main/docs/OPERATIONS.md)

Recipe release tooling runs with the Node/npm versions pinned in `.nvmrc` and
`package.json`; Node is not required to execute a recipe in Drupal. The private
npm package publishes no browser assets or npm release. The thin workflow calls
the shared integration gate before the guarded GitHub publisher. Its central
workflow must be rolled out first; source configuration is not proof of a live
GitHub run or release. See the release guide for actual tag state, stable-tag
adoption, and explicit development exceptions.

Package updates do not automatically change configuration already applied to a
site. Preserve its complete export and adopt changes through reviewed native
configuration migrations and project-owned overrides. Do not routinely replay
the entire recipe on a customized site.

## License

Sous recipe source is licensed under [GPL-2.0-or-later](LICENSE). Dependencies
retain their own license terms; see the shared license policy in the platform
guide.
