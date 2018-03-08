<?

// Permission levels
// 1 = Exec
// 2 = Scheduler
// 3 = Discplinarian

if (isset($permission_level)){
	if ($permission_level == 1) {
		if (!($is_exec)) header("Location:./403.php"); 
	} else if ($permission_level == 2) {
		if (!($is_scheduler or $is_chair or $is_techchair or $is_vicechair)) header("Location:./403.php"); 
	} else if ($permission_level == 3) {
		if (!($is_disciplinarian or $is_chair or $is_techchair or $is_vicechair)) header("Location:./403.php"); 
	}
}

?>