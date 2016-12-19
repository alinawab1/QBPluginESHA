<?php

/**
 * Fetching the TimeZone dropdown from TimeDate class
 * @return type
 */

function getTimezoneList() {
	$timezoneList = TimeDate::getTimezoneList();
	return $timezoneList;
}

/**
 * 
 * @return type
 */
function getAdminUserList() {
	$adminUserList = get_admin_user_list(false, 'Active');
	return $adminUserList;
}

function get_admin_user_list ($add_blank=true, $status="Active", $user_id='', $portal_filter=' AND portal_only=0 ') {
	global $locale;

    if (empty($locale)) {
        $locale = Localization::getObject();
    }

    $db = DBManagerFactory::getInstance();

    // Pre-build query for use as cache key
    // Including deleted users for now.
    if (!empty($status)) {
        $query = "SELECT id, first_name, last_name, user_name FROM users ";
        $where = "status='$status'" . $portal_filter;
    }

    $user = BeanFactory::getBean('Users');
    $user->addVisibilityFrom($query);
    $query .= " WHERE is_admin='1' AND $where ";
    $user->addVisibilityWhere($query);

    if (!empty($user_id)) {
        $query .= " OR id='{$user_id}'";
    }
    $query .= ' ORDER BY user_name ASC';

    if (empty($user_array)) {
        $temp_result = Array();

        $GLOBALS['log']->debug("get_user_array query: $query");
        $result = $db->query($query, true, "Error filling in user array: ");

		// Get the id and the name.
		while($row = $db->fetchByAssoc($result)) {
			if(showFullName()) {
				if(isset($row['last_name'])) { // cn: we will ALWAYS have both first_name and last_name (empty value if blank in db)
                    $temp_result[$row['id']] = $locale->formatName('Users', $row);
				} else {
					$temp_result[$row['id']] = $row['user_name'];
				}
			} else {
				$temp_result[$row['id']] = $row['user_name'];
			}
		}

        $user_array = $temp_result;
    }

    if ($add_blank) {
        $user_array[''] = '';
    }
	
	return $user_array;
}