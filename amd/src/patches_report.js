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
        'local_securitypatcher/jquery.dataTables', 'local_securitypatcher/dataTables.bootstrap4',
        'local_securitypatcher/dataTables.buttons', 'local_securitypatcher/buttons.bootstrap4',
        'local_securitypatcher/buttons.colVis', 'local_securitypatcher/dataTables.responsive',
        'local_securitypatcher/responsive.bootstrap4'],
    function ($, Ajax, Notification, Repository, Prefetch, Str, ModalFactory, DataTable
) {
    /**
     *  Initialise and load the datatable.
     */
    function load_datatable() {
        $(document).ready( function () {
            console.log($.fn.DataTable.isDataTable('#patchesreporttable'));
            console.log('loaded report');

            $('#patchesreporttable tbody').empty();

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
                buttons: [
                    {
                        extend: 'colvis',
                        columns: 'th:nth-child(n+2)'
                    },
                ],
                initComplete: function (settings, json) {
                    console.log('laoded complete');
                    datatable_loader(false);
                    add_column_filters(this.api(), settings.aoColumns);
                    $('#patchesreporttable').removeClass('d-none');
                },
            });

            // Hide filters for hidden columns.
            table.columns().every(function (index) {
                if (this.responsiveHidden() === false) {
                    $('thead tr:eq(1)').find('th:eq(' + index + ') ').hide();
                }
            });

            // Responsive filters.
            table.on('responsive-resize', function (e, datatable, columns) {
                columns.forEach(function (visible, index) {
                    if (visible) {
                        $('thead tr:eq(1)').find('th:eq(' + index + ') ').show();
                    } else {
                        $('thead tr:eq(1)').find('th:eq(' + index + ') ').hide();
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
        });
    }

    /**
     * Clone the header row of a table and add input filters for specified columns.
     *
     * @param {DataTable} table - The DataTable instance representing the table.
     * @param {Array} columns - An array of column configuration objects.
     * @returns {void}
     */
    function add_column_filters(table, columns) {
        $('#patchesreporttable thead tr').clone(true).appendTo('#patchesreporttable thead');

        columns.forEach(function(item) {
            let clonedCell = $('#patchesreporttable thead tr:eq(1) th:eq(' + item.idx + ')');
            let title = clonedCell.text();
            console.log(title);
            console.log(item);
            if (item.searchable === false) {
                clonedCell.html('');
                return;
            }

            let classesArray = clonedCell.attr('class').split(' ');
            let sortClasses = classesArray.filter(function(className) {
                return className.includes('sorting');
            });
            clonedCell.removeClass(sortClasses.join(' '));

            clonedCell.html('<input class="text-center w-100" type="text" placeholder="' + title + '"/>');

            // Search on keyup in every column.
            $('input', clonedCell).on('keyup change', function () {
                if (table.column(item.idx).search() !== this.value) {
                    table.column(item.idx).search(this.value).draw();
                }
            });
            // Event to stop sorting when clicking on.
            $('input', clonedCell).on('click', function (e) {
                e.stopPropagation();
            });
        });
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
        let loader = $('.modal-table-wrapper .datatable-loader');
        if (enable) {
            loader.show();
        } else {
            loader.hide();
        }
    }

    const init = function () {
        load_datatable();
    }

    return {
        init: init
    };
});