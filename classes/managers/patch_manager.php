<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Patch manager class for the local_securitypatcher plugin.
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_securitypatcher\managers;

use context_system;
use local_securitypatcher\api;
use stdClass;
use stored_file;

/**
 * Patch manager class responsible for the management of security patches.
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class patch_manager {

    /** Patch is clean, not applied. */
    public const PATCH_CLEAN = 0;
    /** Patch has been applied. */
    public const PATCH_APPLIED = 1;
    /** Patch has been restored. */
    public const PATCH_RESTORED = 2;
    /** Patch report success status. */
    public const PATCH_REPORT_SUCCESS = 'success';
    /** Patch report error status. */
    public const PATCH_REPORT_ERROR = 'error';

    /** @var int The identifier of the security patch. */
    private int $patchid;
    /** @var object The current security patch in use. */
    private object $currentpatch;
    /** @var array|string[] Valid patch operations. */
    private array $validpatchoperations = ['apply', 'restore'];
    /** @var string The operation action. */
    private string $operationaction;

    /** @var array $operationoutput An array to store the output generated during patch operations. */
    private array $operationoutput = [];
    /**
     * @var int|null $operationstatus Represents the status of the patch operation.
     *                            - When set to 0, it indicates a successful execution of the operation.
     *                            - Anything else, it indicates that an error occurred during the operation.
     */
    private ?int $operationstatus = null;

    /**
     * @var string The filearea that the security patches are stored.
     */
    public static string $filearea = 'local_securitypatcher_security_patches';
    /**
     * @var string The component the security files belongs.
     */
    public static string $component = 'local_securitypatcher';

    /**
     * Retrieve a security patch record by its ID.
     *
     * @param int $patchid The ID of the security patch to retrieve.
     *
     * @return stdClass|false Returns the security patch record as an object if found, or false if not found.
     */
    public function get_patch(int $patchid): false|stdClass {
        global $DB;

        return $DB->get_record('local_securitypatcher', ['id' => $patchid], '*', MUST_EXIST);
    }

    /**
     * Creates a new security patch record in the database and stores the file attachment.
     *
     * @param object $formdata An object containing the form data for the new patch.
     *
     * @return bool Returns true if successful, or false if an error occurred.
     */
    public function create_patch(object $formdata): bool {
        global $DB;

        $context = context_system::instance();

        $currenttime = time();

        // Create a new stdClass object to hold patch data.
        $patch = new stdClass();

        // Set the name of the patch.
        $patch->name = $formdata->name;

        // Initialize other fields with default values.
        $patch->status = self::PATCH_CLEAN;
        $patch->attachments = null;
        $patch->timeapplied = null;
        $patch->timerestored = null;
        $patch->timecreated = $currenttime;
        $patch->timemodified = $currenttime;

        // Insert the new patch record into the database.
        $patchid = $DB->insert_record('local_securitypatcher', $patch);

        // Check if the insertion was successful.
        if (empty($patchid)) {
            return false;
        }

        // Assign the generated ID to the patch object.
        $patch->id = $patchid;

        // Update the attachments.
        $formdata = file_postupdate_standard_filemanager($formdata, 'attachments', api::get_patch_filemanager_options(),
                $context, self::$component, self::$filearea, $patch->id);

        // Update the 'attachments' field in the patch object.
        $patch->attachments = $formdata->attachments;

        // Update the patch record in the database and return the result.
        return $DB->update_record('local_securitypatcher', $patch);
    }

    /**
     * Updates an existing security patch record in the database.
     *
     * @param object $formdata An object containing the updated form data for the patch.
     *
     * @return bool|int Returns true if the update was successful, false if an error occurred.
     */
    public function update_patch(object $formdata): bool|int {
        global $DB;

        $context = context_system::instance();

        // Retrieve the existing patch record based on its ID.
        $patch = $this->get_patch($formdata->id);

        // Update the modified fields of the patch.
        $patch->name = $formdata->name;
        $patch->timemodified = time();

        // Update the attachments.
        $formdata = file_postupdate_standard_filemanager($formdata, 'attachments', api::get_patch_filemanager_options(),
                $context, self::$component, self::$filearea, $patch->id);

        // Update the 'attachments' field in the patch object.
        $patch->attachments = $formdata->attachments;

        // Update the patch record in the database and return the result.
        return $DB->update_record('local_securitypatcher', $patch);
    }

    /**
     * Retrieves a stored patch file from the Moodle file storage.
     *
     * @return stored_file Returns the stored file or throws error if not found.
     */
    private function get_stored_file(): stored_file {
        global $DB;

        $contextid = context_system::instance()->id;

        // Retrieve the patch record from the table.
        if (empty($this->currentpatch)) {
            $this->currentpatch = $DB->get_record('local_securitypatcher', ['id' => $this->patchid], '*', MUST_EXIST);
        }

        // Get the Moodle file storage.
        $fs = get_file_storage();
        // Retrieve the file.
        $areafiles = $fs->get_area_files($contextid, self::$component, self::$filearea, (int) $this->currentpatch->id);
        foreach ($areafiles as $file) {
            if ($file->get_filesize() > 0) {
                return $file;
            }
        }

        throw new \RuntimeException(get_string('exception:patchfilenotfound',
                'local_securitypatcher', $this->currentpatch->name), 404);
    }

    /**
     * Get the local file path associated with a stored_file object.
     *
     * @param stored_file $file The stored_file object for which to retrieve the local file path.
     *
     * @return string The local file path associated with the stored_file object.
     */
    private function get_patch_path(stored_file $file): string {
        $fs = get_file_storage();
        return $fs->get_file_system()->get_local_path_from_storedfile($file);
    }

    /**
     * Deletes the stored security patch file.
     *
     * @param int $patchid The ID of the security patch file to delete.
     * @return bool always true or exception if error occurred.
     */
    public function delete_patch_stored_file(int $patchid): bool {
        $contextid = context_system::instance()->id;

        // Get the Moodle file storage.
        return get_file_storage()->delete_area_files($contextid, self::$component, self::$filearea, $patchid);
    }

    /**
     * Retrieves the configured path to the Git command.
     *
     * @return false|mixed|object|string The path to the Git command if configured, or false if not set.
     */
    private function get_git_command_path(): mixed {
        return get_config('local_securitypatcher', 'git');
    }

    /**
     * Verifies if the operation action is set and matches one of the valid patch operations.
     *
     * @throws \InvalidArgumentException|\coding_exception When no operation action is found or if the provided action is invalid.
     * @return void
     */
    private function check_operation_action(): void {
        if (empty($this->operationaction)) {
            throw new \InvalidArgumentException(get_string('exception:operationnotfound', 'local_securitypatcher'), 500);
        }
        if (!in_array($this->operationaction, $this->validpatchoperations, true)) {
            throw new \InvalidArgumentException(get_string('exception:invalidoperation',
                    'local_securitypatcher', $this->operationaction), 500);
        }
    }

    /**
     * Perform a patch operation (apply or restore) identified by its ID.
     *
     * @param int $patchid The ID of the patch to be operated on.
     * @return void
     */
    public function perform_patch_operation(int $patchid): void {
        global $DB, $CFG;

        $this->check_operation_action();

        $gitpath = $this->get_git_command_path();
        if (empty($gitpath)) {
            throw new \RuntimeException(get_string('exception:gitpathnotfound', 'local_securitypatcher'), 404);
        }

        $this->currentpatch = $DB->get_record('local_securitypatcher', ['id' => $patchid], '*', MUST_EXIST);
        $this->patchid = $patchid;

        $file = $this->get_stored_file();
        $filepath = $this->get_patch_path($file);

        // Execute the operation.
        $patchcommand = ($this->operationaction === 'restore') ? '-R' : ''; // Adjust the patch command based on operation.
        $command = "cd $CFG->dirroot && $gitpath apply --verbose $patchcommand $filepath 2>&1";
        exec($command, $this->operationoutput, $this->operationstatus);

        $this->process_output();
    }

    /**
     * Update the security patch status.
     *
     * @return void
     */
    private function update_patch_status(): void {
        global $DB;
        switch ($this->operationaction){
            case 'apply':
                $this->currentpatch->status = self::PATCH_APPLIED;
                $this->currentpatch->timeapplied = time();
                break;
            case 'restore':
                $this->currentpatch->status = self::PATCH_RESTORED;
                $this->currentpatch->timerestored = time();
                break;
            default:
                return;
        }
        $DB->update_record('local_securitypatcher', $this->currentpatch);
    }

    /**
     * Processes the output after performing the patch operations.
     *
     * @return void
     */
    private function process_output(): void {
        $this->update_patch_status();
        $this->create_report_data();
    }

    /**
     * Parses the operation output array into a string.
     *
     * If the operation output array has multiple items, it concatenates them
     * with newline characters. If there is only one item, it returns that item as a string.
     *
     * @return string The parsed operation output as a string.
     */
    private function parse_operation_output(): string {
        return implode(PHP_EOL, $this->operationoutput);
    }

    /**
     * Creates and inserts report data database.
     *
     * @return int The ID of the inserted record.
     */
    private function create_report_data(): int {
        global $DB;

        $record = new stdClass();
        $record->patchid = $this->currentpatch->id;
        $record->statuscode = $this->operationstatus;
        $record->status = $this->parse_report_status_name($this->operationstatus);
        $record->data = $this->parse_operation_output();
        $record->operation = $this->operationaction;
        $record->timecreated = time();
        $record->timemodified = time();

        return $DB->insert_record('local_securitypatcher_data', $record, true);
    }

    /**
     * Parses the numeric status code into a corresponding report status name.
     *
     * @param int $status The numeric status code.
     * @return string The report status name.
     */
    private function parse_report_status_name(int $status): string {
        return match ($status) {
            0 => self::PATCH_REPORT_SUCCESS,
            default => self::PATCH_REPORT_ERROR,
        };
    }

    /**
     * Retrieves patch information by ID.
     *
     * @param int $patchid The ID of the patch to retrieve information for.
     * @return stdClass An object containing patch information:
     *  - name: The name of the patch.
     *  - content: The content of the stored file associated with the patch.
     */
    public function get_patch_info(int $patchid): object {
        $this->currentpatch = $this->get_patch($patchid);
        $file = $this->get_stored_file();

        $info = new stdClass();
        $info->name = $this->currentpatch->name;
        $info->content = $file->get_content();

        return $info;
    }

    /**
     * Retrieves the time when the patch was applied.
     *
     * @param bool $formated Whether to return the formatted date.
     * @return int|string If $formated is true, returns the formatted date; otherwise, returns the timestamp.
     */
    public function get_timeapplied(bool $formated = false): int|string {
        if ($formated) {
            return api::get_date($this->currentpatch->timeapplied);
        }
        return $this->currentpatch->timeapplied;
    }

    /**
     * Retrieves the time when the patch was restored.
     *
     * @param bool $formated Whether to return the formatted date.
     * @return int|string If $formated is true, returns the formatted date; otherwise, returns the timestamp.
     */
    public function get_timerestored(bool $formated = false): int|string {
        if ($formated) {
            return api::get_date($this->currentpatch->timerestored);
        }
        return $this->currentpatch->timerestored;
    }

    /**
     * Retrieves the status of the patch.
     *
     * @return \lang_string|string The name of the last action for the patch's status.
     */
    public function get_status(): \lang_string|string {
        return $this->get_last_operation_name($this->currentpatch->status);
    }

    /**
     * Retrieves the name of the last operation performed based on the patch status.
     *
     * @param int $status The status of the patch.
     * @return \lang_string|string The name of the last operation performed.
     */
    public function get_last_operation_name(int $status): \lang_string|string {
        return match ($status) {
            self::PATCH_APPLIED => get_string('apply', 'local_securitypatcher'),
            self::PATCH_RESTORED => get_string('restore', 'local_securitypatcher'),
            default => get_string('none', 'local_securitypatcher'),
        };
    }

    /**
     * Get the localized operation name based on the specified operation key.
     *
     * @param string $operation The key representing the operation.
     * @return \lang_string|string The localized operation name.
     */
    public static function get_operation_name(string $operation): \lang_string|string {
        return match ($operation) {
            'apply' => get_string('apply', 'local_securitypatcher'),
            'restore' => get_string('restore', 'local_securitypatcher'),
            default => throw new \InvalidArgumentException(get_string('exception:invalidoperation',
                    'local_securitypatcher', $operation), 500),
        };
    }

    /**
     * Get the localized patch report status based on the specified status.
     *
     * @param string $status The status representing the patch report status.
     * @return \lang_string|string The localized patch report status.
     */
    public static function get_patch_report_status(string $status): \lang_string|string {
        return match ($status) {
            self::PATCH_REPORT_SUCCESS => get_string('operation_success', 'local_securitypatcher'),
            default => get_string('operation_error', 'local_securitypatcher'),
        };
    }

    /**
     * Sets the current security patch in use.
     *
     * @param object $currentpatch The current security patch object in use.
     */
    public function set_current_patch(object $currentpatch): void {
        $this->currentpatch = $currentpatch;
    }

    /**
     * @param string $action The operation action.
     */
    public function set_operation_action(string $action): void {
        $this->operationaction = $action;
    }
}