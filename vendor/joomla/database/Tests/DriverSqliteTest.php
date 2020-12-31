<?php
/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Tests;

/**
 * Test class for Joomla\Database\Sqlite\SqliteDriver.
 *
 * @since  1.0
 */
class DriverSqliteTest extends DatabaseSqliteCase
{
	/**
	 * Data for the testEscape test.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function dataTestEscape()
	{
		return array(
			array("'%_abc123", false, "''%_abc123"),
			array("'%_abc123", true, "''%_abc123"),
			array(3, false, 3)
		);
	}

	/**
	 * Data for the testQuoteBinary test.
	 *
	 * @return  array
	 *
	 * @since   1.7.0
	 */
	public function dataTestQuoteBinary()
	{
		return array(
			array('DATA', "X'" . bin2hex('DATA') . "'"),
			array("\x00\x01\x02\xff", "X'000102ff'"),
			array("\x01\x01\x02\xff", "X'010102ff'"),
		);
	}

	/**
	 * Data for the testQuoteName test.
	 *
	 * @return  array
	 *
	 * @since   1.7.0
	 */
	public function dataTestQuoteName()
	{
		return array(
			array('protected`title', null, '`protected``title`'),
			array('protected"title', null, '`protected"title`'),
			array('protected]title', null, '`protected]title`'),
		);
	}

	/**
	 * Data for the testTransactionRollback test.
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function dataTestTransactionRollback()
	{
		return array(array(null, 0), array('transactionSavepoint', 1));
	}

	/**
	 * Test __destruct method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function test__destruct()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test connected method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testConnected()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Tests the dropTable method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testDropTable()
	{
		$this->assertThat(
			self::$driver->dropTable('#__bar', true),
			$this->isInstanceOf('\\Joomla\\Database\\Sqlite\\SqliteDriver'),
			'The table is dropped if present.'
		);
	}

	/**
	 * Tests the escape method.
	 *
	 * @param   string   $text      The string to be escaped.
	 * @param   boolean  $extra     Optional parameter to provide extra escaping.
	 * @param   string   $expected  The expected result.
	 *
	 * @return  void
	 *
	 * @dataProvider  dataTestEscape
	 * @since         1.0
	 */
	public function testEscape($text, $extra, $expected)
	{
		$this->assertThat(
			self::$driver->escape($text, $extra),
			$this->equalTo($expected),
			'The string was not escaped properly'
		);
	}

	/**
	 * Test the quoteBinary method.
	 *
	 * @param   string  $data  The binary quoted input string.
	 *
	 * @return  void
	 *
	 * @dataProvider  dataTestQuoteBinary
	 * @since         1.7.0
	 */
	public function testQuoteBinary($data, $expected)
	{
		$this->assertThat(
			self::$driver->quoteBinary($data),
			$this->equalTo($expected),
			'The binary data was not quoted properly'
		);
	}

	/**
	 * Test the quoteName method.
	 *
	 * @param   string  $text      The column name or alias to be quote.
	 * @param   string  $asPart    String used for AS query part.
	 * @param   string  $expected  The expected result.
	 *
	 * @return  void
	 *
	 * @dataProvider  dataTestQuoteName
	 * @since         1.7.0
	 */
	public function testQuoteName($text, $asPart, $expected)
	{
		$this->assertThat(
			self::$driver->quoteName($text, $asPart),
			$this->equalTo($expected),
			'The name was not quoted properly'
		);
	}

	/**
	 * Test the execute method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testExecute()
	{
		self::$driver->setQuery("REPLACE INTO `jos_dbtest` (`id`, `title`) VALUES (5, 'testTitle')");

		$this->assertThat(self::$driver->execute(), $this->isInstanceOf('\\PDOStatement'), __LINE__);

		$this->assertThat(self::$driver->insertid(), $this->equalTo(5), __LINE__);
	}

	/**
	 * Test getAffectedRows method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetAffectedRows()
	{
		$query = self::$driver->getQuery(true);
		$query->delete();
		$query->from('jos_dbtest');
		self::$driver->setQuery($query);

		self::$driver->execute();

		$this->assertThat(self::$driver->getAffectedRows(), $this->equalTo(4), __LINE__);
	}

	/**
	 * Test getExporter method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @todo    Implement testGetExporter().
	 */
	public function testGetExporter()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('Implement this test when the exporter is added.');
	}

	/**
	 * Test getImporter method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @todo    Implement testGetImporter().
	 */
	public function testGetImporter()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('Implement this test when the importer is added.');
	}

	/**
	 * Test getNumRows method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetNumRows()
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('jos_dbtest');
		$query->where('description = ' . self::$driver->quote('one'));
		self::$driver->setQuery($query);

		$res = self::$driver->execute();

		$this->assertThat(self::$driver->getNumRows($res), $this->equalTo(0), __LINE__);
	}

	/**
	 * Tests the getTableCreate method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetTableCreate()
	{
		$this->assertThat(
			self::$driver->getTableCreate('#__dbtest'),
			$this->isType('array'),
			'The statement to create the table is returned in an array.'
		);
	}

	/**
	 * Test getTableColumns function.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetTableColumns()
	{
		$tableCol = array(
			'id' => 'INTEGER',
			'title' => 'TEXT',
			'start_date' => 'TEXT',
			'description' => 'TEXT',
			'data' => 'BLOB',
		);

		$this->assertThat(
			self::$driver->getTableColumns('jos_dbtest'),
			$this->equalTo($tableCol),
			__LINE__
		);

		/* not only type field */
		$id = new \stdClass;
		$id->Default = null;
		$id->Field   = 'id';
		$id->Type    = 'INTEGER';
		$id->Null    = 'YES';
		$id->Key     = 'PRI';

		$title = new \stdClass;
		$title->Default = '\'\'';
		$title->Field   = 'title';
		$title->Type    = 'TEXT';
		$title->Null    = 'NO';
		$title->Key     = '';

		$start_date = new \stdClass;
		$start_date->Default = '\'\'';
		$start_date->Field   = 'start_date';
		$start_date->Type    = 'TEXT';
		$start_date->Null    = 'NO';
		$start_date->Key     = '';

		$description = new \stdClass;
		$description->Default = '\'\'';
		$description->Field   = 'description';
		$description->Type    = 'TEXT';
		$description->Null    = 'NO';
		$description->Key     = '';

		$data = new \stdClass;
		$data->Default = null;
		$data->Field   = 'data';
		$data->Type    = 'BLOB';
		$data->Null    = 'YES';
		$data->Key     = '';

		$this->assertThat(
			self::$driver->getTableColumns('jos_dbtest', false),
			$this->equalTo(
				array(
					'id' => $id,
					'title' => $title,
					'start_date' => $start_date,
					'description' => $description,
					'data' => $data,
				)
			),
			__LINE__
		);
	}

	/**
	 * Tests the getTableKeys method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetTableKeys()
	{
		$this->assertThat(
			self::$driver->getTableKeys('#__dbtest'),
			$this->isType('array'),
			'The list of keys for the table is returned in an array.'
		);
	}

	/**
	 * Tests the getTableList method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetTableList()
	{
		$this->assertThat(
			self::$driver->getTableList(),
			$this->isType('array'),
			'The list of tables for the database is returned in an array.'
		);
	}

	/**
	 * Test getVersion method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testGetVersion()
	{
		$this->assertThat(
			\strlen(self::$driver->getVersion()),
			$this->greaterThan(0),
			'Line:' . __LINE__ . ' The getVersion method should return something without error.'
		);
	}

	/**
	 * Test insertid method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testInsertid()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test insertObject method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testInsertObject()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test isSupported method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testIsSupported()
	{
		$this->assertThat(\Joomla\Database\Sqlite\SqliteDriver::isSupported(), $this->isTrue(), __LINE__);
	}

	/**
	 * Test loadAssoc method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testLoadAssoc()
	{
		$query = self::$driver->getQuery(true);
		$query->select('title');
		$query->from('jos_dbtest');
		self::$driver->setQuery($query);
		$result = self::$driver->loadAssoc();

		$this->assertThat($result, $this->equalTo(array('title' => 'Testing')), __LINE__);
	}

	/**
	 * Test loadAssocList method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testLoadAssocList()
	{
		$query = self::$driver->getQuery(true);
		$query->select('title');
		$query->from('jos_dbtest');
		self::$driver->setQuery($query);
		$result = self::$driver->loadAssocList();

		$this->assertThat(
			$result,
			$this->equalTo(
				array(
					array('title' => 'Testing'),
					array('title' => 'Testing2'),
					array('title' => 'Testing3'),
					array('title' => 'Testing4')
				)
			),
			__LINE__
		);
	}

	/**
	 * Test loadColumn method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testLoadColumn()
	{
		$query = self::$driver->getQuery(true);
		$query->select('title');
		$query->from('jos_dbtest');
		self::$driver->setQuery($query);
		$result = self::$driver->loadColumn();

		$this->assertThat($result, $this->equalTo(array('Testing', 'Testing2', 'Testing3', 'Testing4')), __LINE__);
	}

	/**
	 * Test loadObject method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testLoadObject()
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('jos_dbtest');
		$query->where('description=' . self::$driver->quote('three'));
		self::$driver->setQuery($query);
		$result = self::$driver->loadObject();

		$objCompare = new \stdClass;
		$objCompare->id = 3;
		$objCompare->title = 'Testing3';
		$objCompare->start_date = '1980-04-18 00:00:00';
		$objCompare->description = 'three';
		$objCompare->data = null;

		$this->assertThat($result, $this->equalTo($objCompare), __LINE__);
	}

	/**
	 * Test loadObjectList method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testLoadObjectList()
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('jos_dbtest');
		$query->order('id');
		self::$driver->setQuery($query);
		$result = self::$driver->loadObjectList();

		$expected = array();

		$objCompare = new \stdClass;
		$objCompare->id = 1;
		$objCompare->title = 'Testing';
		$objCompare->start_date = '1980-04-18 00:00:00';
		$objCompare->description = 'one';
		$objCompare->data = null;

		$expected[] = clone $objCompare;

		$objCompare = new \stdClass;
		$objCompare->id = 2;
		$objCompare->title = 'Testing2';
		$objCompare->start_date = '1980-04-18 00:00:00';
		$objCompare->description = 'one';
		$objCompare->data = null;

		$expected[] = clone $objCompare;

		$objCompare = new \stdClass;
		$objCompare->id = 3;
		$objCompare->title = 'Testing3';
		$objCompare->start_date = '1980-04-18 00:00:00';
		$objCompare->description = 'three';
		$objCompare->data = null;

		$expected[] = clone $objCompare;

		$objCompare = new \stdClass;
		$objCompare->id = 4;
		$objCompare->title = 'Testing4';
		$objCompare->start_date = '1980-04-18 00:00:00';
		$objCompare->description = 'four';
		$objCompare->data = null;

		$expected[] = clone $objCompare;

		$this->assertThat($result, $this->equalTo($expected), __LINE__);
	}

	/**
	 * Test loadResult method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testLoadResult()
	{
		$query = self::$driver->getQuery(true);
		$query->select('id');
		$query->from('jos_dbtest');
		$query->where('title=' . self::$driver->quote('Testing2'));

		self::$driver->setQuery($query);
		$result = self::$driver->loadResult();

		$this->assertThat($result, $this->equalTo(2), __LINE__);
	}

	/**
	 * Test loadRow method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testLoadRow()
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('jos_dbtest');
		$query->where('description=' . self::$driver->quote('three'));
		self::$driver->setQuery($query);
		$result = self::$driver->loadRow();

		$expected = array(3, 'Testing3', '1980-04-18 00:00:00', 'three', null);

		$this->assertThat($result, $this->equalTo($expected), __LINE__);
	}

	/**
	 * Test loadRowList method
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testLoadRowList()
	{
		$query = self::$driver->getQuery(true);
		$query->select('*');
		$query->from('jos_dbtest');
		$query->where('description=' . self::$driver->quote('one'));
		self::$driver->setQuery($query);
		$result = self::$driver->loadRowList();

		$expected = array(
			array(1, 'Testing', '1980-04-18 00:00:00', 'one', null),
			array(2, 'Testing2', '1980-04-18 00:00:00', 'one', null)
		);

		$this->assertThat($result, $this->equalTo($expected), __LINE__);
	}

	/**
	 * Test quoteBinary and decodeBinary methods
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	public function testLoadBinary()
	{
		// Add binary data with null byte
		$query = self::$driver->getQuery(true)
			->update('jos_dbtest')
			->set('data = ' . self::$driver->quoteBinary("\x00\x01\x02\xff"))
			->where('id = 3');

		self::$driver->setQuery($query)->execute();

		// Add binary data with invalid UTF-8
		$query = self::$driver->getQuery(true)
			->update('jos_dbtest')
			->set('data = ' . self::$driver->quoteBinary("\x01\x01\x02\xff"))
			->where('id = 4');

		self::$driver->setQuery($query)->execute();

		$selectRow3 = self::$driver->getQuery(true)
			->select('id')
			->from('jos_dbtest')
			->where('data = ' . self::$driver->quoteBinary("\x00\x01\x02\xff"));

		$selectRow4 = self::$driver->getQuery(true)
			->select('id')
			->from('jos_dbtest')
			->where('data = '. self::$driver->quoteBinary("\x01\x01\x02\xff"));

		$result = self::$driver->setQuery($selectRow3)->loadResult();
		$this->assertThat($result, $this->equalTo(3), __LINE__);

		$result = self::$driver->setQuery($selectRow4)->loadResult();
		$this->assertThat($result, $this->equalTo(4), __LINE__);

		$selectRows = self::$driver->getQuery(true)
			->select('data')
			->from('jos_dbtest')
			->order('id');

		// Test loadColumn
		$result = self::$driver->setQuery($selectRows)->loadColumn();

		foreach ($result as $i => $v)
		{
			$result[$i] = self::$driver->decodeBinary($v);
		}

		$expected = array(null, null, "\x00\x01\x02\xff", "\x01\x01\x02\xff");
		$this->assertThat($result, $this->equalTo($expected), __LINE__);

		// Test loadAssocList
		$result = self::$driver->setQuery($selectRows)->loadAssocList();

		foreach ($result as $i => $v)
		{
			$result[$i]['data'] = self::$driver->decodeBinary($v['data']);
		}

		$expected = array(
			array('data' => null),
			array('data' => null),
			array('data' => "\x00\x01\x02\xff"),
			array('data' => "\x01\x01\x02\xff"),
		);
		$this->assertThat($result, $this->equalTo($expected), __LINE__);

		// Test loadObjectList
		$result = self::$driver->setQuery($selectRows)->loadObjectList();

		foreach ($result as $i => $v)
		{
			$result[$i]->data = self::$driver->decodeBinary($v->data);
		}

		$expected = array(
			(object) array('data' => null),
			(object) array('data' => null),
			(object) array('data' => "\x00\x01\x02\xff"),
			(object) array('data' => "\x01\x01\x02\xff"),
		);
		$this->assertThat($result, $this->equalTo($expected), __LINE__);
	}

	/**
	 * Tests the lockTable method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testLockTable()
	{
		$this->assertThat(
			self::$driver->lockTable('#__dbtest'),
			$this->isInstanceOf('\\Joomla\\Database\\Sqlite\\SqliteDriver'),
			'Method returns the current instance of the driver object.'
		);
	}

	/**
	 * Tests the renameTable method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testRenameTable()
	{
		$newTableName = 'bak_jos_dbtest';

		self::$driver->renameTable('jos_dbtest', $newTableName);

		// Check name change
		$tableList = self::$driver->getTableList();
		$this->assertThat(\in_array($newTableName, $tableList), $this->isTrue(), __LINE__);

		// Restore initial state
		self::$driver->renameTable($newTableName, 'jos_dbtest');
	}

	/**
	 * Test select method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testSelect()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test setUtf method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testSetUtf()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Tests the transactionCommit method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testTransactionCommit()
	{
		self::$driver->transactionStart();
		$queryIns = self::$driver->getQuery(true);
		$queryIns->insert('#__dbtest')
			->columns('id, title, start_date, description')
			->values("6, 'testTitle', '1970-01-01', 'testDescription'");

		self::$driver->setQuery($queryIns)->execute();

		self::$driver->transactionCommit();

		/* check if value is present */
		$queryCheck = self::$driver->getQuery(true);
		$queryCheck->select('*')
			->from('#__dbtest')
			->where('id = 6');
		self::$driver->setQuery($queryCheck);
		$result = self::$driver->loadRow();

		$expected = array('6', 'testTitle', '1970-01-01', 'testDescription', null);

		$this->assertThat($result, $this->equalTo($expected), __LINE__);
	}

	/**
	 * Tests the transactionRollback method, with and without savepoint.
	 *
	 * @param   string  $toSavepoint  Savepoint name to rollback transaction to
	 * @param   int     $tupleCount   Number of tuple found after insertion and rollback
	 *
	 * @return  void
	 *
	 * @since        1.0
	 * @dataProvider dataTestTransactionRollback
	 */
	public function testTransactionRollback($toSavepoint, $tupleCount)
	{
		self::$driver->transactionStart();

		/* try to insert this tuple, inserted only when savepoint != null */
		$queryIns = self::$driver->getQuery(true);
		$queryIns->insert('#__dbtest')
			->columns('id, title, start_date, description')
			->values("7, 'testRollback', '1970-01-01', 'testRollbackSp'");
		self::$driver->setQuery($queryIns)->execute();

		/* create savepoint only if is passed by data provider */
		if (!\is_null($toSavepoint))
		{
			self::$driver->transactionStart((boolean) $toSavepoint);
		}

		/* try to insert this tuple, always rolled back */
		$queryIns = self::$driver->getQuery(true);
		$queryIns->insert('#__dbtest')
			->columns('id, title, start_date, description')
			->values("8, 'testRollback', '1972-01-01', 'testRollbackSp'");
		self::$driver->setQuery($queryIns)->execute();

		self::$driver->transactionRollback((boolean) $toSavepoint);

		/* release savepoint and commit only if a savepoint exists */
		if (!\is_null($toSavepoint))
		{
			self::$driver->transactionCommit();
		}

		/* find how many rows have description='testRollbackSp' :
		 *   - 0 if a savepoint doesn't exist
		 *   - 1 if a savepoint exists
		 */
		$queryCheck = self::$driver->getQuery(true);
		$queryCheck->select('*')
			->from('#__dbtest')
			->where("description = 'testRollbackSp'");
		self::$driver->setQuery($queryCheck);
		$result = self::$driver->loadRowList();

		$this->assertThat(\count($result), $this->equalTo($tupleCount), __LINE__);
	}

	/**
	 * Tests the unlockTables method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testUnlockTables()
	{
		$this->assertThat(
			self::$driver->unlockTables(),
			$this->isInstanceOf('\\Joomla\\Database\\Sqlite\\SqliteDriver'),
			'Method returns the current instance of the driver object.'
		);
	}

	/**
	 * Test updateObject method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testUpdateObject()
	{
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
