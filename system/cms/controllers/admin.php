<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * The admin class is basically the main controller for the backend.
 *
 * @author PyroCMS Development Team
 * @package	 PyroCMS\Core\Controllers
 */
class Admin extends Admin_Controller
{
	/**
	 * Constructor method
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('approvedivgroup/approvegroup_m','approvediv/approvediv_m','materialrequest/matreq_m','materialrequest/matreq_items_m', 'categories/category_m','items/item_m','audit_trail/audit_trail_m','users/user_m','users/profile_m'));
	
		$this->load->helper('users/user');
		$this->lang->load('mrs');
	}

	/**
	 * Show the control panel
	 */
	public function index()
	{
		$this->template
			->enable_parser(TRUE)
			->title(lang('global:dashboard'));

		if (is_dir('./installer'))
		{
			$this->template
				->set('messages', array('notice' => lang('cp_delete_installer_message')));
		}
		
		//REQUISITIONER		
		$group_id  = $this->user_m->get_group($this->current_user->id);
		
		switch ($group_id) 
		{
		
			case 6:
				//REQUISITIONER
				$material_request = $this->matreq_m->get_today($this->current_user->id);						
				$news_feeds = $this->audit_trail_m->get_all_history();	
				$material_req = $this->matreq_m->get_where(array('requestor'=>$this->current_user->id));
				$this->template
				->set('material_request', $material_request)
				->set('news_feeds', $news_feeds)
				->set('material_req', $material_req)
				->build('admin/dashboard_requisitioner');
				break;
				
			case 8:
				//DIVISION APPROVER
				$users_division = $this->approvediv_m->get_approver_division($this->current_user->id);		
				$mr_approval = $this->approvediv_m->get_approval($users_division->id,'on hold');	
				$mr_on_hold = $this->approvediv_m->get_approval($users_division->id,'for approval');				
				$news_feeds = $this->audit_trail_m->get_all_history();	
				$material_req = $this->matreq_m->get_where(array('division'=>$users_division->id));
				
				$this->template
				->set('mr_approval', $mr_approval)
				->set('mr_on_hold', $mr_on_hold )
				->set('news_feeds', $news_feeds)
				->set('material_req', $material_req)
				->build('admin/dashboard_divapprover');
				break;
				
			case 10:
				//DIVISION GROUP APPROVER
				$users_division = $this->approvegroup_m->get_approver_group_division($this->current_user->id);	
				$mr_approval = $this->approvegroup_m->get_approval($users_division->id,'on hold');	
				$mr_on_hold = $this->approvegroup_m->get_approval($users_division->id,'for approval');		
				$news_feeds = $this->audit_trail_m->get_all_history();	
				$material_req = $this->matreq_m->get_where(array('division_group'=>$users_division->id));
				
				$this->template
				->set('mr_approval', $mr_approval)
				->set('mr_on_hold', $mr_on_hold )
				->set('news_feeds', $news_feeds)
				->set('material_req', $material_req)
				->build('admin/dashboard_divgroup_approver');
				break;
				
			case 13:
				//SPECIAL APPROVER				
				$news_feeds = $this->audit_trail_m->get_all_history();				
				$this->template
				->set('news_feeds', $news_feeds)
				->build('admin/dashboard_special_approver');
				break;
			default:
			$this->template
				->build('dashboard');
		}
		
		
	
			
	}

	/**
	 * Log in
	 */
	public function login()
	{
		// Set the validation rules
		$this->validation_rules = array(
			array(
				'field' => 'email',
				'label' => lang('email_label'),
				'rules' => 'required|callback__check_login'
			),
			array(
				'field' => 'password',
				'label' => lang('password_label'),
				'rules' => 'required'
			)
		);

		// Call validation and set rules
		$this->load->library('form_validation');
		$this->form_validation->set_rules($this->validation_rules);

		// If the validation worked, or the user is already logged in
		if ($this->form_validation->run() OR $this->ion_auth->logged_in())
		{
			// if they were trying to go someplace besides the 
			// dashboard we'll have stored it in the session
			$redirect = $this->session->userdata('admin_redirect');
			$this->session->unset_userdata('admin_redirect');

			redirect($redirect ? $redirect : 'admin');
		}

		$this->template
			->set_layout(FALSE)
			->build('admin/login');
	}

	/**
	 * Logout
	 */
	public function logout()
	{
		$this->load->language('users/user');
		$this->ion_auth->logout();
		$this->session->set_flashdata('success', lang('user_logged_out'));
		redirect('admin/login');
	}

	/**
	 * Callback From: login()
	 *
	 * @param string $email The Email address to validate
	 *
	 * @return bool
	 */
	public function _check_login($email)
	{
		if ($this->ion_auth->login($email, $this->input->post('password'), (bool)$this->input->post('remember')))
		{
			Events::trigger('post_admin_login');
			
			return true;
		}

		$this->form_validation->set_message('_check_login', $this->ion_auth->errors());
		return false;
	}

	/**
	 * Display the help string from a module's
	 * details.php file in a modal window
	 *
	 * @param	string	$slug	The module to fetch help for
	 *
	 * @return	void
	 */
	public function help($slug)
	{
		$this->template
			->set_layout('modal', 'admin')
			->set('help', $this->module_m->help($slug))
			->build('admin/partials/help');
	}
}