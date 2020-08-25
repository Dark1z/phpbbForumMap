# phpBB Forum Mapper
   
This is used to create presentable phpBB Forum structure.   
For example in form of Table, Dropdown, ...etc... , But the most comman is Table.   
   
This is an **Abstract Class** that need's to be **Inherited** by **Extended Class** , as follows :   
   
# Example of Extended Class from Abstract Class
```php
namespace <vendor>\<ext>\<path>;

require_once('forum_map.php');

use phpbb\db\driver\driver_interface as db_driver;
use dark1\phpbb\forum_map;

/**
 * EXT Forum Mapper.
 */
class forum_map_ext extends forum_map
{
	/**
	 * Constructor.
	 *
	 * @param \phpbb\db\driver\driver_interface		$db		Database object
	 */
	public function __construct(db_driver $db)
	{
		parent::__construct($db);
	}

	/**
	 * Get forum custom SQL Column.
	 *
	 * @return array
	 * @access protected
	 */
	protected function get_forums_cust_sql_col()
	{
		// For one forum table column
		return ['vendor_ext_enable'];
		// OR
		// For two or more forum table columns
		return ['vendor_ext_enable', 'vendor_ext_value'];
	}

	/**
	 * Get forum custom template row.
	 *
	 * @param array		$row	Forum row
	 *
	 * @return array
	 * @access protected
	 */
	protected function get_forum_cust_tpl_row($row)
	{
		$tpl_row = [];
		if ($row['forum_type'] == FORUM_POST)
		{
			// Array to be joined with original `$tpl_row`
			$tpl_row = [
				'ENABLE'	=> $row['vendor_ext_enable'],
				// If more than one
				'VALUE'		=> $row['vendor_ext_value'],
			];
		}
		return $tpl_row;
	}
}
```
   
# Usage of above Extended Class
```php
global $db, $template, $phpbb_container;
// Class Initialization
$forum_map_rsi = new forum_map_ext($db);
// OR
// phpBB Container Initialization , as-per definition in EXT's `services.yml` here it's `vendor.ext.forum_map_ext`
$forum_map_rsi = $phpbb_container->get('vendor.ext.forum_map_ext');

// Run Main with No Parameter
$forum_tpl_rows = $forum_map_rsi->main();
// OR
// Run Main with Custom `Padding Spacer` Parameter
$forum_tpl_rows = $forum_map_rsi->main('&nbsp;');
// OR
// Run Main with Custom `Padding Spacer` & `Padding Symbol` Parameters
$forum_tpl_rows = $forum_map_rsi->main('&nbsp;', '&#8627;');

// Use `$forum_tpl_rows` as-per your convenience
foreach ($forum_tpl_rows as $tpl_row)
{
	$template->assign_block_vars('forumrow', $tpl_row);
}
```
Done.  👍   
   
## GitHub Repository : [phpbbForumMap](https://github.com/dark-1/phpbbForumMap)   
   
## License [GPLv2](license.txt)   
   
--------------   
EnJoY  😃   
Best Regards.  👍   
   
