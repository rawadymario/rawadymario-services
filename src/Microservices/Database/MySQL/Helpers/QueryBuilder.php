<?php
	namespace DigitalSplash\Database\MySQL\Helpers;

	use DigitalSplash\Database\MySQL\Models\Binds;
	use DigitalSplash\Database\MySQL\Models\Data;
	use DigitalSplash\Database\MySQL\Models\Group;
	use DigitalSplash\Database\MySQL\Models\Having;
	use DigitalSplash\Database\MySQL\Models\Join;
	use DigitalSplash\Database\MySQL\Models\Limit;
	use DigitalSplash\Database\MySQL\Models\Offset;
	use DigitalSplash\Database\MySQL\Models\Order;
	use DigitalSplash\Database\MySQL\Models\Sql;
	use DigitalSplash\Database\MySQL\Models\Where;
	use DigitalSplash\Exceptions\NotEmptyParamException;
	use DigitalSplash\Helpers\Helper;
	use PDO;

	class QueryBuilder {
		const SQL = 'sql';
		const BINDS = 'binds';

		protected string $database;
		protected string $table;
		public Sql $sql;
		public Binds $binds;
		public Data $data;
		public Where $where;
		public Having $having;
		public Join $join;
		public Group $group;
		public Order $order;
		public Limit $limit;
		public Offset $offset;

		public function __construct(
			string $database,
			string $table
		) {
			if (Helper::StringNullOrEmpty($database)) {
				throw new NotEmptyParamException('database');
			}

			if (Helper::StringNullOrEmpty($table)) {
				throw new NotEmptyParamException('table');
			}

			$this->database = $database;
			$this->table = $table;
			$this->sql = new Sql();
			$this->binds = new Binds();
			$this->data = new Data();

			$this->where = new Where();
			$this->having = new Having();
			$this->join = new Join();
			$this->group = new Group();
			$this->order = new Order();
			$this->limit = new Limit();
			$this->offset = new Offset();
		}

		public static function GetPDOTypeFromValue($value): int {
			$type = PDO::PARAM_STR;

			$valueType = gettype($value);
			if ($valueType === 'integer' || $valueType === 'double') {
				$type = PDO::PARAM_INT;
			}

			return $type;
		}

		public function getDatabase(): string {
			return $this->database;
		}

		public function getTable(): string {
			return $this->table;
		}

		public function insert(): array {
			if (Helper::ArrayNullOrEmpty($this->data->getData())) {
				throw new NotEmptyParamException('data');
			}

			$columns = [];
			$binds = [];
			$r = 1;
			foreach ($this->data->getData() AS $rows) {
				$row_binds = [];

				foreach ($rows AS $column => $value) {
					if (!in_array($column, $columns)) {
						$columns[] = $column;
					}

					$bind_key = ':' . $column . "_" . $r;
					$this->binds->appendToBinds($bind_key, [
						'value' => $value,
						'type' => self::GetPDOTypeFromValue($value)
					]);

					$row_binds[] = $bind_key;
				}

				$binds[] = "(" . Helper::ImplodeArrToStr($row_binds, ', ') . ")";
				$r++;
			}

			$columnsStr = '`' . Helper::ImplodeArrToStr($columns, '`, `') . '`';
			$bindsStr = Helper::ImplodeArrToStr($binds, ', ');

			$this->sql->setValue("INSERT INTO `{$this->database}`.`{$this->table}` ({$columnsStr}) VALUES {$bindsStr}");
			$this->sql->generateStringStatement();
			return [
				self::SQL => $this->sql->getFinalString(),
				self::BINDS => $this->binds->getBinds()
			];
		}


		// public function update(): array {
		// 	if (Helper::ArrayNullOrEmpty($this->data)) {
		// 		throw new NotEmptyParamException('data');
		// 	}

		// 	$columns = [];
		// 	$binds = [];
		// 	$columnsStr = '';
		// 	foreach ($this->data AS $column => $value) {
		// 		if (!in_array($column, $columns)) {
		// 			$columns[] = "`{$column}`";
		// 		}
		// 		$bind_key = ':' . $column;

		// 		$binds[$bind_key] = [
		// 			'value' => $value,
		// 			'type' => self::GetPDOTypeFromValue($value)
		// 		];

		// 		$columnsStr .= "`{$column}` = :{$column}, ";

		// 	}

		// 	$this->getWhereStatement();
		// 	$binds = array_merge($binds, $this->_binds);

		// 	$columnsStr = rtrim($columnsStr, ', ');
		// 	$sql = "UPDATE {$this->database}.{$this->table} SET $columnsStr" . $this->where;

		// 	return [
		// 		self::SQL => $sql,
		// 		self::BINDS => $binds
		// 	];
		// }

		public function delete(): array {
			$this->join->generateStringStatement();
			$this->where->generateStringStatement();

			$sql = Helper::ImplodeArrToStr([
				"DELETE FROM `{$this->database}`.`{$this->table}`",
				$this->join->getFinalString(),
				$this->where->getFinalString()
			], ' ');

			$this->sql->setValue($sql);
			$this->sql->generateStringStatement();
			$this->binds->setBinds($this->where->binds->getBinds());

			return [
				self::SQL => $this->sql->getFinalString(),
				self::BINDS => $this->binds->getBinds()
			];
		}

		public function select(): array {
			$columnsStr = Helper::ImplodeArrToStr($this->data->getData(), '`, `');
			if (Helper::StringNullOrEmpty($columnsStr)) {
				$columnsStr = '*';
			} else {
				$columnsStr = '`' . $columnsStr . '`';
			}

			$this->where->generateStringStatement();
			$this->join->generateStringStatement();
			$this->group->generateStringStatement();
			$this->having->generateStringStatement();
			$this->order->generateStringStatement();
			$this->limit->generateStringStatement();
			$this->offset->generateStringStatement();

			$this->binds->setBinds(array_merge(
				$this->where->binds->getBinds(),
				$this->having->binds->getBinds()
			));

			$sql = Helper::ImplodeArrToStr([
				"SELECT $columnsStr FROM `{$this->database}`.`{$this->table}`",
				$this->join->getFinalString(),
				$this->where->getFinalString(),
				$this->group->getFinalString(),
				$this->having->getFinalString(),
				$this->order->getFinalString(),
				$this->limit->getFinalString(),
				$this->offset->getFinalString(),
			], ' ');
			$this->sql->setValue($sql);
			$this->sql->generateStringStatement();

			return [
				self::SQL => $this->sql->getFinalString(),
				self::BINDS => $this->binds->getBinds()
			];
		}
	}
