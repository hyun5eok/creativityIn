<?php

/**
 * main model
 */
class Model extends Database
{
	public $errors = array();

	public function __construct()
	{
		// code...
		if(!property_exists($this, 'table'))
		{
			$this->table = strtolower($this::class) . "s";
		}
	}


	protected function get_primary_key($table)
	{
		$query = "SHOW KEYS from $table WHERE Key_name = 'PRIMARY' ";
		$db = new Database();
		$data = $db->query($query);

		return !empty($data[0]) ? $data[0]->Column_name : 'id';
	}

	public function where($column,$value,$orderby = 'desc',$limit = 10,$offset = 0)
	{
		$column = addslashes($column);
		$primary_key = $this->get_primary_key($this->table);

		$query = "SELECT * FROM $this->table WHERE $column = :value ORDER BY $primary_key $orderby LIMIT $limit OFFSET $offset";
		$data = $this->query($query, ['value' => $value]);

		// run functions after select
		if (is_array($data) && property_exists($this, 'afterSelect')) {
			foreach ($this->afterSelect as $func) {
				$data = $this->$func($data);
			}
		}

		return $data;
	}

	public function first($column,$value,$orderby = 'desc')
	{
		$column = addslashes($column);
		$primary_key = $this->get_primary_key($this->table);

		$query = "SELECT * FROM $this->table WHERE $column = :value ORDER BY $primary_key $orderby";
		$data = $this->query($query, ['value' => $value]);

		// run functions after select
		if (is_array($data) && property_exists($this, 'afterSelect')) {
			foreach ($this->afterSelect as $func) {
				$data = $this->$func($data);
			}
		}

		return is_array($data) ? $data[0] : $data;
	}

	public function findAll($orderby = 'desc',$limit = 100,$offset = 0)
	{

		$primary_key = $this->get_primary_key($this->table);

		$query = "select * from $this->table order by $primary_key $orderby limit $limit offset $offset";
		$data = $this->query($query);

		//run functions after select
		if(is_array($data)){
			if(property_exists($this, 'afterSelect'))
			{
				foreach($this->afterSelect as $func)
				{
					$data = $this->$func($data);
				}
			}
		}

		return $data;

	}

	public function insert($data)
	{
		// remove unwanted columns
		if (property_exists($this, 'allowedColumns')) {
			$data = array_intersect_key($data, array_flip($this->allowedColumns));
		}

		// run functions before insert
		if (property_exists($this, 'beforeInsert')) {
			foreach ($this->beforeInsert as $func) {
				$data = $this->$func($data);
			}
		}

		$columns = implode(',', array_keys($data));
		$values = ':' . implode(',:', array_keys($data));

		$query = "INSERT INTO $this->table ($columns) VALUES ($values)";
		return $this->query($query, $data);
	}

	public function update($id,$data)
	{
		// remove unwanted columns
		if (property_exists($this, 'allowedColumns')) {
			$data = array_intersect_key($data, array_flip($this->allowedColumns));
		}

		// run functions before update
		if (property_exists($this, 'beforeUpdate')) {
			foreach ($this->beforeUpdate as $func) {
				$data = $this->$func($data);
			}
		}

		$updateData = array_map(function ($key) {
			return "$key=:$key";
		}, array_keys($data));

		$updateStr = implode(',', $updateData);

		$data['id'] = $id;
		$query = "UPDATE $this->table SET $updateStr WHERE id = :id";

		return $this->query($query, $data);
	}

	public function delete($id)
	{

		$query = "delete from $this->table where id = :id";
		$data['id'] = $id;
		return $this->query($query,$data);
	}
	
}

