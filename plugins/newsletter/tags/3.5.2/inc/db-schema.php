<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Newsletter, a plugin for Dotclear 2.
# Copyright (C) 2009 Benoit de Marne, and contributors. All rights
# reserved.
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 3
# of the License, or (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# ***** END LICENSE BLOCK *****

if (!($_s instanceof dbStruct)) { 
	throw new Exception('No valid schema object'); 
}

// ====================================================================================================
// tables
// ====================================================================================================

// newsletter
$_s->newsletter
	->subscriber_id	('integer', 0, true)
	->blog_id			('varchar', 32, false)
	->email			('varchar', 255, false)
	->regcode			('varchar', 255, false)
	->state			('varchar', 255, false)
	->subscribed		('timestamp', 0, false, 'now()')
	->lastsent		('timestamp', 0, true)
	->modesend		('varchar', 10, true)
	
	->primary			('pk_newsletter', 'blog_id', 'subscriber_id')
	->unique			('uk_newsletter', 'email')
	;


// ====================================================================================================
// index de référence
// ====================================================================================================


// ====================================================================================================
// index de performance
// ====================================================================================================

$_s->newsletter->index	('idx_newsletter_blog_id', 'btree', 'blog_id');
$_s->newsletter->index	('idx_newsletter_email', 'btree', 'email');
$_s->newsletter->index	('idx_newsletter_lastsent', 'btree', 'lastsent');


// ====================================================================================================
// clées étrangères
// ====================================================================================================

$_s->newsletter->reference	('fk_newsletter_blog', 'blog_id', 'blog', 'blog_id', 'cascade', 'cascade');

?>
