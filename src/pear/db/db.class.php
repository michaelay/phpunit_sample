<?php
/***************************************************************************
 *	Package  	: MySQL Database Class
 *	Version  	: 1.0
 *	Date		: 05/10/2009
 *	Copyright	: (C) 2009 Studio77D
 *	Author   	: Rostislav Stoyanov
 *	Email    	: stoyanovrr@gmail.com
 *	Site     	: http://www.studio77d.com/
 *	File     	: db.class.php
 *	Usage		: http://www.2smart4you.net/php-mysql-database-connection-class/
 *
 *	License:
 *	This class is dual-licensed under the GNU General Public License and the MIT License and
 *	is copyright (C) 2009 Studio77D.
 * 
 *	This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ***************************************************************************/

class DBConnection {
	/**
	 * 	Connection link.
	 * 	@var string
	 */
	private $connection;
	
	/**
	 * Keeps the info of the last used connection.
	 * @var string
	 */
	private $last_connection=null;
	
	/**
	 * Keeps the info of the last used MySQL query.
	 * @var string
	 */
	private $msql='';
	
	/**
	 * Returns the text of the error message from last MySQL operation.
	 * @var string
	 */
	private $error='';
	
	/**
	 * Returns the numerical value of the error message from last MySQL operation.
	 * @var integer
	 */
	private $errno='';
	
	/**
	 * Is there any locked tables right now?
	 * @var boolean
	 */
	private $is_locked=false;
	
	/**
	 * The Constructor. Initializes a database connection and selects database.
	 * @param  string  Database host
	 * @param  string  Database username
	 * @param  string  Database password
	 * @param  string  Database name
	 *
	 * @return boolean
	 */
	function DBConnection($db_host='', $db_user='', $db_pass='', $db_name='') {
		$this->connection=mysql_connect($db_host, $db_user, $db_pass);
		
		if ($this->connection){
			if (mysql_select_db($db_name, $this->connection)){
				$this->last_connection=&$this->connection;
				return $this->connection;
			}else{ // if we can't select the database
				$this->display_errors('- Could not select database: '.$db_name.'');
				return false;
			}
		}else{ // if we couldn't connect to the database
			$this->display_errors('- Could not connect to database: '.$db_name.'');
			return false;
		}
	}
	
	/**
	 * Send a MySQL query.
	 * @param  string  Query to run
	 * @return mixed
	 */
	function rq($msql) {
		$this->last_connection=&$this->connection;
		$this->msql=&$msql;
		$result=mysql_query($msql, $this->connection);
		if ($result){
			$this->queries_count++;
			return $result;
		}else{
			$this->display_errors();
			return false;
		}
	}
	
	/**
	 * Fetch a result row as an associative array.
	 * @param  string  The query which we send.
	 * @return array
	 */
	function fetch($query) {
		return mysql_fetch_assoc($query);
	}
	
	/**
	 * Fetch a result row as an object
	 * @param  string  The query which we send.
	 * @return array
	 */
	function ofetch($query) {
		return mysql_fetch_object($query);
	}
	
	/**
	 * Fetch a result row as an associative array, a numeric array, or both.
	 * @param  string  The query which we send.
	 * @return array
	 */
	function afetch($query) {
		return mysql_fetch_array($query);
	}
	
	/**
	 * Returns the number of rows from the executed query.
	 * @param  string  The query result.
	 * @return integer
	 */
	function num_rows($result) {
		return mysql_num_rows($result);
	}
	
	/**
	 * Retuns the number of rows affected bt last used query.
	 * @return integer
	 */
	function affected_rows() {
		return mysql_affected_rows($this->last_connection);
	}
	
	/**
	 * Returns the total number of executed queries. Usually goes to the end of scripts.
	 * @return integer
	 */
	function num_queries() {
		return $this->queries_count;
	}
	
	/**
	 * Lock database table(s).
	 * @param   array  Array of table => Lock type
	 * @return  void
	 */

	function lock_tables($tables) {
		if (is_array($tables)&&count($tables)>0){
			$msql='';
			
			foreach ($tables as $name=>$type){
				$msql.=(!empty($msql)?', ':'').''.$name.' '.$type.'';
			}
			
			$this->rq('LOCK TABLES '.$msql.'');
			$this->is_locked=true;
		}
	}
	
	/* Unlock database table(s) */
	function unlock_tables() {
		if ($this->is_locked){
			$this->rq('UNLOCK TABLES');
			$this->is_locked=false;
		}
	}
	
	/**
	 * Returns the last unique ID (auto_increment field) from the last inserted row.
	 * @return  integer
	 */
	function last_id() {
		return mysql_insert_id($this->connection);
	}
	
	/**
	 * Escapes a value to make it safe for using in queries.
	 * @param  string  String to be escaped
	 * @param  bool    If escaping of % and _ is also needed
	 * @return string
	 */
	function string_escape($string, $full_escape=false) {
		$string=stripslashes($string);
		
		if ($full_escape) $string=str_replace(array('%', '_'), array('\%', '\_'), $string);
		
		if (function_exists('mysql_real_escape_string')){
			return mysql_real_escape_string($string, $this->connection);
		}else{
			return mysql_escape_string($string);
		}
	}
	
	/**
	 * Free result memory.
	 * @param  string   The result which we want to release.
	 * @return boolean
	 */
	function free_result($result) {
		return mysql_free_result($result);
	}
	
	/**
	 * Closes the MySQL connection.
	 * @param  none
	 * @return boolean
	 */
	function close() {
		$this->msql='';
		return mysql_close($this->connection);
	}
	
	/**
	 * Returns the MySQL error message.
	 * @return string
	 */
	function error() {
		$this->error=(is_null($this->last_connection))?'':mysql_error($this->last_connection);
		return $this->error;
	}
	
	/**
	 * Returns the MySQL error number.
	 * @return string
	 */
	function errno() {
		$this->errno=(is_null($this->last_connection))?0:mysql_errno($this->last_connection);
		return $this->errno;
	}
	
	/**
	 * If database error occur, the script will be stopped and an error message displayed.
	 * @param  string  The error message. If it's empty, it will be created with $this->sql.
	 * @return string
	 */
	function display_errors($error_message='') {
		if ($this->last_connection){
			$this->error=$this->error($this->last_connection);
			$this->errno=$this->errno($this->last_connection);
		}
		
		/** CHECK $_SERVER  to add more details **/
		if(!$error_message) $error_message='- Error in query: '.$this->msql;
		
		$message=''.$error_message.'<br />
		'.(($this->errno!='')?'- Error: '.$this->error.' (Error #'.$this->errno.')<br />':'').'
		- File: '.$_SERVER['SCRIPT_FILENAME'].'<br />';
		
		die('Database problem occur, please try again later.<br />'.$message.'');
	}
	
	/*** Shortcut functions ***/
	/**
	 * For fetching single row with possibility to enter conditions
	 *
	 * @param string	From which table(s) to get result
	 * @param string	What conditions to use
	 * @param string	Which field to fetch. Very usable if you need to make JOINs
	 *
	 * @return mixed
	 */
	function getRow($table, $conditions='1', $select_what='*') {
		$query='SELECT '.$select_what.' FROM '.$table.' WHERE '.$conditions;
		$res=$this->rq($query);
		$row=$this->fetch($res);
		return $row;
	}
}
?>