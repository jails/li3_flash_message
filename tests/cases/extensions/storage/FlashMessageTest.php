<?php
/**
 * li3_flash_message plugin for Lithium: the most rad php framework.
 *
 * @copyright     Copyright 2010, Michael Hüneburg
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_flash_message\tests\cases\extensions\storage;

use lithium\core\Libraries;
use lithium\storage\Session;
use li3_flash_message\extensions\storage\FlashMessage;

class FlashMessageTest extends \lithium\test\Unit {

	public function setUp() {
		Session::config(array(
			'default' => array(
				'adapter' => 'Memory'
			)
		));
	}

	public function tearDown() {
		Session::delete('default');
		FlashMessage::reset();
	}

	public function testConfig() {
		$result = FlashMessage::config();
		$expected = array(
			'session' => array('config' => 'default', 'base' => null),
			'classes' => array('session' => 'lithium\storage\Session')
		);
		$this->assertEqual($expected, $result);

		FlashMessage::config(array('session' => array('base' => 'message')));
		$result = FlashMessage::config();
		$expected = array(
			'session' => array('config' => 'default', 'base' => 'message'),
			'classes' => array('session' => 'lithium\storage\Session')
		);
		$this->assertEqual($expected, $result);
	}

	public function testReset() {
		FlashMessage::config(array('session' => array('base' => 'message')));
		FlashMessage::reset();
		$result = FlashMessage::config();
		$expected = array(
			'session' => array('config' => 'default', 'base' => null),
			'classes' => array('session' => 'lithium\storage\Session')
		);
		$this->assertEqual($expected, $result);
	}

	public function testWrite() {
		FlashMessage::write('Foo');
		$expected = array('message' => 'Foo', 'attrs' => array());
		$result = Session::read('flash_message');
		$this->assertEqual($expected, $result);

		FlashMessage::write('Foo 2', array('type' => 'notice'));
		$expected = array('message' => 'Foo 2', 'attrs' => array('type' => 'notice'));
		$result = Session::read('flash_message');
		$this->assertEqual($expected, $result);

		FlashMessage::write('Foo 3', array(), 'TestKey');
		$expected = array('message' => 'Foo 3', 'attrs' => array());
		$result = Session::read('TestKey');
		$this->assertEqual($expected, $result);
	}

	public function testRead() {
		FlashMessage::write('Foo');
		$expected = array('message' => 'Foo', 'attrs' => array());
		$result = FlashMessage::read();
		$this->assertEqual($expected, $result);

		FlashMessage::write('Foo 2', array('type' => 'notice'));
		$expected = array('message' => 'Foo 2', 'attrs' => array('type' => 'notice'));
		$result = FlashMessage::read();
		$this->assertEqual($expected, $result);

		FlashMessage::write('Foo 3', array(), 'TestKey');
		$expected = array('message' => 'Foo 3', 'attrs' => array());
		$result = FlashMessage::read('TestKey');
		$this->assertEqual($expected, $result);
	}

	public function testClear() {
		FlashMessage::write('Foo');
		FlashMessage::clear();
		$result = FlashMessage::read();
		$this->assertNull($result);

		FlashMessage::write('Foo 2', array(), 'TestKey');
		FlashMessage::clear('TestKey');
		$result = FlashMessage::read('TestKey');
		$this->assertNull($result);

		FlashMessage::write('Foo 3', array(), 'TestKey2');
		FlashMessage::write('Foo 4', array(), 'TestKey3');
		FlashMessage::clear();
		$result = FlashMessage::read();
		$this->assertNull($result);
	}

	public function testWriteWithBase() {
		FlashMessage::config(array('session' => array('base' => 'message')));
		FlashMessage::write('Foo');
		$expected = array('message' => 'Foo', 'attrs' => array());
		$result = Session::read('message.flash_message');
		$this->assertEqual($expected, $result);

		FlashMessage::write('Foo 2', array('type' => 'notice'));
		$expected = array('message' => 'Foo 2', 'attrs' => array('type' => 'notice'));
		$result = Session::read('message.flash_message');
		$this->assertEqual($expected, $result);

		FlashMessage::write('Foo 3', array(), 'TestKey');
		$expected = array('message' => 'Foo 3', 'attrs' => array());
		$result = Session::read('message.TestKey');
		$this->assertEqual($expected, $result);
	}

	public function testReadWithBase() {
		FlashMessage::config(array('session' => array('base' => 'message')));
		FlashMessage::write('Foo');
		$expected = array('message' => 'Foo', 'attrs' => array());
		$result = FlashMessage::read();
		$this->assertEqual($expected, $result);

		FlashMessage::write('Foo 2', array('type' => 'notice'));
		$expected = array('message' => 'Foo 2', 'attrs' => array('type' => 'notice'));
		$result = FlashMessage::read();
		$this->assertEqual($expected, $result);

		FlashMessage::write('Foo 3', array(), 'TestKey');
		$expected = array('message' => 'Foo 3', 'attrs' => array());
		$result = FlashMessage::read('TestKey');
		$this->assertEqual($expected, $result);
	}

	public function testClearWithBase() {
		FlashMessage::config(array('session' => array('base' => 'message')));
		FlashMessage::write('Foo');
		FlashMessage::clear();
		$result = FlashMessage::read('flash_message');
		$this->assertNull($result);

		FlashMessage::write('Foo 2', array(), 'TestKey');
		FlashMessage::clear('TestKey');
		$result = FlashMessage::read('TestKey');
		$this->assertNull($result);

		FlashMessage::write('Foo 3', array(), 'TestKey2');
		FlashMessage::write('Foo 4', array(), 'TestKey3');
		FlashMessage::clear();
		$result = FlashMessage::read();
		$this->assertNull($result);
	}

	public function testMessageWithParameters() {
		FlashMessage::write('{:name}: the most rad php framework', array('name' => 'Lithium'));
		$result = FlashMessage::read('flash_message');
		$expected = array(
			'message' => 'Lithium: the most rad php framework',
			'attrs' => array('name' => 'Lithium')
		);
		$this->assertEqual($expected, $result);
	}

	public function testArrayOfStringAsMessage() {
		$messages = array(
			'Name can\'t be empty.',
			'Email required',
			'Phone is invalid.'
		);
		FlashMessage::write($messages);

		$expected = array(
			'message' => $messages,
			'attrs' => array()
		);
		$result = FlashMessage::read('flash_message');
		$this->assertEqual($expected, $result);
	}

	public function testNestedArrayAsMessage() {
		$messages = array(
			'name' => array(
				'Name is required.'
			),
			'email' => array(
				'Email can\'t be empty.',
				'Email is invalid.'
			),
			'phone' => array(
				'Invalid phone number.'
			)
		);
		FlashMessage::write($messages);

		$expected = array(
			'message' => $messages,
			'attrs' => array()
		);
		$result = FlashMessage::read('flash_message');
		$this->assertEqual($expected, $result);
	}

	public function testMessageTranslation() {
		$testApp = Libraries::get(true, 'resources') . '/tmp/tests/test_app';
		mkdir($testApp . '/config', 0777, true);

		$body = <<<EOD
<?php
return array(
	'hello' => 'Hello World.',
	'advice' => 'Whatever advice you give, be short.',
	'error' => 'To err is human, but for a real disaster you need a computer.'
);
?>
EOD;
		$filepath = $testApp . '/config/messages.php';
		file_put_contents($filepath, $body);

		Libraries::add('test_app', array('path' => $testApp));

		FlashMessage::config(array('library' => 'test_app'));

		$messages = array('hello', 'advice', 'error');
		FlashMessage::write($messages);

		$expected = array(
			'message' => array(
				'Hello World.',
				'Whatever advice you give, be short.',
				'To err is human, but for a real disaster you need a computer.'
			),
			'attrs' => array()
		);
		$result = FlashMessage::read('flash_message');
		$this->assertEqual($expected, $result);

		$message = 'hello';
		FlashMessage::write($message);

		$expected = array(
			'message' => 'Hello World.',
			'attrs' => array()
		);
		$result = FlashMessage::read('flash_message');
		$this->assertEqual($expected, $result);

		$this->_cleanUp();
	}
}

?>