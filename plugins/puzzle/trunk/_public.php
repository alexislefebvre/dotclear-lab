<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Puzzle, a plugin for Dotclear.
# 
# Copyright (c) 2009 kévin lepeltier
# kevin@lepeltier.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

require_once (dirname(__FILE__).'/inc/class.tplpuzzle.php');

$core->tpl->addBlock('Entries',array('tplPuzzle','Entries'));
$core->tpl->addValue('PuzzlePart',array('tplPuzzle','PuzzlePart'));