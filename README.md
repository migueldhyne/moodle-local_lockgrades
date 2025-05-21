# Lock Grades (`local_lockgrades`)

## Description

`local_lockgrades` is a Moodle plugin that allows administrators to recursively lock or unlock a grade category (grade items) along with all its subcategories and associated items, using an `idnumber` field as the identifier.

## Features

* **Recursive locking**: Locks a parent category and all its subcategories, updating the `locked`, `timemodified`, and `locktime` fields in the `mdl_grade_items` table.
* **Recursive unlocking**: Unlocks the same items by resetting `locked` and `locktime` to 0 while preserving the modification timestamp (`timemodified`).
* **Simple interface**: A form is available in the Moodle administration to enter the `idnumber` and choose the action (lock or unlock).
* **Security**: Access is restricted to users with the `moodle/site:config` capability (administrators).
* **Data integrity**: Uses transactions to ensure consistent updates.

## Requirements

* Moodle 3.5 or higher
* SSH or FTP access to copy files to the server
* Administrator rights on the Moodle platform

## Installation

1. **Copy the files**

   Place the `lockgrades` folder inside the `local/` directory of your Moodle installation, so the full path is:

   ```
   moodle/local/lockgrades/
   ```

2. **Check permissions**

   Make sure files and directories have appropriate permissions (readable by the web server):

   ```bash
   chown -R www-data:www-data moodle/local/lockgrades
   chmod -R 755 moodle/local/lockgrades
   ```

3. **Database update**

   Log in as an administrator on your Moodle site. Moodle will automatically detect the new plugin and prompt you to update the database.

4. **Verification**

   Go to **Site administration > Plugins > Local plugins** and check that `Lock Grades` appears in the list.

## Usage

1. Log in with an administrator account (with `moodle/site:config` capability).

2. In your browser, open the URL:

   ```
   https://your-moodle-site.local/local/lockgrades/index.php
   ```

3. Enter the **idnumber** of the main category whose grades you want to lock or unlock (e.g., `totPeriode_1`).

4. Click on **Lock grades** or **Unlock grades**.

5. A notification will confirm the success of the operation.

## File structure

```
local/lockgrades/
├── form.php             # Form definition
├── index.php            # Main page and plugin logic
├── version.php          # Version and dependencies
└── lang/
    └── en/
        └── local_lockgrades.php  # Language strings
```

## Customization

* **Adjust capabilities**: To restrict access to other roles, modify the capability used in `index.php` (`moodle/site:config`).
* **Edit messages**: Modify the language strings in `lang/en/local_lockgrades.php`.

## License

This plugin is distributed under the GNU GPL v3 license. See the `LICENSE` file for details.

## Authors and support

* **Author name**: Miguël Dhyne
* **Contact**: [miguel.dhyne@gmail.com](mailto:miguel.dhyne@gmail.com)

For any questions or contributions, please open an issue or submit a pull request on the project’s GitHub repository.
