<?php
/**
 *
 * phpBB Forum Mapper. A helper class for the phpBB Forum Software package.
 *
 * @author Dark❶, https://dark1.tech
 * @version 1.0.0
 * @source https://github.com/dark-1/phpbbForumMap
 * @copyright (c) 2020, Dark❶, https://dark1.tech
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vendor\ext\path;

/**
 * @ignore
 */
use phpbb\db\driver\driver_interface as db_driver;

/**
 * phpBB Forum Mapper.
 */
abstract class forum_map
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var int Latest Right ID*/
	private $right;

	/** @var string Padding */
	private $padding;

	/** @var string Padding Spacer */
	private $padding_spacer;

	/** @var string Padding Symbol */
	private $padding_symbol;

	/** @var array Forum Template Row */
	private $forum_tpl_row;

	/** @var array All Forum Data */
	private $forums;

	/** @var array Forum SQL Column */
	private $sql_col;

	/** @var array Store Padding for each Forum */
	private $padding_store;

	/** @var string Default Padding Spacer */
	const PADDING_SPACER	= '&nbsp; &nbsp; &nbsp;';

	/** @var string Default Padding Symbol */
	const PADDING_SYMBOL	= '&nbsp; &#8627; &nbsp;';

	/**
	 * Constructor.
	 *
	 * @param \phpbb\db\driver\driver_interface		$db		Database object
	 */
	public function __construct(db_driver $db)
	{
		$this->db				= $db;
		$this->right			= 0;
		$this->padding			= '';
		$this->padding_spacer	= '';
		$this->padding_symbol	= '';
		$this->forum_tpl_row	= [];
		$this->forums			= [];
		$this->sql_col			= [];
		$this->padding_store	= ['0' => ''];
	}

	/**
	 * Display the Forum options.
	 *
	 * @param string	$padding_spacer		Padding Spacer
	 * @param string	$padding_symbol		Padding Symbol
	 *
	 * @return array
	 * @access public
	 */
	public function main($padding_spacer = '', $padding_symbol = '')
	{
		$this->padding_spacer	= !empty($padding_spacer) ? $padding_spacer : self::PADDING_SPACER;
		$this->padding_symbol	= !empty($padding_symbol) ? $padding_symbol : self::PADDING_SYMBOL;
		$this->sql_col = $this->get_forums_cust_sql_col();

		$this->get_forums();
		$this->parse_forums();

		return $this->forum_tpl_row;
	}

	/**
	 * Get forums.
	 *
	 * @return void
	 * @access private
	 */
	private function get_forums()
	{
		$sql = 'SELECT forum_id, forum_type, forum_name, parent_id, left_id, right_id' . (!empty($this->sql_col) ? ', ' . implode(', ', $this->sql_col) : '') . ' FROM ' . FORUMS_TABLE . ' ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->forums[] = $row;
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Parse forums.
	 *
	 * @return void
	 * @access private
	 */
	private function parse_forums()
	{
		foreach ($this->forums as $row)
		{
			$this->get_forum_padding($row['parent_id'], $row['left_id'], $row['right_id']);
			$tpl_row = $this->get_forum_tpl_row($row) + $this->get_forum_cust_tpl_row($row);

			if (!empty($tpl_row))
			{
				$this->forum_tpl_row[] = $tpl_row;
			}
		}
	}

	/**
	 * Get forum padding.
	 *
	 * @param int		$parent_id		Forum parent ID
	 * @param int		$left_id		Forum left ID
	 * @param int		$right_id		Forum right ID
	 *
	 * @return void
	 * @access private
	 */
	private function get_forum_padding($parent_id, $left_id, $right_id)
	{
		if ($left_id < $this->right)
		{
			$this->padding .= $this->padding_spacer;
			$this->padding_store[$parent_id] = $this->padding;
		}
		else if ($left_id > $this->right + 1)
		{
			$this->padding = (isset($this->padding_store[$parent_id])) ? $this->padding_store[$parent_id] : '';
		}
		$this->right = $right_id;
	}

	/**
	 * Get forum template row.
	 *
	 * @param array		$row	Forum row
	 *
	 * @return array
	 * @access private
	 */
	private function get_forum_tpl_row($row)
	{
		$tpl_row = [];
		// Normal forums have configuration setting
		if ($row['forum_type'] == FORUM_POST)
		{
			// The labels for all the inputs are supposed to be constructed based on the forum IDs to make it easy to know which
			$tpl_row = [
				'S_IS_CAT'		=> false,
				'FORUM_PAD'		=> $this->padding . $this->padding_symbol,
				'FORUM_NAME'	=> $row['forum_name'],
				'FORUM_ID'		=> $row['forum_id'],
			];
		}
		// Category forums are displayed for organizational purposes, but have no configuration setting
		else if ($row['forum_type'] == FORUM_CAT)
		{
			$tpl_row = [
				'S_IS_CAT'		=> true,
				'FORUM_PAD'		=> $this->padding . $this->padding_symbol,
				'FORUM_NAME'	=> $row['forum_name'],
			];
		}
		// Other forum types (Example: links) are ignored
		return $tpl_row;
	}

	/**
	 * Get forum custom SQL Column.
	 *
	 * @return array
	 * @access protected
	 */
	abstract protected function get_forums_cust_sql_col();
	/** @example :
	{
		// For one forum table coloumn
		return ['dark1_ext_enable'];
		// OR
		// For two or more forum table coloumn
		return ['dark1_ext_enable', 'dark1_ext_value'];
	}
	*/

	/**
	 * Get forum custom template row.
	 *
	 * @param array		$row	Forum row
	 *
	 * @return array
	 * @access protected
	 */
	abstract protected function get_forum_cust_tpl_row($row);
	/** @example :
	{
		$tpl_row = [];
		if ($row['forum_type'] == FORUM_POST)
		{
			// Array to be joined with original $tpl_row
			$tpl_row = [
				'ENABLE'	=> $row['dark1_ext_enable'],
				// If more than one
				'VALUE'		=> $row['dark1_ext_value'],
			];
		}
		return $tpl_row;
	}
	*/
}
