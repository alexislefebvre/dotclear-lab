<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'My Handlers', a plugin for Dotclear 2             *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'My Handlers' (see COPYING.txt);        *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

$__autoload['myHandlers'] = dirname(__FILE__).'/class.myhandlers.php';

$myHandlers = (array) @unserialize($core->blog->settings->my_handlers);
foreach ($myHandlers as $handler_name=>$handler_url)
{
	myHandlers::overrideHandler($handler_name,$handler_url);
}

unset($myHandlers,$handler_name,$handler_url);
?>