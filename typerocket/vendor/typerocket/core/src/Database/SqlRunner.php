<?php
namespace TypeRocket\Database;

use TypeRocket\Exceptions\SqlException;
use TypeRocket\Utility\Str;

class SqlRunner
{
    protected $query_prefix_tag = '{!!prefix!!}';

    public function runQueryFile($file_sql, $callback = null, $cb_data = null) {
        if( ! file_exists( $file_sql ) ) {
            throw new \Exception('Not Found: SQL '. $file_sql .' failed to run.');
            return;
        }

        $sql = file_get_contents($file_sql);
        $this->runQueryString( $sql, $callback, $cb_data );
    }

    public function runQueryString($sql, $callback = null, $cb_data = null) {
        /** @var \wpdb $wpdb */
        global $wpdb;
        $prefix = $wpdb->prefix;
        $prefixed = str_replace($this->query_prefix_tag, $prefix, $sql);
        return $this->runQueryArray( explode(';'.PHP_EOL, $prefixed ), $callback, $cb_data );
    }

    public function runQueryArray($queries, $callback = null, $cb_data = null) {
        /** @var \wpdb $wpdb */
        global $wpdb;
        // $wpdb->show_errors();
        $succesess = [];

        foreach ($queries as $query) {

            $RXSQLComments = '(--.*)';
            $query = ( ($query == '') ?  '' : trim(preg_replace( $RXSQLComments, '', $query )));

            if( Str::starts('create table', strtolower($query)) ) {

                if(!function_exists('dbDelta')) {
                    include ABSPATH . WPINC . '/upgrade.php';
                }

                $result = dbDelta($query);
            } elseif( !empty(trim($query)) ) {
                $result = $wpdb->query( $query );
            } else {
                continue;
            }

            if ( !$result ) {
                $e = new SqlException('Query Error: SQL failed to run.');
                $e->setSql($wpdb->last_query);
                $e->setSqlError($wpdb->last_error);
                $wpdb->last_error = '';
                throw $e;
            }

            $result = [
                'message' => 'SQL successfully run.',
                'result' => $result,
                'wpdb' => $wpdb->last_query
            ];

            if(is_callable($callback)) {
                $callback($result, $cb_data);
            }

            $succesess[] = $result;
        }

        return $succesess;
    }
}