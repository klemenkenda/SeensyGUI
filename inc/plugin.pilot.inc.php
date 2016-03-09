<?php

function pilotId() {
	global $id;
	$pilotPId = getAbel($id);
	
	switch ($pilotPId) {
		case 3: return "epex";
		break;
		case 5: return "miren";
		break;
		case 6: return "csi";
		break;
		case 7: return "iren-thermal";
		break;
		case 8: return "ntua";
		break;
		case 9: return "fir";
		break;
	}
	
	return "error";	
}

?>
