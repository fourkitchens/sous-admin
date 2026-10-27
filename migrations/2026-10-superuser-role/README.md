# Missing administrator role

This is an explicit native Drupal configuration migration, not an automatic
update hook. Use it only after reviewing a site's authorization policy and
confirming the `superuser` role is missing. It creates an administrator role
with the same machine ID and defaults used by fresh Sous Admin installations.
It does not assign that role to any account.

Existing Sous Emulsify installations normally need no configuration migration:
their active role and user assignments already exist and survive package/source
updates. Keep the role in the project's complete configuration export; account
assignments remain in its database. Updating a
recipe does not retroactively apply its configuration to the site.

Test a database copy first. Once this reviewed migration source is available in
the generated project's `web/recipes/sous-admin`, apply only this migration:

```sh
ddev drush recipe /var/www/html/web/recipes/sous-admin/migrations/2026-10-superuser-role
ddev drush cr
ddev drush config:export
```

Review the project-owned export, administrator account access, and any intended
role assignment before committing and deploying the configuration normally.
The migration uses core `createIfNotExists`: repeating it preserves an existing
role's UUID, label, weight, permissions, administrator flag, and all account
assignments. It never promotes an existing explicit-permission role or replaces
project customizations. Any such authorization change belongs in a separate
project-reviewed configuration migration.
