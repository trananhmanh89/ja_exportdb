<?php
/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Tests;

use Joomla\Database\Mysql\MysqlDriver;
use Joomla\Database\Mysqli\MysqliDriver;
use Joomla\Test\TestDatabase;
use Joomla\Database\DatabaseDriver;

/**
 * Abstract test case class for MySQLi database testing.
 *
 * @since  1.0
 */
abstract class DatabaseMysqliCase extends TestDatabase
{
	/**
	 * @var    array  The database driver options for the connection.
	 * @since  1.0
	 */
	private static $options = array('driver' => 'mysqli');

	/**
	 * This method is called before the first test of this test class is run.
	 *
	 * An example DSN would be: host=localhost;dbname=joomla_ut;user=utuser;pass=ut1234
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function setUpBeforeClass()
	{
		// First let's look to see if we have a DSN defined or in the environment variables.
		if (\defined('JTEST_DATABASE_MYSQLI_DSN') || getenv('JTEST_DATABASE_MYSQLI_DSN'))
		{
			$dsn = \defined('JTEST_DATABASE_MYSQLI_DSN') ? JTEST_DATABASE_MYSQLI_DSN : getenv('JTEST_DATABASE_MYSQLI_DSN');
		}
		else
		{
			return;
		}

		// Make sure the driver is supported, we check both PDO MySQL and MySQLi here due to PHPUnit requiring a PDO connection to set up the test
		if (!MysqlDriver::isSupported() || !MysqliDriver::isSupported())
		{
			static::markTestSkipped('The PDO MySQL or MySQLi driver is not supported on this platform.');
		}

		// First let's trim the mysql: part off the front of the DSN if it exists.
		if (strpos($dsn, 'mysql:') === 0)
		{
			$dsn = substr($dsn, 6);
		}

		// Split the DSN into its parts over semicolons.
		$parts = explode(';', $dsn);

		// Parse each part and populate the options array.
		foreach ($parts as $part)
		{
			list ($k, $v) = explode('=', $part, 2);

			switch ($k)
			{
				case 'host':
					self::$options['host'] = $v;
					break;
				case 'port':
					self::$options['port'] = $v;
					break;
				case 'dbname':
					self::$options['database'] = $v;
					break;
				case 'user':
					self::$options['user'] = $v;
					break;
				case 'pass':
					self::$options['password'] = $v;
					break;
			}
		}

		try
		{
			// Attempt to instantiate the driver.
			static::$driver = DatabaseDriver::getInstance(self::$options);
		}
		catch (\RuntimeException $e)
		{
			static::$driver = null;
		}
	}

	/**
	 * This method is called after the last test of this test class is run.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function tearDownAfterClass()
	{
		if (static::$driver !== null)
		{
			static::$driver->disconnect();
			static::$driver = null;
		}
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  \PHPUnit_Extensions_Database_DataSet_XmlDataSet
	 *
	 * @since   1.0
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/Stubs/database.xml');
	}

	/**
	 * Returns the default database connection for running the tests.
	 *
	 * @return  \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
	 *
	 * @since   1.0
	 */
	protected function getConnection()
	{
		// Compile the connection DSN.
		$dsn = 'mysql:host=' . self::$options['host'] . ';dbname=' . self::$options['database'];

		if (isset(self::$options['port']))
		{
			$dsn .= ';port=' . self::$options['port'];
		}

		// Create the PDO object from the DSN and options.
		$pdo = new \PDO($dsn, self::$options['user'], self::$options['password']);

		return $this->createDefaultDBConnection($pdo, self::$options['database']);
	}
}
