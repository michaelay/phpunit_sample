# PHPUnit cheatsheet

Things about PHPUnit that I have found useful.

[Reference](http://www.phpunit.de/manual/current/en/installation.html)

# Installation steps 
	sudo pear upgrade PEAR
	sudo pear config-set auto_discover 1
	sudo pear install pear.phpunit.de/PHPUnit
	sudo pear install phpunit/DbUnit
	sudo pear install phpunit/PHPUnit_SkeletonGenerator

# Generate skeleton (optional)
`phpunit-skelgen --test -- [class] [class file] [test class] [test class file]`

	phpunit-skelgen --test -- Calculator src/pear/Calculator.class.php  CalculatorTest test/CalculatorTest.php

# Test case file 
Example: 
Class file: src/pear/Calculator.class.php

	<?php
	class Calculator
	{
	    public function add($a, $b)
	    {
	        return $a + $b;
	    }
	}

Testcase file: tests/pear/CalculatorTest.php 

	<?php

	include("pear/Calculator.class.php");

	class CalculatorTest extends PHPUnit_Framework_TestCase
	{
	    /**
	     * @var Calculator
	     */
	    protected $object;
	
	    /**
	     * Sets up the fixture, for example, opens a network connection.
	     * This method is called before a test is executed.
	     */
	    protected function setUp()
	    {
	        $this->object = new Calculator;
	    }
	
	    /**
	     * Tears down the fixture, for example, closes a network connection.
	     * This method is called after a test is executed.
	     */
	    protected function tearDown()
	    {
	    }
	
	    public function provider()
	    {
	        return array(
	          array(0, 0, 0),
	          array(0, 1, 1),
	          array(1, 0, 1),
	          array(1, 1, 2),
	        );
	    }
	
	    /**
	     * @dataProvider provider 
	     */
	    public function testAdd($a, $b, $c)
	    {
	        $this->assertEquals(
	          $c,
	          $this->object->add($a, $b)
	        );
	    }
	}


# Running test
suggested structure:

* src/pear/xxx
* tests/pear/xxx


`phpunit tests`
	
	$ phpunit tests
	PHPUnit 3.6.10 by Sebastian Bergmann.
	
	Configuration read from /Users/mistralay/Documents/sample/test/tests/phpunit.xml
	
	.....
	
	Time: 0 seconds, Memory: 6.25Mb
	
	OK (5 tests, 7 assertions)

# Fixture
Runs code before and after tests 

* setup - before every test
* tearDown - after every test 
* setupBeforeClass - before the first test in a test case file
* tearDownBeforeClass - after the last test in a test case file 

# Database test  
### DB connection

For DB related test, the test case inherits from `PHPUnit_Extensions_Database_TestCase` instead of `PHPUnit_Framework_TestCase`. It is, however, suggested that to created to a generic database base testcase class for across the product to avoid copying DB connection code everywhere. Sample implementation as below. 

Config file (tests/phpunit.xml):

	<?xml version="1.0" encoding="UTF-8" ?>
	<phpunit>
	    <php>
	        <var name="DB_DSN" value="mysql:dbname=sample;host=localhost" />
	        <var name="DB_USER" value="root" />
	        <var name="DB_PASSWD" value="" />
	        <var name="DB_DBNAME" value="sample" />
	    </php>
	</phpunit>
	

Generic DB test case class (tests/common/):  
	
	<?php
	
	require_once "PHPUnit/Extensions/Database/TestCase.php";
	
	abstract class Generic_Tests_DatabaseTestCase extends PHPUnit_Extensions_Database_TestCase
	{
	    // only instantiate pdo once for test clean-up/fixture load
	    static private $pdo = null;
	
	    // only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
	    private $conn = null;
	
	    final public function getConnection()
	    {
	        if ($this->conn === null) {
	            if (self::$pdo == null) {
	                self::$pdo = new PDO( $GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'] );
	            }
	            $this->conn = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['DB_DBNAME']);
	        }
	
	        return $this->conn;
	    }
	}

### DB data

There are many ways to load test data, consult [http://www.phpunit.de/manual/current/en/database.html](http://www.phpunit.de/manual/current/en/database.html) for details. Below is an example of using MySQL dump. 

	mysqldump --xml -t -u [username] --password=[password] [database] > tests/data/sample.xml

A Test that users the db assertion (tests/pear/UserTest.php)

	<?php
	
	include("pear/User.class.php");
	include("tests/common/GenericTestsDatabaseTestCase.php");
	
	class UserTest extends Generic_Tests_DatabaseTestCase
	{
	    public function getDataSet()
	    {
	        return $this->createMySQLXMLDataSet('tests/data/sample.xml');
	    }
	
	    public function testCreate() {
	        $name = "some name";
	
	        $this->assertEquals(3, $this->getConnection()->getRowCount('users'));
	
	        $this->assertEquals(
	            1,
	            User::create($name)
	        );
	
	        $this->assertEquals(4, $this->getConnection()->getRowCount('users'));
	    }
	}


# Stub and Mock

Replacing methods of class. It does not work on final, protected and private methods.

User class again: 

	<?php 
	
	class User {

		// … 	
	
	    public function getFirstFriend() {
	        $friends = $this->getFriends();
	        return $friends[0];
	    }
	
	    public function getFriends() {
	        // some webservice to get friends, will be stub in the test cases
	        return array('friend1', 'friend2', 'friend3');
	    }

Test case: 

    public function testGetFirstFriend() {

        $fake_friends = array('aaa', 'bbb', 'ccc');

        $stub = $this->getMock('User', array('getFriends'));
        $stub->expects($this->any())
             ->method('getFriends')
             ->will($this->returnValue($fake_friends));

        $friend = $stub->getFirstFriend();
        $this->assertEquals('aaa', $friend);
    }
    
# Good practice 
* data provider 


