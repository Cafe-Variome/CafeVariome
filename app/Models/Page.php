<?php namespace App\Models;

/**
 * Name: Page.php
 *
 * Created: 19/02/2020
 *
 * @author Mehdi Mehtarizadeh
 *
 */

use CodeIgniter\Model;

class Page extends Model
{
    protected $db;
    protected $table      = 'pages';
    protected $builder;

    protected $primaryKey = 'id';

	public function __construct(ConnectionInterface &$db = null)
	{
        if ($db != null)
		{
            $this->db =& $db;
        }
        else
		{
            $this->db = \Config\Database::connect();
        }
		$this->builder = $this->db->table($this->table);
    }

    public function getPages()
	{
        return $this->builder->get()->getResultArray();
    }

	public function getPage(int $id)
	{
		$this->builder->select();
		$this->builder->where('id', $id);
		$query = $this->builder->get()->getResultArray();

		if(count($query) == 1)
		{
			return $query[0];
		}

		return null;
	}

	public function getActivePage(int $id)
	{
		$this->builder->select();
		$this->builder->where('id', $id);
		$this->builder->where('Active', true);
		$query = $this->builder->get()->getResultArray();

		if(count($query) == 1)
		{
			return $query[0];
		}

		return null;
	}

    public function createPage(array $data)
    {
        $this->builder->insert($data);
    }

    public function updatePage(array $data, array $conds)
	{
        if ($conds)
		{
            $this->builder->where($conds);
        }
        $this->builder->update($data);
    }

    public function deletePage(int $page_id)
    {
        $this->builder->where('id', $page_id);
        $this->builder->delete();
    }
}
