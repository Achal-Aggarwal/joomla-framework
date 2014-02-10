<?php
/**
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Filesystem\Path;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;

/**
 * Tests for the Path class.
 *
 * @since  1.0
 */
class PathTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Data provider for testClean() method.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getCleanData()
	{
		return array(
			// Input Path, Directory Separator, Expected Output
			'Nothing to do.' => array('/var/www/foo/bar/baz', '/', '/var/www/foo/bar/baz'),
			'One backslash.' => array('/var/www/foo\\bar/baz', '/', '/var/www/foo/bar/baz'),
			'Two and one backslashes.' => array('/var/www\\\\foo\\bar/baz', '/', '/var/www/foo/bar/baz'),
			'Mixed backslashes and double forward slashes.' => array('/var\\/www//foo\\bar/baz', '/', '/var/www/foo/bar/baz'),
			'UNC path.' => array('\\\\www\\docroot', '\\', '\\\\www\\docroot'),
			'UNC path with forward slash.' => array('\\\\www/docroot', '\\', '\\\\www\\docroot'),
			'UNC path with UNIX directory separator.' => array('\\\\www/docroot', '/', '/www/docroot'),
		);
	}

	/**
	 * Test the canChmod method.
	 *
	 * @return void
	 */
	public function testCanChmod()
	{
		$data = 'Lorem ipsum dolor sit amet';
		File::write(__DIR__ . '/tempFile', $data);

		$this->assertThat(
			Path::canChmod(__DIR__ . '/tempFile'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' Web user can modify permission of his own file.'
		);

		File::delete(__DIR__ . '/tempFile');

		$this->assertThat(
			Path::canChmod('/'),
			$this->isFalse(),
			'Line:' . __LINE__ . ' Web user can\'t modify permission of root user files.'
		);
	}

	/**
	 * Test the setPermission method.
	 *
	 * @return void
	 */
	public function testSetPermissions()
	{
		$this->assertThat(
			Path::setPermissions(__DIR__ . '/tempFile'),
			$this->isFalse(),
			'Line:' . __LINE__ . ' Setting permission of a non-existing file should fail.'
		);

		$data = 'Lorem ipsum dolor sit amet';
		File::write(__DIR__ . '/tempFile', $data);
		$this->assertThat(
			Path::setPermissions(__DIR__ . '/tempFile'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' Setting permission of existing file should not fail.'
		);
		File::delete(__DIR__ . '/tempFile');

		Folder::create(__DIR__ . '/tempFolder');
		$this->assertThat(
			Path::setPermissions(__DIR__ . '/tempFolder'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' Setting permission of existing folder should not fail.'
		);
		Folder::delete(__DIR__ . '/tempFolder');

		Folder::create(__DIR__ . '/tempFolder');
		Folder::create(__DIR__ . '/tempFolder/tempSubFolder');
		File::write(__DIR__ . '/tempFolder/tempFile', $data);
		$this->assertThat(
			Path::setPermissions(__DIR__ . '/tempFolder'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' Setting permission of existing folder recursively should not fail.'
		);
		Folder::delete(__DIR__ . '/tempFolder');
	}

	/**
	 * Test the testGetPermissions method.
	 *
	 * @return void
	 */
	public function testGetPermissions()
	{
		$data = 'Lorem ipsum dolor sit amet';
		File::write(__DIR__ . '/tempFile', $data);

		$this->assertEquals(
			Path::getPermissions(__DIR__ . '/tempFile'),
			"rw-rw-r--",
			'Line:' . __LINE__ . ' Web user can modify permission of his own file.'
		);

		File::delete(__DIR__ . '/tempFile');
	}

	/**
	 * Test the check mthod.
	 *
	 * @return void
	 *
	 * @expectedException  Exception
	 */
	public function testCheck()
	{
		$this->assertEquals(
			Path::check(__DIR__),
			__DIR__,
			'Line:' . __LINE__ . ' Already clean path should be returned as it'
		);

		$this->assertInstanceOf(
			'Exception',
			Path::check(__DIR__ . '/..'),
			'Line:' . __LINE__ . ' Use of realtive path should throw an exception'
		);
	}

	/**
	 * Test the check method with out of bound path.
	 *
	 * @return void
	 *
	 * @expectedException  Exception
	 */
	public function testCheckOutOfBoundPath()
	{
		$this->assertInstanceOf(
			'Exception',
			Path::check('/home'),
			'Line:' . __LINE__ . ' Snooping out of bound path should throw exception.'
		);
	}

	/**
	 * Tests the clean method.
	 *
	 * @param   string  $input     @todo
	 * @param   string  $ds        @todo
	 * @param   string  $expected  @todo
	 *
	 * @return  void
	 *
	 * @covers        Joomla\Filesystem\Path::clean
	 * @dataProvider  getCleanData
	 * @since      1.0
	 */
	public function testClean($input, $ds, $expected)
	{
		$this->assertEquals(
			$expected,
			Path::clean($input, $ds)
		);
	}

	/**
	 * Tests the JPath::clean method with an array as an input.
	 *
	 * @return  void
	 *
	 * @expectedException  UnexpectedValueException
	 */
	public function testCleanArrayPath()
	{
		Path::clean(array('/path/to/folder'));
	}

	/**
	 * Test the isOwner method.
	 *
	 * @return void
	 */
	public function testIsOwner()
	{
		$this->assertThat(
			Path::isOwner('/home'),
			$this->isFalse()
		);
	}

	/**
	 * Test the find method.
	 *
	 * @todo Implement testFind().
	 *
	 * @return void
	 */
	public function testFind()
	{
		$data = 'Lorem ipsum dolor sit amet';
		File::write(__DIR__ . '/tempFile', $data);

		$this->assertEquals(
			Path::find(__DIR__, 'tempFile'),
			__DIR__ . '/tempFile'
		);

		$this->assertThat(
			Path::find(__DIR__, 'tFile'),
			$this->isFalse()
		);

		File::delete(__DIR__ . '/tempFile');
	}
}
