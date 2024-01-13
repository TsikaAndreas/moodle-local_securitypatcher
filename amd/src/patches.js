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
        'core/prefetch', 'core/str', 'core/modal_factory',
        'local_securitypatcher/jquery.dataTables', 'local_securitypatcher/dataTables.bootstrap4',
        'local_securitypatcher/dataTables.buttons', 'local_securitypatcher/buttons.bootstrap4',
        'local_securitypatcher/buttons.colVis', 'local_securitypatcher/buttons.html5',
        'local_securitypatcher/buttons.print', 'local_securitypatcher/pdfmake',
        'local_securitypatcher/dataTables.responsive', 'local_securitypatcher/responsive.bootstrap4'],
    function ($, Ajax, Notification, Repository, Prefetch, Str, ModalFactory, DataTable
) {
    function load_datatable() {
        $(document).ready(function () {
            // Initialize dataTable.
            var table = $('#patchestable').DataTable({
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
                    [1, 'desc']
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
                    add_column_filters(this.api(), settings.aoColumns);
                },
                drawCallback: function() {

                }
            });

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
                var confirmQuestion = Str.get_string('patches:patch_confirmdelete', 'local_securitypatcher');
                var confirmButton = Str.get_string('patches:patch_confirmdeletebtn', 'local_securitypatcher');

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
                var confirmQuestion = Str.get_string('patches:patch_confirmapply', 'local_securitypatcher');
                var confirmButton = Str.get_string('patches:patch_confirmapplybtn', 'local_securitypatcher');

                var confirmCallback = function () {
                    datatable_loader(true);
                    var args = {
                        patchid: patch,
                    };
                    Repository.apply_patch(args)
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
                var confirmQuestion = Str.get_string('patches:patch_confirmrestore', 'local_securitypatcher');
                var confirmButton = Str.get_string('patches:patch_confirmrestorebtn', 'local_securitypatcher');

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

            // View action.
            table.on('click', 'tbody tr button.view-patch-action', function() {
                var patch = parseInt(this.getAttribute('data-patch'), 10);

                datatable_loader(true);
                var args = {
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
                    .catch(function (error) {
                        datatable_loader(false);
                    });
            });
        });
    }

    function add_column_filters(table, columns) {
        $('#patchestable thead tr').clone(true).appendTo('#patchestable thead');

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

            clonedCell.html('<input class="text-center w-100" type="text" placeholder="' + title + '"/>');
            $('input', clonedCell).on('keyup change', function () {
                if (table.column(item.idx).search() !== this.value) {
                    table.column(item.idx).search(this.value).draw();
                }
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

    /**
     * Creates and displays a modal with specified title and content asynchronously.
     *
     * @param {string} title The title for the modal.
     * @param {string} content The html content/body of the modal.
     */
    async function display_patch_content(title, content) {
        let modal = await ModalFactory.create({
            title: title,
            body: content,
            large: true,
        });
        datatable_loader(false);
        modal.show();
    }

    function prefetch_strings() {
        Prefetch.prefetchStrings('local_securitypatcher', [
            'confirm_title', 'confirm_cancel', 'patches:patch_confirmdelete',
            'patches:patch_confirmdeletebtn', 'patches:patch_confirmapply',
            'patches:patch_confirmapplybtn', 'patches:patch_confirmrestore',
            'patches:patch_confirmrestorebtn'
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