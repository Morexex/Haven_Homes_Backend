Run Migrations for a Specific Property

Use the custom command to migrate a specific property database:

php artisan migrate:property {property_name}
Replace {property_name} with the name of the property, e.g.:

php artisan migrate:property "My Property"

If you want to run a fresh migration (dropping all tables and re-migrating), use the --fresh option:

php artisan migrate:property "My Property" --fresh



Run Migrations for All Properties
To migrate all property databases:

php artisan migrate:all-properties

To refresh all property databases:

php artisan migrate:all-properties --fresh



Rollback for a Single Property
To rollback the last migration (or migrations up to a certain step) for a specific property:

php artisan migrate:rollback-property {property_name} --step=1

Replace {property_name} with the name of the property.
Adjust the --step value to specify how many migrations to roll back.


Rollback for All Properties
To rollback migrations for all property databases:

php artisan migrate:rollback-all-properties --step=1
Adjust the --step value as needed.



To move a migration to the property_specific folder:

mv database/migrations/2025_01_18_164024_create_property_testing_table.php database/mi
grations/property_specific/2025_01_18_164024_create_property_testing_table.php
