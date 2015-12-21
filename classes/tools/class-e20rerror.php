<?php
/**
 * Created by PhpStorm.
 * User: sjolshag
 * Date: 12/16/15
 * Time: 12:43 PM
 */

namespace E20R\Sequences\Tools;

class E20RError
{
    private $history = array();
    private static $_this;

    public function __construct()
    {
        if (isset(self::$_this)) {
            wp_die(sprintf(__('%s is a singleton class and you are not allowed to create a second instance', 'e20rsequence'), get_class($this)));
        }

        self::$_this = $this;

        add_filter('get_e20rerror_class_instance', [$this, 'get_instance']);
    }

    private function configure() {

        return $error = array(
            'setting' => null,
            'code' => null,
            'message' => null,
            'type' => null,
        );
    }

    public function get_instance() {

        return self::$_this;
    }

    public function set_error( $message = null, $type = 'error', $code = null, $setting = 'e20r_seq_errors' ) {

        $new = $this->configure();

        $new['setting'] = $setting;
        $new['code'] = $code;
        $new['type'] = $type;
        $new['message'] = $message;

        $this->history[] = $new;

        return true;
    }

    public function get_error( $type = null, $limit = null ) {

        $count = 0;
        if ( is_null( $type )) {

            return $this->history;
        }

        $result = array();

        if ( !is_null($limit)) {

            $count = 0;
        }
        foreach( $this->history as $e ) {

            if ( $type == $e['type'] ) {

                if ( !is_null($limit) && ( $count > $limit )) {
                    // quit since we're above the limit.
                    break;
                }

                $result[] = $e;

                if (!is_null($limit)) {
                    $count++;
                }
            }
        }

        return $result;
    }
}