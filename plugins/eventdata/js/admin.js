/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of eventdata, a plugin for Dotclear 2.
 * 
 * Copyright (c) 2009 JC Denis and contributors
 * jcdenis@gdwd.com
 * 
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -- END LICENSE BLOCK ------------------------------------*/

$(function(){var localized_form=document.getElementById('edit-eventdata');var start=document.getElementById('eventdata_start');var end=document.getElementById('eventdata_end');if(localized_form!=undefined){var start_dtPick=new datePicker(start);start_dtPick.img_top='0.5em';start_dtPick.draw();var end_dtPick=new datePicker(end);end_dtPick.img_top='0.5em';end_dtPick.draw();}});