<?php

if( !defined('IN_PAGE') ) {
	// No direct access to this PHP file.
	exit();
}


class Session {
	private $maxlifetime;

	/**
	 * Implementation of a Session Manager
	 * credits: http://avedo.net/402/mysql-based-session-mangagement-in-php/
	 */

	/*
	 * Constructor
	 */
	public function __construct($maxlifetime = 0) {
		$this->maxlifetime = time() - (( $maxlifetime == 0 ) ? ini_get("session.gc_maxlifetime") : $maxlifetime);

        // change the ini configuration
        ini_set('session.save_handler', 'user');
     
        // set the session handler to the class methods
        session_set_save_handler(
            array(&$this, '_open'),
            array(&$this, '_close'),
            array(&$this, '_read'),
            array(&$this, '_write'),
            array(&$this, '_destroy'),
            array(&$this, '_gc')
        );
         
        // and start a new session
        session_start();
 
        // finally ensure that the session values are stored
        register_shutdown_function('session_write_close');
    }

    /**
     * Is called to open a session. The method
     * does nothing because we do not want to write
     * into a file so we don't need to open one.
     *
     * @param string $save_path The save path
     * @param string $session_name The name of the session
     * @return boolean
     */
    public function _open($save_path, $session_name) {
        return true;
    }

    /**
     * Is called when the reading in a session is
     * completed. The method calls the garbage collector.
     *
     * @return Boolean
     */
    public function _close() {
        $this->_gc(100);
        return true;
    }

    /**
     * Is called to read data from a session.
     *
     * @param string $id The id of the current session
     * @return mixed
     */
    public function _read($id) {
    	// query to get the session data
		$result = DB::queryFirstRow("SELECT data FROM sessions WHERE id = %s LIMIT 1", $id);

		// if session data found, return it or empty string
		return (( isset($result['data']) ) ? $result['data'] : '');
    }

    /**
     * Writes data into a session rather
     * into the session record in the database.
     *
     * @param string $id The id of the current session
     * @param string $sess_data The data of the session
     * @return boolean
     */
    public function _write($id, $sess_data) {
        // validate the given data
        if( $sess_data == null ) {
            return true;
        }
     
 		$time = time();

		// query to do an (INSERT ... ON DUPLICATE KEY UPDATE) for a session
		// note that the session timestamp won't change, even the data changed
		DB::query("INSERT INTO sessions (id, data, timestamp) VALUES (%s, %?, %i) ON DUPLICATE KEY UPDATE data = IF(timestamp < VALUES(timestamp), VALUES(data), data);", $id, $sess_data, $time);

		return true;
    }

    /**
     * Ends a session and deletes it.
     *
     * @param string $id The id of the current session
     * @return boolean
     */
    public function _destroy($id) {
        // query to delete the current session
        DB::delete('sessions', "id=%s", $id);

        return true;
    }
     
    /**
     * The garbage collector deletes all sessions from the database
     * that where not deleted by the session_destroy function.
     * so your session table will stay clean.
     *
     * @param integer $maxlifetime The maximum session lifetime
     * @return boolean
     */
    public function _gc($maxlifetime) {
    	// query to delete discontinued sessions
    	DB::delete('sessions', "timestamp<%s", $this->maxlifetime);

        return true;
    }
}

?>