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
 * Server Side Processing class for the local_securitypatcher plugin.
 *
 * @package   local_securitypatcher
 * @copyright 2024 onwards Andrei-Robert Țîcă <andreastsika@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_securitypatcher\datatables;

use stdClass;

/**
 * Class for server side processing of DataTables data.
 *
 * @package    local_securitypatcher
 * @copyright  2024 onwards Andrei-Robert Țîcă <andreastsika@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ssp {
    /**
     * @var array The request array with the datatable data.
     */
    protected array $request;

    /**
     * @var int The draw number.
     */
    protected int $draw;

    /**
     * @var array Columns array that contains all columns sent.
     */
    protected array $columns;

    /**
     * @var mixed Search array that contains value of the global search.
     */
    protected $search;

    /**
     * @var mixed Order array that contains direction and column name.
     */
    protected $order;

    /**
     * @var mixed The offset.
     */
    protected $start;

    /**
     * @var mixed The limit.
     */
    protected $length;

    /**
     * @var array Filters array that contains all filters sent.
     */
    protected array $filters = [];

    /**
     * @var array Global search array that contains the search value sent.
     */
    protected array $globalfilters = [];

    /**
     * @var string The string that contains the SQL for counting.
     */
    protected string $countsql;

    /**
     * @var string The string that contains the main SQL.
     */
    protected string $mainsql;

    /**
     * @var string The string that contains the where SQL.
     */
    protected string $wheresql = '';

    /**
     * @var array The array that contains the where parameters.
     */
    protected array $whereparams = [];

    /**
     * @var array The array that contains the column type mapping.
     */
    protected array $columntypemap;

    /**
     * Initialise with the request data.
     *
     * @param array $request The request array with the datatable data.
     * @return void
     */
    public function init(array $request): void {
        $this->request = $request;
        $this->draw = $request['draw'];
        $this->columns = $request['columns'];
        $this->search = $request['search'];
        $this->order = $request['order'];
        $this->start = $request['start'];
        $this->length = $request['length'];

        // We get the filters by the columns name.
        foreach ($this->columns as $column) {
            if (!isset($column['search']['value']) || $column['search']['value'] === '') {
                continue;
            }

            if ($column['searchable'] === 'false') {
                continue;
            }

            $this->filters[$column['name']] = $column['search']['value'];
        }

        if (!empty($this->search['value'])) {
            foreach ($this->columns as $column) {
                if (empty($column['name'])) {
                    continue;
                }

                if ($column['searchable'] === false) {
                    continue;
                }

                $this->globalfilters[$column['name']] = $this->search['value'];
            }
        }
    }

    /**
     * Set the count sql needed for the total rows number.
     *
     * @param string $sql
     * @return void
     */
    public function set_countsql(string $sql): void {
        $this->countsql = $sql;
    }

    /**
     * Set the main sql used to return all needed data to the DataTable.
     *
     * @param string $sql
     * @return void
     */
    public function set_mainsql(string $sql): void {
        $this->mainsql = $sql;
    }

    /**
     * Set the where sql clause to the DataTable.
     *
     * @param string $sql
     * @return void
     */
    public function set_wheresql(string $sql): void {
        $this->wheresql = $sql;
    }

    /**
     * Set the where clause parameters for the DataTable.
     *
     * @param array $params
     * @return void
     */
    public function set_whereparams(array $params): void {
        $this->whereparams = $params;
    }

    /**
     * Sets the column type map for data processing.
     *
     * @param array $map An associative array where keys represent column names
     *                   and values represent their corresponding data types.
     * @return void
     */
    public function set_column_type_map(array $map): void {
        $this->columntypemap = $map;
    }

    /**
     * This returns the object that needs to be json encoded and is required
     * by javascript to render the DataTable.
     *
     * @return stdClass
     */
    public function result(): stdClass {
        global $DB;

        $params = [];

        // Build the order by sql.
        $ordersql = '';

        if (!empty($this->order)) {
            $orders = [];

            // Get the names of the columns we want to order by.
            foreach ($this->order as $order) {
                $column = $order['column'];
                $dir = $order['dir'];
                $columnname = $this->columns[$column]['name'];
                $orders[] = $DB->sql_compare_text($columnname) . " {$dir} ";
            }

            if (!empty($orders)) {
                $ordersql = "ORDER BY " . implode(',', $orders);
            }
        }

        // Get total results count.
        $recordstotal = $DB->count_records_sql($this->countsql);

        // Build the filter sql.
        $filtersql = '';

        if (!empty($this->filters)) {
            $filtersql .= !empty($this->wheresql) ? ' AND (' : ' (';
            $i = 1;

            foreach ($this->filters as $columnname => $value) {
                if ($i > 1) {
                    $filtersql .= ' AND ';
                }
                $this->add_search_query($columnname, $value, $i, $filtersql, $params, false);
                $i++;
            }

            $filtersql .= ' )';
        }

        if (!empty($this->globalfilters)) {
            $filtersql .= (!empty($filtersql) && !empty($this->wheresql)) ? ' AND (' : ' (';
            $i = 1;

            foreach ($this->globalfilters as $columnname => $value) {
                if ($i > 1) {
                    $filtersql .= ' OR ';
                }

                $this->add_search_query($columnname, $value, $i, $filtersql, $params, true);
                $i++;
            }

            $filtersql .= ' )';
        }

        // Build the where sql.
        $wheresql = (!empty($this->wheresql) || !empty($filtersql)) ? 'WHERE ' : '';

        if (!empty($this->wheresql) && !empty($this->whereparams)) {
            $wheresql .= "($this->wheresql)";
            foreach ($this->whereparams as $wherekey => $wherevalue) {
                $whereparam = ':where' . $wherekey;
                $wheresql = preg_replace('/\?/', $whereparam, $wheresql, 1);
                $params["where{$wherekey}"] = $wherevalue;
            }
        }

        $sql = $this->mainsql . " {$wheresql} {$filtersql} {$ordersql}";
        $filteredsql = $this->countsql . " {$wheresql} {$filtersql}";
        $recordsfiltered = $DB->get_records_sql($sql, $params, $this->start, $this->length);
        $recordsfilteredtotal = $DB->count_records_sql($filteredsql, $params);
        $data = array_values($recordsfiltered);

        $obj = new stdClass();
        $obj->draw = $this->draw;
        $obj->recordsTotal = $recordstotal;
        $obj->recordsFiltered = $recordsfilteredtotal;
        $obj->data = $data;

        return $obj;
    }

    /**
     * Add a search query for a specific column to the SQL query.
     *
     * @param string $columnname The name of the column to search.
     * @param string $value The value to search for.
     * @param int $i The search index to distinguish multiple search conditions.
     * @param string &$sql The SQL query to which the search condition will be added.
     * @param array &$params The array of parameters used in the SQL query.
     * @param bool $isglobal Whether the search is global or not. Defaults to false.
     *
     * @return void
     */
    private function add_search_query(string $columnname, string $value, int $i, string &$sql, array &$params,
            bool $isglobal = false): void {
        global $DB;

        $search = $isglobal ? 'gsearch' : 'search';

        if (array_key_exists($columnname, $this->columntypemap)) {
            $type = $this->columntypemap[$columnname];

            switch ($type) {
                case 'int':
                    $sql .= $DB->sql_like("LOWER({$columnname})", ":{$search}{$i}", false, false);
                    $params["{$search}{$i}"] = mb_strtolower($value);
                    break;
                case 'timestamp':
                    $sql .= "(LOWER({$columnname}) >= :{$search}datefrom{$i} AND LOWER({$columnname}) < :{$search}dateto{$i})";
                    $params["{$search}datefrom{$i}"] = strtotime("midnight", strtotime($value));
                    $params["{$search}dateto{$i}"] = strtotime("tomorrow", strtotime($value)) - 1;
                    break;
                default:
                    $sql .= $DB->sql_like("LOWER({$columnname})", ":{$search}{$i}", false, false);
                    $params["{$search}{$i}"] = '%' . mb_strtolower($value) . '%';
                    break;
            }
        } else {
            $sql .= $DB->sql_like("LOWER({$columnname})", ":{$search}{$i}", false, false);
            $params["{$search}{$i}"] = '%' . mb_strtolower($value) . '%';
        }
    }
}
