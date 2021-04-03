// Table in admin panel
<?php
if (is_admin() == TRUE) {
	new Init_Active_Phone_Menu_Table_Create();
}

class Init_Active_Phone_Menu_Table_Create
{
	public function __construct()
	{
		add_action('admin_menu', array($this, 'createMenu'));
	}

	public function createMenu()
	{
		add_menu_page('Статистика кликов по активной кнопке', 'Статистика кликов по активной кнопке', 'manage_options', 'active_phone_statistic', array($this, 'createTable'), 'dashicons-code-standards', '20.5');
	}

	public function createTable()
	{
		$Table = new Active_Phone_Menu_Table_Create();
		$Table->prepare_items();

?>
		<div class="wrap">
			<h2>Example List Table</h2>
			<?php $Table->display(); ?>
		</div>
<?php
	}
}

if (class_exists('WP_List_Table') == FALSE) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Active_Phone_Menu_Table_Create extends WP_List_Table
{
	public function prepare_items()
	{
		$columns    = $this->get_columns();
		$hidden     = $this->get_hidden_columns();
		$sortable   = $this->get_sortable_columns();
		$data       = $this->table_data();

		usort($data, array(&$this, 'sort_data'));

		$i = 0;
		foreach ($data as $key) {
			if (isset($key['available_date'])) {
				$data[$i]['available_date'] = date("F Y", strtotime($key['available_date']));
			}
			$i++;
		}

		$perPage        = 20;
		$currentPage    = $this->get_pagenum();
		$totalItems     = count($data);
		$this->set_pagination_args(array(
			'total_items' => $totalItems,
			'per_page'    => $perPage
		));

		$data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $data;
	}

	public function get_columns()
	{
		return array(
			'vacancy_name'	=> 'Вакансия',
			'employer_name'	=> 'Работодатель',
			'date' 			=> 'Дата',
		);
	}

	public function get_hidden_columns()
	{
		return array();
	}

	public function get_sortable_columns()
	{
		return array(
			'vacancy_name' => array('vacancy_name', false),
			'employer_name' => array('employer_name', false),
			'date' => array('date', false),
		);
	}

	private function table_data()
	{
		global $wpdb;
		$data = $wpdb->get_results("SELECT vacancy_name, employer_name, date FROM wp_activephone");
		$array = [];
		foreach ($data as $value) {

			array_push($array, (array)$value);
		};
		return $array;
	}

	public function column_default($item, $column_name)
	{
		switch ($column_name) {
			case 'vacancy_name':
			case 'employer_name':
			case 'date':
				return $item[$column_name];
			default:
				return print_r($item, true);
		}
	}

	private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'title';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }


        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }
}
