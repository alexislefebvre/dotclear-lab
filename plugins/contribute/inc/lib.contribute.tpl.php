<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Contribute, a plugin for Dotclear 2
# Copyright (C) 2008,2009,2010 Moe (http://gniark.net/)
#
# Contribute is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Contribute is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons :
# <http://www.famfamfam.com/lab/icons/silk/>
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

/**
@ingroup Contribute
@brief Template
*/
class contributeTpl
{
	/**
	display a message
	@return	<b>string</b> PHP block
	*/
	public static function ContributeMessage()
	{
		return('<?php echo($_ctx->contribute->message); ?>');
	}
	
	/**
	display the help
	@return	<b>string</b> PHP block
	*/
	public static function ContributeHelp()
	{
		return('<?php echo($_ctx->contribute->help); ?>');
	}
	
	/**
	display preview
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ContributePreview($attr,$content)
	{
		return
		'<?php if ($_ctx->contribute->preview) : ?>'."\n".
		$content."\n".
		'<?php endif; ?>';
	}
	
	/**
	display form
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ContributeForm($attr,$content)
	{
		return
		'<?php if ($_ctx->contribute->form) : ?>'."\n".
		$content."\n".
		'<?php endif; ?>';
	}
	
	/**
	if
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	
	we can't use <tpl:ContributeIf> in another <tpl:ContributeIf> block yet
	
	<tpl:ContributeIf something="1">
		<tpl:ContributeIf something_again="1">
		</tpl:ContributeIf>
	</tpl:ContributeIf>
	
	will return :
	
	<?php if () : ?>
		<tpl:ContributeIf something_again="1">
		<?php endif; ?>>
	</tpl:ContributeIf>
	*/
	public static function ContributeIf($attr,$content)
	{
		$if = array();
		$operator = isset($attr['operator']) ?
			self::getOperator($attr['operator']) : '&&';
		
		if (isset($attr['message']))
		{
			$if[] = '$_ctx->contribute->message == \''.$attr['message'].'\'';
		}
		
		if (isset($attr['choose_format']))
		{
			if ($attr['choose_format'] == '1')
			{
				$if[] = '$_ctx->contribute->choose_format';
			}
			else
			{
				$if[] = '$_ctx->contribute->choose_format !== true';
			}
		}
		
		if (isset($attr['format']))
		{
			$format = trim($attr['format']);
			$sign = '=';
			if (substr($format,0,1) == '!')
			{
				$sign = '!';
				$format = substr($format,1);
			}
			foreach (explode(',',$format) as $format)
			{
				$if[] = '$_ctx->posts->post_format '.$sign.'= "'.$format.'"';
			}
		}
		
		if (isset($attr['excerpt']))
		{
			$if[] = '$core->blog->settings->contribute_allow_excerpt';
		}
		
		if (isset($attr['category']))
		{
			$if[] = '$core->blog->settings->contribute_allow_category';
		}
		
		if (isset($attr['tags']))
		{
			$if[] = '$core->blog->settings->contribute_allow_tags';
		}
		
		if (isset($attr['mymeta']))
		{
			$if[] = '$core->blog->settings->contribute_allow_mymeta';
		}
		
		if (isset($attr['notes']))
		{
			$if[] = '$core->blog->settings->contribute_allow_notes';
		}
		
		if (isset($attr['author']))
		{
			$if[] = '$core->blog->settings->contribute_allow_author';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.
				$content."\n".
				'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	/**
	Get operator
	@param	op	<b>string</b>	Operator
	@return	<b>string</b> Operator
	\see /dotclear/inc/public/class.dc.template.php > getOperator()
	*/
	protected static function getOperator($op)
	{
		switch (strtolower($op))
		{
			case 'or':
			case '||':
				return '||';
			case 'and':
			case '&&':
			default:
				return '&&';
		}
	}
	
	/**
	if name and email are required
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ContributeIfNameAndEmailAreNotRequired($attr,
		$content)
	{
		$if = '$core->blog->settings->contribute_require_name_email !== true';
		
		return '<?php if('.$if.') : ?>'.
			$content."\n".
			'<?php endif; ?>';
	}
	
	/**
	if an element is selected
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ContributeIfSelected($attr,$content)
	{
		$if = array();
		$operator = '&&';
		
		if (isset($attr['format']))
		{
			$if[] = '$_ctx->formaters->format === $_ctx->posts->post_format';
		}
		
		if (isset($attr['category']))
		{
			$if[] = '$_ctx->categories->cat_id == $_ctx->posts->cat_id';
		}
		
		if (isset($attr['mymeta']))
		{
			$if[] = 'isset($_ctx->posts->mymeta[$_ctx->mymeta->id])';
			$if[] = '$_ctx->mymetavalues->id == $_ctx->posts->mymeta[$_ctx->mymeta->id]';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.
				$content."\n".
				'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	/**
	Formaters
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ContributeFormaters($attr,$content)
	{
		return
		'<?php '.
		# initialize for <tpl:LoopPosition>
		'$_ctx->formaters = $_ctx->contribute->formaters;'.
		'while ($_ctx->formaters->fetch()) : ?>'."\n".
		$content."\n".
		'<?php endwhile; ?>';
	}
	
	/**
	Format
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ContributeFormat($attr,$content)
	{
		return('<?php echo(html::escapeHTML($_ctx->formaters->format)); ?>');
	}
	
	/**
	Entry Excerpt
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryExcerpt($attr)
	{
		return('<?php echo(html::escapeHTML($_ctx->posts->post_excerpt)); ?>');
	}
	
	/**
	Entry Content
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryContent($attr)
	{
		return('<?php echo(html::escapeHTML($_ctx->posts->post_content)); ?>');
	}
	
	/**
	Category ID
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeCategoryID($attr)
	{
		return('<?php echo($_ctx->categories->cat_id); ?>');
	}
	
	/**
	Category spacer
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeCategorySpacer($attr)
	{
		$string = '&nbsp;&nbsp;';
		
		if (isset($attr['string'])) {$string = $attr['string'];}
		
		return('<?php echo(str_repeat(\''.$string.'\','.
			'$_ctx->categories->level-1)); ?>');
	}
	
	/**
	Filter to display only unselected tags
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryTagsFilter($attr,$content)
	{
		return
		'<?php '.
		'if (!in_array($_ctx->meta->meta_id,$_ctx->contribute->selected_tags)) : ?>'."\n".
		$content."\n".
		'<?php endif; ?>';
	}
	
	/**
	Loop on My Meta values
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryMyMeta($attr,$content)
	{
		return
		'<?php '.
		# initialize for <tpl:LoopPosition>
		'$_ctx->mymeta = contribute::getMyMeta($_ctx->contribute->mymeta);'.
		'while ($_ctx->mymeta->fetch()) : ?>'."\n".
		$content."\n".
		'<?php endwhile; ?>';
	}
	
	/**
	test on My Meta values
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryMyMetaIf($attr,$content)
	{
		$if = array();
		$operator = '&&';
		
		if (isset($attr['type']))
		{
			$if[] = '$_ctx->mymeta->type === \''.$attr['type'].'\'';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.
				$content."\n".
				'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	public static function ContributeEntryMyMetaIfChecked($attr,$content)
	{
		return('<?php '.
		'if (isset($_ctx->posts->mymeta[$_ctx->mymeta->id])) :'.
		'if ($_ctx->posts->mymeta[$_ctx->mymeta->id] == 1) : ?>'.
		$content.
		'<?php endif;'.
		'endif; ?>');
	}
	
	/**
	My Meta ID
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryMyMetaID($attr)
	{
		return('<?php echo($_ctx->mymeta->id); ?>');
	}
	
	/**
	My Meta Prompt
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryMyMetaPrompt($attr)
	{
		return('<?php echo($_ctx->mymeta->prompt); ?>');
	}
	
	/**
	My Meta value
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryMyMetaValue($attr)
	{
		return('<?php '.
		'if (isset($_ctx->posts->mymeta[$_ctx->mymeta->id])) :'.
		'echo($_ctx->posts->mymeta[$_ctx->mymeta->id]);'.
		'endif; ?>');
	}
	
	/**
	My Meta values
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryMyMetaValues($attr,$content)
	{
		return
		'<?php '.
		# initialize for <tpl:LoopPosition>
		'$_ctx->mymetavalues = contribute::getMyMetaValues($_ctx->mymeta->values);'.
		'while ($_ctx->mymetavalues->fetch()) : ?>'."\n".
		$content."\n".
		'<?php endwhile; ?>';
	}
	
	/**
	My Meta values : ID
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryMyMetaValuesID($attr)
	{
		return('<?php echo($_ctx->mymetavalues->id); ?>');
	}
	
	/**
	My Meta values : Description
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryMyMetaValuesDescription($attr)
	{
		return('<?php echo($_ctx->mymetavalues->description); ?>');
	}
	
	/**
	Entry notes
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function ContributeEntryNotes($attr)
	{
		return('<?php echo(html::escapeHTML($_ctx->posts->post_notes)); ?>');
	}
}

?>