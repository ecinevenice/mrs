<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Divisions_m extends MY_Model
{
	protected $_table = 'default_divisions';


	/**
	 * Insert a new category into the database
	 * 
	 * @param array $input The data to insert
	 * @return string
	 */
	public function insert($input = array())
	{
		parent::insert(array(
			//~ 'title'=>$input['title'],
			//~ 'slug'=>url_title(strtolower(convert_accented_characters($input['title'])))
			'division_name' 				=> $input['division_name'],
			'division_group' 				=> $input['division_group'],
			'approver' 						=> $input['approver'],
			'approver_proxy' 				=> $input['approver_proxy'],
			'requestor' 					=> $input['requestor'],
			'requestor_proxy' 				=> $input['requestor_proxy']
		));
		
		return $this->db->insert_id();
	}
	
	public function get_all()
	{
		//~ $this->db
			//~ ->select('blog.*, blog_categories.title AS category_title, blog_categories.slug AS category_slug, profiles.display_name')
			//~ ->join('blog_categories', 'blog.category_id = blog_categories.id', 'left')
			//~ ->join('profiles', 'profiles.user_id = blog.author_id', 'left')
			//~ ->order_by('created_on', 'DESC');
	
		$this->db
			->select('divisions.*');
		return $this->db->get('divisions')->result();
		//~ print_r($this->db->get('stockcategory')->result());
		//~ die();
	}
	
	
	public function get_accounting_categories()
	{
		//~ $this->db
			//~ ->select('blog.*, blog_categories.title AS category_title, blog_categories.slug AS category_slug, profiles.display_name')
			//~ ->join('blog_categories', 'blog.category_id = blog_categories.id', 'left')
			//~ ->join('profiles', 'profiles.user_id = blog.author_id', 'left')
			//~ ->order_by('created_on', 'DESC');
	
		$this->db
			->select('accounting_category.*')
			->order_by('id','ASC');
		return $this->db->get('accounting_category')->result();
		//~ print_r($this->db->get('stockcategory')->result());
		//~ die();
	}
	
	

	public function get($id)
	{
		return $this->db
			->select('divisions.*')
			->where(array('id' => $id))
			->get('divisions')
			->row();
	}
	

	
	public function count_category_items($id)
	{
		return $this->db
			->select('stockmaster.*')
			->where(array('category_id' => $id))
			->count_all_results('stockmaster');
	}
	
	public function get_by($key, $value = '')
	{
		$this->db
			->select('blog.*, profiles.display_name')
			->join('profiles', 'profiles.user_id = blog.author_id', 'left');
			
		if (is_array($key))
		{
			$this->db->where($key);
		}
		else
		{
			$this->db->where($key, $value);
		}

		return $this->db->get($this->_table)->row();
	}

	public function get_many_by($params = array())
	{
		$this->load->helper('date');

		if (!empty($params['category']))
		{
			if (is_numeric($params['category']))
				$this->db->where('blog_categories.id', $params['category']);
			else
				$this->db->where('blog_categories.slug', $params['category']);
		}

		if (!empty($params['month']))
		{
			$this->db->where('MONTH(FROM_UNIXTIME(created_on))', $params['month']);
		}

		if (!empty($params['year']))
		{
			$this->db->where('YEAR(FROM_UNIXTIME(created_on))', $params['year']);
		}

		if ( ! empty($params['keywords']))
		{
			$this->db
				->like('blog.title', trim($params['keywords']))
				->or_like('profiles.display_name', trim($params['keywords']));
		}

		// Is a status set?
		if (!empty($params['status']))
		{
			// If it's all, then show whatever the status
			if ($params['status'] != 'all')
			{
				// Otherwise, show only the specific status
				$this->db->where('status', $params['status']);
			}
		}

		// Nothing mentioned, show live only (general frontend stuff)
		else
		{
			$this->db->where('status', 'live');
		}

		// By default, dont show future posts
		if (!isset($params['show_future']) || (isset($params['show_future']) && $params['show_future'] == FALSE))
		{
			$this->db->where('created_on <=', now());
		}

		// Limit the results based on 1 number or 2 (2nd is offset)
		if (isset($params['limit']) && is_array($params['limit']))
			$this->db->limit($params['limit'][0], $params['limit'][1]);
		elseif (isset($params['limit']))
			$this->db->limit($params['limit']);

		return $this->get_all();
	}
	
	public function count_tagged_by($tag, $params)
	{
		return $this->select('*')
			->from('blog')
			->join('keywords_applied', 'keywords_applied.hash = blog.keywords')
			->join('keywords', 'keywords.id = keywords_applied.keyword_id')
			->where('keywords.name', str_replace('-', ' ', $tag))
			->where($params)
			->count_all_results();
	}
	
	public function get_tagged_by($tag, $params)
	{
		return $this->db->select('blog.*, blog.title title, blog.slug slug, blog_categories.title category_title, blog_categories.slug category_slug, profiles.display_name')
			->from('blog')
			->join('keywords_applied', 'keywords_applied.hash = blog.keywords')
			->join('keywords', 'keywords.id = keywords_applied.keyword_id')
			->join('blog_categories', 'blog_categories.id = blog.category_id', 'left')
			->join('profiles', 'profiles.user_id = blog.author_id', 'left')
			->where('keywords.name', str_replace('-', ' ', $tag))
			->where($params)
			->get()
			->result();
	}

	public function count_by($params = array())
	{
		$this->db->join('blog_categories', 'blog.category_id = blog_categories.id', 'left')
			// we need the display name joined so we can get an accurate count when searching
			->join('profiles', 'profiles.user_id = blog.author_id');

		if (!empty($params['category']))
		{
			if (is_numeric($params['category']))
				$this->db->where('blog_categories.id', $params['category']);
			else
				$this->db->where('blog_categories.slug', $params['category']);
		}

		if (!empty($params['month']))
		{
			$this->db->where('MONTH(FROM_UNIXTIME(created_on))', $params['month']);
		}

		if (!empty($params['year']))
		{
			$this->db->where('YEAR(FROM_UNIXTIME(created_on))', $params['year']);
		}

		if ( ! empty($params['keywords']))
		{
			$this->db
				->like('blog.title', trim($params['keywords']))
				->or_like('profiles.display_name', trim($params['keywords']));
		}

		// Is a status set?
		if (!empty($params['status']))
		{
			// If it's all, then show whatever the status
			if ($params['status'] != 'all')
			{
				// Otherwise, show only the specific status
				$this->db->where('status', $params['status']);
			}
		}

		// Nothing mentioned, show live only (general frontend stuff)
		else
		{
			$this->db->where('status', 'live');
		}

		return $this->db->count_all_results('blog');
	}

	public function update($id, $input)
	{
		//$input['updated_on'] = now();
        //if($input['status'] == "live" and $input['preview_hash'] !='') $input['preview_hash'] = '';
		return parent::update($id, $input);
	}

	public function publish($id = 0)
	{
		return parent::update($id, array('status' => 'live','preview_hash'=>''));
	}
	
	

	// -- Archive ---------------------------------------------

	public function get_archive_months()
	{
		$this->db->select('UNIX_TIMESTAMP(DATE_FORMAT(FROM_UNIXTIME(t1.created_on), "%Y-%m-02")) AS `date`', FALSE);
		$this->db->from('blog t1');
		$this->db->distinct();
		$this->db->select('(SELECT count(id) FROM ' . $this->db->dbprefix('blog') . ' t2
							WHERE MONTH(FROM_UNIXTIME(t1.created_on)) = MONTH(FROM_UNIXTIME(t2.created_on))
								AND YEAR(FROM_UNIXTIME(t1.created_on)) = YEAR(FROM_UNIXTIME(t2.created_on))
								AND status = "live"
								AND created_on <= ' . now() . '
						   ) as post_count');

		$this->db->where('status', 'live');
		$this->db->where('created_on <=', now());
		$this->db->having('post_count >', 0);
		$this->db->order_by('t1.created_on DESC');
		$query = $this->db->get();

		return $query->result();
	}

	// DIRTY frontend functions. Move to views
	public function get_blog_fragment($params = array())
	{
		$this->load->helper('date');

		$this->db->where('status', 'live');
		$this->db->where('created_on <=', now());

		$string = '';
		$this->db->order_by('created_on', 'DESC');
		$this->db->limit(5);
		$query = $this->db->get('blog');
		if ($query->num_rows() > 0)
		{
			$this->load->helper('text');
			foreach ($query->result() as $blog)
			{
				$string .= '<p>' . anchor('blog/' . date('Y/m') . '/' . $blog->slug, $blog->title) . '<br />' . strip_tags($blog->intro) . '</p>';
			}
		}
		return $string;
	}

	public function check_exists($field, $value = '', $id = 0)
	{
		if (is_array($field))
		{
			$params = $field;
			$id = $value;
		}
		else
		{
			$params[$field] = $value;
		}
		//$params['id !='] = (int) $id;

		return parent::count_by($params) == 0;
	}

	/**
	 * Searches blog posts based on supplied data array
	 * @param $data array
	 * @return array
	 */
	public function search($data = array())
	{
		if (array_key_exists('category_id', $data))
		{
			$this->db->where('category_id', $data['category_id']);
		}

		if (array_key_exists('status', $data))
		{
			$this->db->where('status', $data['status']);
		}

		if (array_key_exists('keywords', $data))
		{
			$matches = array();
			if (strstr($data['keywords'], '%'))
			{
				preg_match_all('/%.*?%/i', $data['keywords'], $matches);
			}

			if (!empty($matches[0]))
			{
				foreach ($matches[0] as $match)
				{
					$phrases[] = str_replace('%', '', $match);
				}
			}
			else
			{
				$temp_phrases = explode(' ', $data['keywords']);
				foreach ($temp_phrases as $phrase)
				{
					$phrases[] = str_replace('%', '', $phrase);
				}
			}

			$counter = 0;
			foreach ($phrases as $phrase)
			{
				if ($counter == 0)
				{
					$this->db->like('blog.title', $phrase);
				}
				else
				{
					$this->db->or_like('blog.title', $phrase);
				}

				$this->db->or_like('blog.body', $phrase);
				$this->db->or_like('blog.intro', $phrase);
				$this->db->or_like('profiles.display_name', $phrase);
				$counter++;
			}
		}
		return $this->get_all();
	}
	
	public function check_if_has_request($id = 0)
	{
		$this->db
			->select('material_requests.*')
			->where('division_group',$id);
		return $this->db->get('material_requests')->result();
	}
	
	public function get_division_approvers()
	{
		$this->db
			->select('users.*,profiles.first_name,profiles.last_name')
			->join('profiles', 'users.id = profiles.user_id', 'left')
			->where('users.group_id','8');
	    return $this->db->get('users')->result();
		
	}
	
	public function get_division_requestors()
	{
		$this->db
			->select('users.*,profiles.first_name,profiles.last_name')
			->join('profiles', 'users.id = profiles.user_id', 'left')
			->where('users.group_id','6');
	    return $this->db->get('users')->result();
		
	}
	
	
	public function is_requestor_assigned($id)
	{
		return $this->db
			->select('divisions.*')
			->where(array('requestor' => $id))
			->get('divisions')
			->row();
	
	}
	
	
	

}