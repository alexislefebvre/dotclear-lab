<?php

if (!($_s instanceof dbStruct)) { throw new Exception('No valid schema object'); }

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
