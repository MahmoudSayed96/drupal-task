# Drupal Evaluation Task

## Prerequisites

Install on local machine this tools.

- [Git](https://git-scm.com).

- [Composer v2](https://getcomposer.org).

- [PHP8.3](https://www.php.net).

- [MySQL/Mariadb](https://www.mysql.com).

## Installation

This steps requires to install project on your local machine.

1. Clone project from git repository `git clone <repo_url>`

2. Create new database on your local and import database file from `databases` folder. </br> **NOTE** :Use same database user to save table roles.

   ```sql
   -- 1. Create the database with proper encoding
    CREATE DATABASE drupal_task CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

    -- 2. Create a dedicated user for the application (more secure than using root)
    CREATE USER 'drupal_task_user'@'localhost' IDENTIFIED BY 'YOUR_PASSWORD';

    -- 3. Grant all privileges on the new database to the new user
    GRANT ALL PRIVILEGES ON drupal_task.* TO 'drupal_task_user'@'localhost';

    -- 4. Tell the server to reload the grant tables and apply changes
    FLUSH PRIVILEGES;

    -- 5. View all databases to confirm it was created (optional)
    SHOW DATABASES;

   ```

3. Open project in your IDE

   - Set project on development mode by this way.

     - Copy file `default.settings.php` and rename to `settings.local.php`

     - Set database connection with your local database.
       ```php
       $databases['default']['default'] = [
          'database' => 'DATABASE_NAME',
          'username' => 'DB_USER',
          'password' => 'DB_PASS',
          'host' => 'localhost',
          'port' => '3306',
          'isolation_level' => 'READ COMMITTED',
          'driver' => 'mysql',
          'namespace' => 'Drupal\\mysql\\Driver\\Database\\mysql',
          'autoload' => 'core/modules/mysql/src/Driver/Database/mysql/',
        ];
       ```
     - Set config folder path

     ```php
         $settings['config_sync_directory'] = 'sites/default/config/sync';
     ```

     - Set the Drupal Trusted Host Patterns.
       ```php
       $settings['trusted_host_patterns'] = ['YOUR_LOCAL_DOMAIN'];
       ```

4. Install all dependencies and packages using composer.

```bash
composer install
```

5. Import configuration using drush commands

```bash
vendor/bin/drush cim -y
```

6. Update the database.

```bash
vendor/bin/drush updb -y
```

7. Clear all caches

```bash
vendor/bin/drush cr
```

8. Run server

```bash
vendor/bin/drush rs
```

## Author

Copyrights &copy; [Mahmoud Sayed](https://github.com/MahmoudSayed96). All rights reserved

---
