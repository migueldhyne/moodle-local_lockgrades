# Lock Grades (`local_lockgrades`)

## Description

`local_lockgrades` is a Moodle plugin that allows administrators to recursively lock or unlock a grade category (grade items) along with all its subcategories and associated items, using an `idnumber` field as the identifier. Additionally, you can optionally filter which courses to affect by specifying a substring that must appear in the course `shortname`.

## Features

* **Recursive locking**: Lock a parent category and all its subcategories, updating the `locked`, `timemodified`, and `locktime` fields in the `mdl_grade_items` table.
* **Recursive unlocking**: Unlock the same items by resetting `locked` and `locktime` to `0` while preserving the modification timestamp (`timemodified`).
* **Course filtering**: Optionally enter a pattern to only (un)lock grades in courses whose `shortname` contains that pattern.
* **Action scheduling**: **New!** Schedule locking or unlocking actions for a specific date and time. The task will be executed automatically by Moodle's cron.
* **Task overview and management**: **New!** See a table of all scheduled (pending) lock/unlock actions, with execution time and action type. Each scheduled task can be modified, duplicated or deleted from the interface before execution.
* **Previsualization ("dry-run")**: **New!** Preview the list of courses that would be affected by a lock/unlock action before applying or scheduling it.
* **Ancestral lock inheritance**: **New!** Automatically applies locking rules to any newly created or updated grade item or category when at least one ancestor category is already locked. Ensures that inheritance-based locks propagate through deep category hierarchies without manual intervention.
* **Unlock constraints**: You cannot unlock a subcategory if any of its ancestor categories remain locked; to unlock a subcategory, all its parent categories must first be unlocked.
* **Simple interface**\*\*: An admin form lets you enter the category `idnumber`, an optional course `shortname` filter, select the date and time for scheduling, and choose the action (lock, unlock, schedule lock, schedule unlock, or preview).
* **Unified history log**: **New!**
- All actions (executed or scheduled) are listed in a single table, with a status badge (green = executed, grey = scheduled).
- Sort, filter, and search on every column (id, shortname, action, dates, status…).
- Expandable details for each operation (showing impacted items).
- Edit, duplicate, or delete actions for scheduled tasks directly from the table.
* **Security**: Access is restricted to users with the `local/lockgrades:manage` capability (by default administrators).
* **Data integrity**: Uses database transactions to ensure consistent updates.

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

1. Log in with an account that has the `local/lockgrades:manage` capability.

2. Navigate to:

   ```
   https://your-moodle-site.local/local/lockgrades/index.php
   ```

3. Fill in the **Category ID number** of the grade item you wish to (un)lock (e.g., `totPeriode_1`).

4. Optionally fill in the **Course shortname filter**. Leave empty to affect **all** courses.

5. **Preview** the impact using the "Preview" button, which will list all impacted courses before you proceed.

6. To lock or unlock immediately, click **Lock grades** or **Unlock grades**.

7. To schedule a lock or unlock at a future date and time, fill the date/time field and click **Schedule Lock** or **Schedule Unlock**.
   Scheduled actions will be executed automatically by Moodle's cron.

8. **Review and manage scheduled tasks** at the bottom of the page, where you will see a table of all pending actions with their details (category, filter, type, execution time). You can delete any pending task before it is executed.

9. After any action, a notification will confirm the success, and an informational box will explain the "Recalculate anyway" behavior in the gradebook UI.

## Customization

* **Adjust capabilities**: To restrict access to other roles, modify `require_capability('local/lockgrades:manage', ...)` in `index.php` and adjust `access.php` accordingly.
* **Edit messages**: Modify the language strings in `lang/en/local_lockgrades.php` or `lang/fr/local_lockgrades.php`.
* **Default behavior**: The course filter is optional—leaving it empty will (un)lock all matching `idnumber` items across all courses.

## License

This plugin is distributed under the GNU GPL v3 license. See the `LICENSE` file for details.

## Authors and Support

* **Author**: Miguël Dhyne
* **Contact**: [miguel.dhyne@gmail.com](mailto:miguel.dhyne@gmail.com)

For questions or contributions, please open an issue or submit a pull request on the project’s GitHub repository.
