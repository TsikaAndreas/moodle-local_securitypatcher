# local_securitypatcher

The **local_securitypatcher** plugin simplifies the process of applying and managing security patches within your Moodle Learning Management System (LMS). With this plugin, effortlessly import security patch diff files from the official Moodle security announcements.

### Key Features:
- **Effortless Patch Import:** Easily import security patch diff files from official Moodle announcements into your platform.
- **Seamless Application:** Apply security fixes across your Moodle LMS with the click of a button.
- **Error Handling:** Receive clear notifications in case of any errors or code mismatches during patch application, ensuring your platform's integrity.
- **Reversion Capability:** Revert applied security changes swiftly and effortlessly, providing flexibility in managing your platform's security updates.
- **User-Friendly Interface:** All these functionalities are conveniently accessible through a user-friendly interface, making security management hassle-free.

Enhance your Moodle LMS security management by simplifying the process of applying, verifying, and reverting security patches. Keep your platform secure without complexities, using the **local_securitypatcher** plugin.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/local/securitypatcher

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## License ##

2023 Andrei-Robert Tica <andreastsika@gmail.com>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
