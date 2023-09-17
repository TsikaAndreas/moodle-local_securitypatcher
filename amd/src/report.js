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
        'local_securitypatcher/jquery.dataTables', 'local_securitypatcher/dataTables.bootstrap4',
        'local_securitypatcher/dataTables.buttons', 'local_securitypatcher/buttons.bootstrap4',
        'local_securitypatcher/buttons.colVis', 'local_securitypatcher/buttons.html5',
        'local_securitypatcher/buttons.print', 'local_securitypatcher/dataTables.responsive',
        'local_securitypatcher/responsive.bootstrap4'],
    function ($, Ajax, Notification, Repository, DataTable
) {

    function load_datatable() {
        $(document).ready(function () {
            // Initialize dataTable.
            var table = $('#reporttable').DataTable({
                dom: '<l<t>ip>',
                responsive: true,
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 10001, targets: 3 },
                    { responsivePriority: 2, searchable: false, targets: -1 }
                ],
                order: [
                    [0, 'desc']
                ],
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
                $(this).html('<input type="text" placeholder="Filter ' + title + '" style="width: 100%"/>');
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

            // Required (for responsive)
            $(window).resize(function () {
                $('#reporttable thead tr:eq(0) th').each(function () {
                    var display = $(this).css('display');
                    var position = $(this).index();
                    if (display == 'none') {
                        $('thead tr:eq(1)').find('th:eq(' + position + ') ').addClass('hidden');
                    } else {
                        $('thead tr:eq(1)').find('th:eq(' + position + ') ').removeClass('hidden');
                    }
                });
            });

            // For collumn visibility
            $('#reporttable thead tr:eq(0) th').each(function () {
                var display = $(this).css('display');
                var position = $(this).index();
                if (display == 'none') {
                    $('thead tr:eq(1)').find('th:eq(' + position + ') ').addClass('hidden');
                } else {
                    $('thead tr:eq(1)').find('th:eq(' + position + ') ').removeClass('hidden');
                }
            });
        });
    }


    var init = function (params) {

        console.log('test');

        load_datatable();
    }

    return {
        init: init
    };
});