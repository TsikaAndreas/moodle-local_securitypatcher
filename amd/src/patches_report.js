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
        'core/str', 'core/modal_factory',
        'local_securitypatcher/jquery.dataTables', 'local_securitypatcher/dataTables.dateTime',
        'local_securitypatcher/dataTables.bootstrap4', 'local_securitypatcher/dataTables.buttons',
        'local_securitypatcher/buttons.bootstrap4', 'local_securitypatcher/buttons.colVis',
        'local_securitypatcher/dataTables.responsive', 'local_securitypatcher/responsive.bootstrap4'],
    function ($, Ajax, Notification, Repository, Str, ModalFactory, DataTable
) {
    /**
     *  Initialise and load the datatable.
     *
     *  @param {Object} options - Options for datatable columns.
     */
    function load_datatable(options) {
        $(document).ready( function () {
            // Initialize dataTable.
            let table = $('#patchesreporttable').DataTable({
                dom: 'Brtrip',
                responsive: {
                    details: {
                        type: 'column',
                        target: 0
                    }
                },
                columnDefs: [
                    {responsivePriority: 1, className: 'control', orderable: false, targets: 0, searchable: false},
                    {responsivePriority: 1, targets: 1},
                    {responsivePriority: 2, searchable: false, targets: -1, orderable: false},
                ],
                order: [
                    [1, 'desc']
                ],
                processing: true,
                serverSide: true,
                ajax: function (data, callback, settings) {
                    datatable_loader(false);
                    let args = {
                        id: options.id,
                        data: JSON.stringify(data),
                    };
                    Repository.get_patch_reports(args).then((res) => {
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
                    {data: "timecreated", name: "timecreated", type: "datetime"},
                    {data: "status", name: "status", type: "select"},
                    {data: "operation", name: "operation", type: "select"},
                    {data: "actions"},
                ],
                buttons: [
                    {
                        extend: 'colvis',
                        columns: 'th:nth-child(n+2)'
                    },
                ],
                initComplete: function (settings, json) {
                    datatable_loader(false);
                    add_column_filters(this.api(), settings.aoColumns, options.filters);
                },
            });

            // Hide filters for hidden columns.
            table.columns().every(function (index) {
                if (this.responsiveHidden() === false) {
                    $('#patchesreporttable thead tr:eq(1)').find('th:eq(' + index + ') ').hide();
                }
            });

            // Responsive filters.
            table.on('responsive-resize', function ( e, datatable, columns ) {
                columns.forEach(function (visible, index) {
                    if (visible) {
                        $('#patchesreporttable thead tr:eq(1)').find('th:eq(' + index + ') ').show();
                    } else {
                        $('#patchesreporttable thead tr:eq(1)').find('th:eq(' + index + ') ').hide();
                    }
                });
            });

            // View action.
            table.on('click', 'tbody tr button.view-report-action', function () {
                let patch = parseInt(this.getAttribute('data-patch'), 10);

                let args = {
                    patchid: patch,
                };
            });

            // Delete action.
            table.on('click', 'tbody tr button.delete-report-action', function () {
                let patch = parseInt(this.getAttribute('data-patch'), 10);

                let args = {
                    patchid: patch,
                };
            });
        });
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
        $('#patchesreporttable thead tr').clone(false).appendTo('#patchesreporttable thead');

        columns.forEach(function(item) {
            let clonedCell = $('#patchesreporttable thead tr:eq(1) th:eq(' + item.idx + ')');
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
     * Creates and displays a modal with specified title and content asynchronously.
     *
     * @param {string} title The title for the modal.
     * @param {string} content The html content/body of the modal.
     * @return {void}
     */
    async function display_report_content(title, content) {
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
     * Toggle the visibility of a loader for a data table.
     *
     * @param {boolean} enable - If true, show the loader; if false, hide the loader.
     * @returns {void}
     */
    function datatable_loader(enable) {
        let loader = $('#patches-report-modal-wrapper .datatable-loader');
        if (enable) {
            loader.show();
        } else {
            loader.hide();
        }
    }

    const init = function (options) {
        load_datatable(options);
    }

    return {
        init: init
    };
});