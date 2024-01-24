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
 * A javascript module to handle web service calls.
 *
 * @package   local_securitypatcher
 * @copyright 2023 onwards Andrei-Robert Tica <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax', 'core/notification', 'local_securitypatcher/repository',
        'core/prefetch', 'core/str', 'core/modal_factory', 'core/modal_events', 'core/templates',
        'local_securitypatcher/jquery.dataTables', 'local_securitypatcher/dataTables.dateTime',
        'local_securitypatcher/dataTables.bootstrap4',
        'local_securitypatcher/dataTables.buttons', 'local_securitypatcher/buttons.bootstrap4',
        'local_securitypatcher/buttons.colVis', 'local_securitypatcher/buttons.html5',
        'local_securitypatcher/buttons.print', 'local_securitypatcher/pdfmake',
        'local_securitypatcher/dataTables.responsive', 'local_securitypatcher/responsive.bootstrap4'],
    function ($, Ajax, Notification, Repository, Prefetch,
              Str, ModalFactory, ModalEvents, Template, DataTable
) {
    /**
     *  Initialise and load the datatable.
     *
     *  @param {Object} filterOptions - Filter options for datatable columns.
     */
    function load_datatable(filterOptions) {
        $(document).ready(function () {
            // Initialize dataTable.
            let table = $('#patchestable').DataTable({
                dom: 'Brtrip',
                responsive: {
                    details: {
                        type: 'column',
                        target: 0
                    }
                },
                columnDefs: [
                    { responsivePriority: 1, className: 'control', orderable: false, targets: 0, searchable: false },
                    { responsivePriority: 1, targets: 1 },
                    { responsivePriority: 2, searchable: false, targets: -1, orderable: false },
                ],
                order: [
                    [3, 'desc']
                ],
                processing: true,
                serverSide: true,
                ajax: function (data, callback, settings) {
                    datatable_loader(false);
                    let args = {
                        data: JSON.stringify(data),
                    };
                    Repository.get_patches(args).then((res) => {
                        callback({
                            draw: res.draw,
                            recordsTotal: res.recordsTotal,
                            recordsFiltered: res.recordsFiltered,
                            data: res.data,
                        });
                    }).catch((error) => {
                        datatable_loader(false);
                    });
                },
                columns: [
                    {data: null, defaultContent: ""},
                    {data: "name", name: "name", type: "text"},
                    {data: "lastaction", name: "status", type: "select"},
                    {data: "created", name: "timecreated", type: "datetime"},
                    {data: "modified", name: "timemodified", type: "datetime"},
                    {data: "applied", name: "timeapplied", type: "datetime"},
                    {data: "restored", name: "timerestored", type: "datetime"},
                    {data: "actions"},
                ],
                buttons: [
                    {
                        extend: 'colvis',
                        columns: 'th:nth-child(n+2)'
                    },
                    {
                        extend: 'collection',
                        className: 'exportButton',
                        text: 'Export',
                        buttons: [
                            {
                                extend: 'copy',
                                exportOptions: {
                                    columns: 'th:not(:last-child):nth-child(n+2)',
                                }
                            },
                            {
                                extend: 'print',
                                exportOptions: {
                                    columns: 'th:not(:last-child):nth-child(n+2)',
                                }
                            },
                            {
                                extend: 'excel',
                                exportOptions: {
                                    columns: 'th:not(:last-child):nth-child(n+2)',
                                }
                            },
                            {
                                extend: 'pdf',
                                exportOptions: {
                                    columns: 'th:not(:last-child):nth-child(n+2)',
                                }
                            },
                            {
                                extend: 'csv',
                                exportOptions: {
                                    columns: 'th:not(:last-child):nth-child(n+2)',
                                }
                            },
                        ]
                    },
                ],
                initComplete: function(settings, json) {
                    datatable_loader(false);
                    let options = filterOptions['patches'] ?? {};
                    add_column_filters(this.api(), settings.aoColumns, options);
                },
            });

            // Hide filters for hidden columns.
            table.columns().every(function (index) {
                if (this.responsiveHidden() === false) {
                    $('thead tr:eq(1)').find('th:eq(' + index + ') ').hide();
                }
            });

            // Responsive filters.
            table.on( 'responsive-resize', function ( e, datatable, columns ) {
                columns.forEach(function (visible, index) {
                    if (visible) {
                        $('thead tr:eq(1)').find('th:eq(' + index + ') ').show();
                    } else {
                        $('thead tr:eq(1)').find('th:eq(' + index + ') ').hide();
                    }
                });
            });

            // Delete action.
            table.on('click', 'tbody tr button.delete-patch-action', function() {
                let node = this;
                let patch = parseInt(this.getAttribute('data-patch'), 10);

                // Confirmation message.
                let confirmQuestion = Str.get_string('patches:patch_confirmdelete', 'local_securitypatcher');
                let confirmButton = Str.get_string('patches:patch_confirmdeletebtn', 'local_securitypatcher');

                let confirmCallback = function () {
                    datatable_loader(true);
                    let args = {
                        patchid: patch,
                    };
                    Repository.delete_patch(args)
                        .then(function(res) {
                            if (res.result){
                                table.row(node.closest('tr')).remove().draw();
                            }
                            datatable_loader(false);
                        })
                        .catch(function () {
                            datatable_loader(false);
                        });
                };
                show_confirmation(confirmQuestion, confirmButton, confirmCallback);
            });

            // Apply action.
            table.on('click', 'tbody tr button.apply-patch-action', function() {
                let patch = parseInt(this.getAttribute('data-patch'), 10);
                let node = this;

                // Confirmation message.
                let confirmQuestion = Str.get_string('patches:patch_confirmapply', 'local_securitypatcher');
                let confirmButton = Str.get_string('patches:patch_confirmapplybtn', 'local_securitypatcher');

                let confirmCallback = function () {
                    datatable_loader(true);
                    let args = {
                        patchid: patch,
                    };
                    Repository.apply_patch(args)
                        .then(function(res) {
                            if (res.result){
                                let row = table.row(node.closest('tr')).index();
                                table.cell(row, 5).data(res.result.timestamp);
                                table.cell(row, 2).data(res.result.status);
                            }
                            datatable_loader(false);
                        })
                        .catch(function () {
                            datatable_loader(false);
                        });
                };
                show_confirmation(confirmQuestion, confirmButton, confirmCallback);
            });

            // Restore action.
            table.on('click', 'tbody tr button.restore-patch-action', function() {
                let patch = parseInt(this.getAttribute('data-patch'), 10);
                let node = this;

                // Confirmation message.
                let confirmQuestion = Str.get_string('patches:patch_confirmrestore', 'local_securitypatcher');
                let confirmButton = Str.get_string('patches:patch_confirmrestorebtn', 'local_securitypatcher');

                let confirmCallback = function () {
                    datatable_loader(true);
                    let args = {
                        patchid: patch,
                    };
                    Repository.restore_patch(args)
                        .then(function(res) {
                            if (res.result){
                                let row = table.row(node.closest('tr')).index();
                                table.cell(row, 6).data(res.result.timestamp);
                                table.cell(row, 2).data(res.result.status);
                            }
                            datatable_loader(false);
                        })
                        .catch(function () {
                            datatable_loader(false);
                        });
                };
                show_confirmation(confirmQuestion, confirmButton, confirmCallback);
            });

            // View action.
            table.on('click', 'tbody tr button.view-patch-action', function() {
                let patch = parseInt(this.getAttribute('data-patch'), 10);

                datatable_loader(true);
                let args = {
                    patchid: patch,
                };
                Repository.get_patch_info(args)
                    .then(function(res) {
                        if (Object.keys(res.result).length !== 0){
                            let content = "<pre class='patch-info-content'>" + res.result.content + "</pre>";
                            display_patch_content(res.result.name, content)
                        }
                        datatable_loader(false);
                    })
                    .catch(function () {
                        datatable_loader(false);
                    });
            });

            // Report action.
            table.on('click', 'tbody tr button.report-patch-action', function() {
                let filters = filterOptions['patchesreport'] ?? {};
                let context = {
                    patchid: parseInt(this.getAttribute('data-id'), 10),
                    patchname: this.getAttribute('data-name'),
                    filters: JSON.stringify(filters),
                };
                Template.renderForPromise('local_securitypatcher/patches_report', context)
                    .then(async ({html, js}) => {
                        let output = document.createElement("div");
                        output.id = 'patches-report-modal-wrapper';
                        await create_report_modal(context.patchname, output);
                        Template.appendNodeContents($('#patches-report-modal-wrapper'), html, js);
                    })
                    .catch((error) => Notification.exception(error));
            });
        });
    }

    async function create_report_modal(name, html) {
        let modal = await ModalFactory.create({
            title: Str.get_string('datatable:patchesreport', 'local_securitypatcher', name),
            body: html,
            large: true,
            removeOnClose: true,
        });
        datatable_loader(false);
        modal.show();
    }

    /**
     * Clone the header row of a table and add input filters for specified columns.
     *
     * @param {DataTable} table - The DataTable instance representing the table.
     * @param {Array} columns - An array of column configuration objects.
     * @param {Object} filterOptions - Filter options for column.
     * @returns {void}
     */
    function add_column_filters(table, columns, filterOptions) {
        $('#patchestable thead tr').clone(false).appendTo('#patchestable thead');

        columns.forEach(function(item) {
            let clonedCell = $('#patchestable thead tr:eq(1) th:eq(' + item.idx + ')');
            let title = clonedCell.text();
            
            if (item.searchable === false) {
                clonedCell.html('');
                return;
            }

            let classesArray = clonedCell.attr('class').split(' ');
            let sortClasses = classesArray.filter(function(className) {
                return className.includes('sorting');
            });
            clonedCell.removeClass(sortClasses.join(' '));

            create_filter_input(table, item, clonedCell, title, filterOptions);
        });
    }

    /**
     * Creates a filter input based on item type and applies it to a DataTable column.
     *
     * @param {DataTable} table - The DataTable instance.
     * @param {Object} item - The item configuration.
     * @param {jQuery} cell - The jQuery object representing the table cell.
     * @param {string} title - The column name.
     * @param {Object} filterOptions - Filter options for column.
     * @returns {void}
     */
    function create_filter_input(table, item, cell,title, filterOptions) {
        switch (true) {
            case ['text', 'datetime'].includes(item.type):
                cell.html('<input class="form-control text-center w-100" type="text" placeholder="' + title + '"/>');
                $('input', cell).on('keyup change', function () {
                    if (table.column(item.idx).search() !== this.value) {
                        table.column(item.idx).search(this.value).draw();
                    }
                });
                if (item.type === 'datetime') {
                    new DateTime($('input', cell), {
                        format: 'DD-MM-YYYY'
                    });
                }
                break;
            case item.type === 'select' && filterOptions.hasOwnProperty(item.name):
                cell.html(filterOptions[item.name]);
                $('select', cell).on('change', function () {
                    if (table.column(item.idx).search() !== this.value) {
                        table.column(item.idx).search(this.value).draw();
                    }
                });
                break;
        }
    }

    /**
     * Display a confirmation dialog with a specified question and custom confirmation button.
     *
     * @param {string|Promise} question - The question or message to display in the confirmation dialog.
     * @param {string|Promise} confirmButton - The label for the confirmation button.
     * @param confirmCallback - The callback function to execute when the confirmation button is clicked.
     * @returns {void}
     */
    function show_confirmation(question, confirmButton, confirmCallback) {
        Notification.confirm(
            Str.get_string('confirm_title', 'local_securitypatcher'),
            question,
            confirmButton,
            Str.get_string('confirm_cancel', 'local_securitypatcher'),
            confirmCallback,
            null
        );
    }

    /**
     * Creates and displays a modal with specified title and content asynchronously.
     *
     * @param {string|Promise} title The title for the modal.
     * @param {string} content The html content/body of the modal.
     * @return {void}
     */
    async function display_patch_content(title, content) {
        let modal = await ModalFactory.create({
            title: title,
            body: content,
            large: true,
            removeOnClose: true,
        });
        datatable_loader(false);
        modal.show();
    }

    /**
     * Prefetches the language strings.
     *
     * @return {void}
     */
    function prefetch_strings() {
        Prefetch.prefetchStrings('local_securitypatcher', [
            'confirm_title', 'confirm_cancel', 'patches:patch_confirmdelete',
            'patches:patch_confirmdeletebtn', 'patches:patch_confirmapply',
            'patches:patch_confirmapplybtn', 'patches:patch_confirmrestore',
            'patches:patch_confirmrestorebtn', 'datatable:patchesreport',
            'patchesreport:report_confirmdeletebtn', 'patchesreport:report_confirmdelete',
        ]);
    }

    /**
     * Prefetch the templates.
     *
     * @return {void}
     */
    function prefetch_templates() {
        Prefetch.prefetchTemplate('local_securitypatcher/patches_report');
    }

    /**
     * Toggle the visibility of a loader for a data table.
     *
     * @param {boolean} enable - If true, show the loader; if false, hide the loader.
     * @returns {void}
     */
    function datatable_loader(enable) {
        let loader = $('.table-wrapper .datatable-loader');
        if (enable) {
            loader.show();
        } else {
            loader.hide();
        }
    }

    const init = function (filterOptions) {
        prefetch_strings();
        prefetch_templates();
        load_datatable(filterOptions);
    }

    return {
        init: init
    };
});