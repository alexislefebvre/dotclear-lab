# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of TextMate Bundle for Dotclear 2.
#
# Copyright (c) 2009 Thomas Bouron
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

require ENV['TM_SUPPORT_PATH'] + '/lib/ui'
require ENV['TM_SUPPORT_PATH'] + '/lib/osx/plist'
require ENV['TM_SUPPORT_PATH'] + '/lib/exit_codes'
require ENV['TM_SUPPORT_PATH'] + '/lib/current_word'

module Dotclear

	def self.form()
		choices = [
			{ 'title' => 'combo', 'insert' => '(${1:\$nid},${2:\$data}${3:,${4:\$default},${5:\$class},${6:\$tabindex},${7:\$disabled},${8:\$extra_html}})' },
			{ 'title' => 'radio', 'insert' => '(${1:\$nid},${2:\$value}${3:,${4:\$checked},${5:\$class},${6:\$tabindex},${7:\$disabled},${8:\$extra_html}})' },
			{ 'title' => 'checkbox', 'insert' => '(${1:\$nid},${2:\$value}${3:,${4:\$checked},${5:\$class},${6:\$tabindex},${7:\$disabled},${8:\$extra_html}})' },
			{ 'title' => 'field', 'insert' => '(${1:\$nid},${2:\$size},${3:\$max}${4:,${5:\$default},${6:\$class},${7:\$tabindex},${8:\$disabled},${9:\$extra_html}})' },
			{ 'title' => 'password', 'insert' => '(${1:\$nid},${2:\$size},${3:\$max}${4:,${5:\$default},${6:\$class},${7:\$tabindex},${8:\$disabled},${9:\$extra_html}})' },
			{ 'title' => 'textArea', 'insert' => '(${1:\$nid},${2:\$cols},${3:\$rows}${4:,${5:\$default},${6:\$class},${7:\$tabindex},${8:\$disabled},${9:\$extra_html}})' }
		]
		t = TextMate::UI.menu(choices)
		
		if t == nil
			TextMate.exit_discard()
		end
		
		ret = "form::" + t['title'] + t['insert'] + "$0"
		
		TextMate.exit_insert_snippet(ret)
	end
	
	def self.dt()
		choices = [
			{ 'title' => 'str', 'insert' => '(${1:\$p}${2:,${3:\$ts},${4:\$tz}})' },
			{ 'title' => 'dt2str', 'insert' => '(${1:\$p},${2:\$ts}${3:,${4:\$tz}})' },
			{ 'title' => 'iso8601', 'insert' => '(${1:\$ts}${2:,${3:\$tz}})' },
			{ 'title' => 'rfc822', 'insert' => '(${1:\$ts}${2:,${3:\$tz}})' },
			{ 'title' => 'setTZ', 'insert' => '(${1:\$tz})' },
			{ 'title' => 'getTZ', 'insert' => '()' },
			{ 'title' => 'getTimeOffset', 'insert' => '(${1:\$tz}${2:,${3:\$ts}})' },
			{ 'title' => 'toUTC', 'insert' => '(${1:\$ts})' },
			{ 'title' => 'addTimeZone', 'insert' => '(${1:\$tz}${2:,${3:\$ts}})' },
			{ 'title' => 'getZones', 'insert' => '(${1:${2:\$flip},${3:\$groups}})' }
		]
		t = TextMate::UI.menu(choices)
		
		if t == nil
			TextMate.exit_discard()
		end
		
		ret = "dt::" + t['title'] + t['insert'] + "$0"
		
		TextMate.exit_insert_snippet(ret)
	end
	
	def self.file()
		choices = [
			{ 'title' => 'scandir', 'insert' => '(${1:\$dir}${2:,${3:\$order}})' },
			{ 'title' => 'getExtension', 'insert' => '(${1:\$file})' },
			{ 'title' => 'getMimeType', 'insert' => '(${1:\$file})' },
			{ 'title' => 'mimeTypes', 'insert' => '()' },
			{ 'title' => 'registerMimeTypes', 'insert' => '(${1:\$array})' },
			{ 'title' => 'isDeletable', 'insert' => '(${1:\$file})' },
			{ 'title' => 'deltree', 'insert' => '(${1:\$dir})' },
			{ 'title' => 'touch', 'insert' => '(${1:\$file})' },
			{ 'title' => 'makeDir', 'insert' => '(${1:\$file}${2:,${3:\$r}})' },
			{ 'title' => 'inheritChmod', 'insert' => '(${1:\$file})' },
			{ 'title' => 'putContent', 'insert' => '(${1:\$file}${2:,${3:\$f_content}})' },
			{ 'title' => 'size', 'insert' => '(${1:\$size})' },
			{ 'title' => 'str2bytes', 'insert' => '(${1:\$size})' },
			{ 'title' => 'uploadStatus', 'insert' => '(${1:\$file})' },
			{ 'title' => 'getDirList', 'insert' => '(${1:\$dirName})' }
		]
		t = TextMate::UI.menu(choices)
		
		if t == nil
	    TextMate.exit_discard()
		end
		
		ret = "file::" + t['title'] + t['insert'] + "$0"
		
		TextMate.exit_insert_snippet(ret)
	end
	
	def self.behavior()
		choices = OSX::PropertyList.load(File.read(ENV['TM_BUNDLE_SUPPORT'] + '/behaviors.plist'))
		t = TextMate::UI.menu(choices)
		
		if t == nil
			TextMate.exit_discard()
		end
		
		ret = "\\$core->addBehavior('" + t['title'] + "',array('${1:Callback class}','${2:Callback function}'));\n$0"
		
		TextMate.exit_insert_snippet(ret)
	end
	
	def self.functions()
		word = Word.current_word('a-zA-Z0-9_')
		choices = OSX::PropertyList.load(File.read(ENV['TM_BUNDLE_SUPPORT'] + '/functions.plist'))
		found = choices.find { |i| i['name'] == word }
		if found.is_a?(Hash)
			ret = '<code style="font-size: 1.2em;">' + found['definition'] + '</code><br />' + found['file']
		else
			ret = 'Sorry, there is no Dotclear function called : ' + word;
		end
		
		style = "font-family: 'Lucida Grande', sans-serif;"
		
		TextMate::UI.tool_tip('<p style="' + style + '">' + ret + '</p>', :format => :html)
		exit
	end
end