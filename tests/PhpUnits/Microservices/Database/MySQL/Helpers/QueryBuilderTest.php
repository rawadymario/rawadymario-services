<?php
	namespace DigitalSplash\Tests\Database\MySQL\Helpers;

	use DigitalSplash\Database\MySQL\Helpers\QueryBuilder;
	use DigitalSplash\Exceptions\NotEmptyParamException;
	use DigitalSplash\Language\Helpers\Translate;
	use PHPUnit\Framework\TestCase;

	class QueryBuilderTest extends TestCase {

		public function testInsertNoDataToInsertThrows(): void {
			$this->expectException(NotEmptyParamException::class);
			$this->expectExceptionMessage(Translate::TranslateString("exception.NotEmptyParam", null, [
				"::params::" => "data"
			]));

			$queryBuilder = new QueryBuilder('db', 'table');
			$queryBuilder->insert([]);
		}

		public function testInsertSingleRecordSuccess(): void {
			$db = 'db';
			$table = 'table';
			$vals = [
				'name' => 'Hadi Darwish',
				'email' => 'hadi@example.com',
				'age' => 22,
			];

			$queryBuilder = new QueryBuilder($db, $table);
			[
				'sql' => $sql,
				'binds' => $binds
			] = $queryBuilder->insert($vals);

			$expectedSql = "INSERT INTO {$db}.{$table} (`name`, `email`, `age`) VALUES (:name, :email, :age)";
			$expectedBinds = [];
			foreach ($vals as $col => $val) {
				$bind_key = ':' . $col;
				$expectedBinds[$bind_key] = [
					'value' => $val,
					'type' => QueryBuilder::GetPDOTypeFromValue($val)
				];
			}

			$this->assertEquals($expectedSql, $sql);
			$this->assertEqualsCanonicalizing($expectedBinds, $binds);
		}

		public function testUpdateNoDataToInsertThrows(): void {
			$this->expectException(NotEmptyParamException::class);
			$this->expectExceptionMessage(Translate::TranslateString("exception.NotEmptyParam", null, [
				"::params::" => "data"
			]));

			$queryBuilder = new QueryBuilder('db', 'table');
			$queryBuilder->update([]);
		}

		public function testUpdateSingleRecordSuccess(): void {
			$queryBuilder = new QueryBuilder('db', 'table');
			[
				'sql' => $sql,
				'binds' => $binds
			] = $queryBuilder->update([
				'name' => 'Hadi Darwish',
				'age' => 22,
				'email' => 'hadi@example.com'
			]
			, ['id' => 1]);

			$expectedSql = 'UPDATE db.table SET `name` = :name, `age` = :age, `email` = :email WHERE `id` = :id';
			$expectedBinds = [
				':name' => ['value' => 'Hadi Darwish', 'type' => 2],
				':age' => ['value' => 22, 'type' => 1],
				':email' => ['value' => 'hadi@example.com', 'type' => 2],
				':id' => ['value' => 1, 'type' => 1],
			];

			$this->assertEquals($expectedSql, $sql);
			$this->assertEqualsCanonicalizing($expectedBinds, $binds);
		}

        public function testDeleteNoDataToInsertThrows(): void {
            $this->expectException(NotEmptyParamException::class);
            $this->expectExceptionMessage(Translate::TranslateString("exception.NotEmptyParam", null, [
                "::params::" => "data"
            ]));

            $queryBuilder = new QueryBuilder('db', 'table');
            $queryBuilder->delete([]);
        }

        public function testDeleteSingleRecordSuccess(): void {
            $queryBuilder = new QueryBuilder('db', 'table');
            [
                'sql' => $sql,
                'binds' => $binds
            ] = $queryBuilder->delete(['id' => 1]);

            $expectedSql = 'DELETE FROM db.table WHERE `id` = :id';
            $expectedBinds = [
                ':id' => ['value' => 1, 'type' => 1],
            ];

            $this->assertEquals($expectedSql, $sql);
            $this->assertEqualsCanonicalizing($expectedBinds, $binds);
        }

		// public function testInsert() {
		//     $table = 'users';
		//     $data = [
		//         'name' => 'John Doe',
		//         'email' => 'john@example.com',
		//         'password' => '123456'
		//     ];
		//     $db = new DatabaseCredentials('localhost', 'root', '', 'test');
		//     $sql = QueryBuilder::insert($table, $data, $db);
		//     $expectedSql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
		//     $this->assertEquals($expectedSql, $sql);
		// }

		// public function testUpdate() {
		//     $table = 'users';
		//     $data = [
		//         'name' => 'John',
		//         'age' => 25
		//     ];
		//     $where = 'id = 1';
		//     $db = new DatabaseCredentials('localhost', 'root', 'password', 'test');
		//     $expectedSql = "UPDATE users SET (name, age) VALUES (?, ?) WHERE id = 1";
		//     $actualSql = QueryBuilder::update($table, $data, $where, $db);
		//     $this->assertEquals($expectedSql, $actualSql);
		// }

		// public function testDelete() {
		//     $table = 'users';
		//     $where = 'id=1';
		//     $db = new DatabaseCredentials('localhost', 'username', 'password', 'database');
		//     $expected = "DELETE FROM $table WHERE $where";
		//     $actual = QueryBuilder::delete($table, $where, $db);
		//     $this->assertEquals($expected, $actual);
		// }

		// // public function testSelect() {
		// //     $table = 'users';
		// //     $columns = ['name', 'email'];
		// //     $where = 'id = 1';
		// //     $db = new DatabaseCredentials('localhost', 'root', 'password', 'test_db');

		// //     $expectedSql = "SELECT name, email FROM users WHERE id = 1";
		// //     $actualSql = QueryBuilder::select($table, $columns, $where, $db);

		// //     $this->assertEquals($expectedSql, $actualSql);
		// // }

		// // public function testSelectAll() {
		// //     $table = 'users';
		// //     $db = new DatabaseCredentials('localhost', 'root', 'password', 'test_db');
		// //     $expected = "SELECT * FROM $table";
		// //     $actual = QueryBuilder::selectAll($table, $db);
		// //     $this->assertEquals($expected, $actual);
		// // }

		// // public function testSelectAllWhere() {
		// //     $table = 'users';
		// //     $where = ['name' => 'John', 'age' => 25];
		// //     $db = new DatabaseCredentials('localhost', 'root', 'password', 'test_db');
		// //     $expectedSql = "SELECT * FROM users WHERE name='John' AND age=25";
		// //     $actualSql = QueryBuilder::selectAllWhere($table, $where, $db);
		// //     $this->assertEquals($expectedSql, $actualSql);
		// // }


		// // public function testSelectAllOrderBy() {
		// //     $table = 'users';
		// //     $db = new DatabaseCredentials('localhost', 'root', 'password', 'test_db');
		// //     $orderBy = 'name';
		// //     $expectedSql = "SELECT * FROM $table ORDER BY $orderBy";
		// //     $actualSql = QueryBuilder::selectAllOrderBy($table, $db, $orderBy);
		// //     $this->assertEquals($expectedSql, $actualSql);
		// // }


		// // public function testSelectAllWhereOrderBy() {
		// //     $table = 'users';
		// //     $where = ['name' => 'John', 'age' => 25];
		// //     $db = new DatabaseCredentials('localhost', 'root', 'password', 'test_db');
		// //     $orderBy = 'name';
		// //     $expectedSql = "SELECT * FROM users WHERE name='John' AND age=25 ORDER BY name";
		// //     $actualSql = QueryBuilder::selectAllWhereOrderBy($table, $where, $db, $orderBy);
		// //     $this->assertEquals($expectedSql, $actualSql);
		// // }



		// public function testSelectWithEmptyColumnsAndNoWhereAndNoJoinAndNoOrderBy()
		// {
		//     $sql = $this->db->select('users');
		//     $this->assertEquals('SELECT * FROM users', $sql);
		// }

		// public function testSelectWithColumnsAndNoWhereAndNoJoinAndNoOrderBy()
		// {
		//     $sql = $this->db->select('users', ['id', 'name']);
		//     $this->assertEquals('SELECT id, name FROM users', $sql);
		// }

		// public function testSelectWithColumnsAndWhereAndNoJoinAndNoOrderBy()
		// {
		//     $sql = $this->db->select('users', ['id', 'name'], ['age > 18', 'country = "USA"']);
		//     $this->assertEquals('SELECT id, name FROM users WHERE age > 18 AND country = "USA"', $sql);
		// }



	}
