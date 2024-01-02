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
        'core/prefetch', 'core/str',
        'local_securitypatcher/jquery.dataTables', 'local_securitypatcher/dataTables.bootstrap4',
        'local_securitypatcher/dataTables.buttons', 'local_securitypatcher/buttons.bootstrap4',
        'local_securitypatcher/buttons.colVis', 'local_securitypatcher/buttons.html5',
        'local_securitypatcher/buttons.print', 'local_securitypatcher/pdfmake',
        'local_securitypatcher/dataTables.responsive', 'local_securitypatcher/responsive.bootstrap4'],
    function ($, Ajax, Notification, Repository, Prefetch, Str, DataTable
) {
    function load_datatable() {
        $(document).ready(function () {
            // Initialize dataTable.
            var table = $('#reporttable').DataTable({
                dom: 'Brtrip',
                responsive: true,
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, searchable: false, targets: -1, orderable: false },
                    { targets: 1 },
                    { targets: 2 },
                    { targets: 3 },
                    { targets: 4 },
                ],
                order: [
                    [0, 'desc']
                ],
                buttons: [
                    {
                        extend: 'colvis',
                        columns: ':not(.noVis)'
                    },
                    {
                        extend: 'collection',
                        className: 'exportButton',
                        text: 'Export',
                        buttons: [
                            {
                                extend: 'copy',
                                exportOptions: {
                                    columns: ':visible:not(.noVis)',
                                }
                            },
                            {
                                extend: 'print',
                                exportOptions: {
                                    columns: ':visible:not(.noVis)',
                                }
                            },
                            {
                                extend: 'excel',
                                exportOptions: {
                                    columns: ':visible:not(.noVis)',
                                }
                            },
                            {
                                extend: 'pdf',
                                exportOptions: {
                                    columns: ':visible:not(.noVis)',
                                }
                            },
                            {
                                extend: 'csv',
                                exportOptions: {
                                    columns: ':visible:not(.noVis)',
                                }
                            },
                        ]
                    },
                ],
                initComplete: function(settings, json) {
                    datatable_loader(false);
                },
                drawCallback: function() {

                }
            });

            // Place Search Fields in every column.
            $('#reporttable thead tr').clone(true).appendTo('#reporttable thead');
            $('#reporttable thead tr:eq(1) th').each(function (i, node) {
                // Remove the sorting icons near the input.
                var classesArray = $(node).attr('class').split(' ');
                var sortClasses = classesArray.filter(function(className) {
                    return className.includes('sorting');
                });
                $(node).removeClass(sortClasses.join(' '));
                // Add the filters.
                var title = $(this).text();
                $(this).html('<input type="text" placeholder="' + title + '" style="width: 100%"/>');
                $('input', this).on('keyup change', function () {
                    if ($('#reporttable').DataTable().column(i).search() !== this.value) {
                        $('#reporttable').DataTable()
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            });
            // $('#reporttable thead tr:eq(1) th:eq(0) input').remove();
            $('#reporttable thead tr:eq(1) th:last-child').html('');

            // Search on keyup in every column.
            table.columns().every(function () {
                var that = this;
                $('input', this.header()).on('keyup change', function () {
                    if (that.search() !== this.value) {
                        that
                            .search(this.value)
                            .draw();
                    }
                });
            });

            // Event to stop sorting when clicking on
            $('thead tr th input').click(function (e) {
                e.stopPropagation();
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
                var node = this;
                var patch = parseInt(this.getAttribute('data-patch'), 10);

                // Confirmation message.
                var confirmQuestion = Str.get_string('report:patch_confirmdelete', 'local_securitypatcher');
                var confirmButton = Str.get_string('report:patch_confirmdeletebtn', 'local_securitypatcher');

                var confirmCallback = function () {
                    datatable_loader(true);
                    var args = {
                        patchid: patch,
                    };
                    Repository.delete_patch(args)
                        .then(function(res) {
                            if (res.result){
                                table.row(node.closest('tr')).remove().draw();
                            }
                            datatable_loader(false);
                        })
                        .catch(function (error) {
                            datatable_loader(false);
                        });
                };
                show_confirmation(confirmQuestion, confirmButton, confirmCallback);
            });

            // Apply action.
            table.on('click', 'tbody tr button.apply-patch-action', function() {
                var node = this;
                var patch = parseInt(this.getAttribute('data-patch'), 10);

                // Confirmation message.
                var confirmQuestion = Str.get_string('report:patch_confirmapply', 'local_securitypatcher');
                var confirmButton = Str.get_string('report:patch_confirmapplybtn', 'local_securitypatcher');

                var confirmCallback = function () {
                    datatable_loader(true);
                    var args = {
                        patchid: patch,
                    };
                    Repository.restore_patch(args)
                        .then(function(res) {
                            if (res.result){
                                // table.row(node.closest('tr')).remove().draw();
                            }
                            datatable_loader(false);
                        })
                        .catch(function (error) {
                            datatable_loader(false);
                        });
                };
                show_confirmation(confirmQuestion, confirmButton, confirmCallback);
            });

            // Restore action.
            table.on('click', 'tbody tr button.restore-patch-action', function() {
                var node = this;
                var patch = parseInt(this.getAttribute('data-patch'), 10);

                // Confirmation message.
                var confirmQuestion = Str.get_string('report:patch_confirmrestore', 'local_securitypatcher');
                var confirmButton = Str.get_string('report:patch_confirmrestorebtn', 'local_securitypatcher');

                var confirmCallback = function () {
                    datatable_loader(true);
                    var args = {
                        patchid: patch,
                    };
                    Repository.restore_patch(args)
                        .then(function(res) {
                            if (res.result){
                                // table.row(node.closest('tr')).remove().draw();
                            }
                            datatable_loader(false);
                        })
                        .catch(function (error) {
                            datatable_loader(false);
                        });
                };
                show_confirmation(confirmQuestion, confirmButton, confirmCallback);
            });
        });
    }

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

    function prefetch_strings() {
        Prefetch.prefetchStrings('local_securitypatcher', [
            'confirm_title', 'confirm_cancel', 'report:patch_confirmdelete',
            'report:patch_confirmdeletebtn', 'report:patch_confirmapply',
            'report:patch_confirmapplybtn', 'report:patch_confirmrestore',
            'report:patch_confirmrestorebtn'
        ]);
    }

    function datatable_loader(enable) {
        var loader = $('.table-wrapper .datatable-loader');
        if (enable) {
            loader.show();
        } else {
            loader.hide();
        }
    }

    var init = function (params) {
        prefetch_strings();
        load_datatable();
    }

    return {
        init: init
    };
});