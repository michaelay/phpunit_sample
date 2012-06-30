<?php 

include("pear/User.class.php");
include("tests/common/GenericTestsDatabaseTestCase.php"); 

class UserTest extends Generic_Tests_DatabaseTestCase
{
    protected $user;

    protected function setUp() { 
        parent::setUp();
        $this->user = new User(); 
    } 

    protected function tearDown() { 
        parent::tearDown();
    } 

    // called once at tearDown of parent class
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

    public function testGetFirstFriend() { 

        $fake_friends = array('aaa', 'bbb', 'ccc');

        $stub = $this->getMock('User', array('getFriends'));
        $stub->expects($this->any())
             ->method('getFriends')
             ->will($this->returnValue($fake_friends));

        $friend = $stub->getFirstFriend();
        $this->assertEquals('aaa', $friend);
    } 
} 
