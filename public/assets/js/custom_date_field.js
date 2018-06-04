function activateCustomDateFields()
{
	"use strict";

	jQuery('.customDateField').datepicker({ dateFormat: 'dd-mm-yy' });
}

jQuery(document).ready(function () {

	"use strict";

	activateCustomDateFields();
});
