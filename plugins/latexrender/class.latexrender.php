<?php
  /**
   * LaTeX Rendering Class
   * Copyright (C) 2003  Benjamin Zeiss <zeiss@math.uni-goettingen.de>
   * Modifications by Jean-Christophe Dubacq <jcdubacq1@free.fr>
   *
   * This library is free software; you can redistribute it and/or
   * modify it under the terms of the GNU Lesser General Public
   * License as published by the Free Software Foundation; either
   * version 2.1 of the License, or (at your option) any later version.
   *
   * This library is distributed in the hope that it will be useful,
   * but WITHOUT ANY WARRANTY; without even the implied warranty of
   * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
   * Lesser General Public License for more details.
   *
   * You should have received a copy of the GNU Lesser General Public
   * License along with this library; if not, write to the Free Software
   * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
   * --------------------------------------------------------------------
   * @author Benjamin Zeiss <zeiss@math.uni-goettingen.de>
   * @version v0.8.9
   * @package latexrender
   *
   * This version includes Mike Boyle's modifications to allow vertical
   * offset of LaTeX formulae
   * This version includes Jean-Christophe Dubacq's modifications to allow
   * better offset of LaTeX formulae
   * This version includes Jean-Christophe Dubacq's simplifications to better
   * fit Dotclear 2
   */

class LatexRender {
  var $_picture_path;
  var $_picture_path_httpd;
  var $_tmp_dir;
  var $_latex_path = '/usr/bin/latex';
  var $_dvips_path = '/usr/bin/dvips';
  var $_convert_path = '/usr/bin/convert';
  var $_identify_path='/usr/bin/identify';
  var $_formula_density = 120;
  var $_xsize_limit = 500;
  var $_ysize_limit = 500;
  var $_string_length_limit = 500;
  var $_font_size = 10;
  var $_latexclass = 'article';
  var $_tmp_filename;
  var $_latex_tags_blacklist = 
    array(
	  'include','def','command','loop','repeat','open','toks','output',
	  'input','catcode','name','^^',
	  '\every','\errhelp','\errorstopmode','\scrollmode','\nonstopmode',
	  '\batchmode','\read','\write','csname','\newhelp','\uppercase',
	  '\lowercase','\relax','\aftergroup',
	  '\afterassignment','\expandafter','\noexpand','\special'
	  );
  var $_errorcode;
  var $_errorextra;
  var $_wronginit=false;
  var $_initerrorcode;
  var $_initerrorextra;
  
  /**
   * Initializes the class
   *
   * @param object core object
   */
  function LatexRender($core) {
    $options=LatexRender::getConfig();
    $this->_formula_density = $options['formuladensity'];
    $this->_method = $options['method'];
    $this->_latexclass = $options['latexclass'];
    $this->_font_size = $options['fontsize'];
    $this->_string_length_limit = $options['stringlengthlimit'];
    $this->_xsize_limit = $options['xsizelimit'];
    $this->_ysize_limit = $options['ysizelimit'];
    $this->_latex_path = $options['latexpath'];
    $this->_dvips_path = $options['dvipspath'];
    $this->_convert_path = $options['convertpath'];
    $this->_identify_path = $options['identifypath'];
    $latexrender_path_http = $core->blog->settings->public_url.'/latexrender/';
    if (strncmp($core->blog->settings->public_path,'/',1)==0) {
        $latexrender_path = $core->blog->settings->public_path.'/latexrender/';
    } else {
        $latexrender_path = dirname(__FILE__).'/../../'.
      $core->blog->settings->public_path.'/latexrender/';
    }
    $this->_picture_path = $latexrender_path.'pictures';
    if (defined('DC_CONTEXT_ADMIN')) {
      $url=$core->blog->url;
      if (substr($latexrender_path_http,0,1) == '/') {
	$url=preg_replace('|(^[a-z]{3,}://.*?)(/.*$)|','$1',$core->blog->url);
      }
      $this->_picture_path_httpd = $url.
	$latexrender_path_http.'pictures';
    } else {
      $this->_picture_path_httpd = $latexrender_path_http.'pictures';
    }
    $this->_tmp_dir = $latexrender_path.'tmp';
    files::makeDir($this->_picture_path,true);
    files::makeDir($this->_tmp_dir,false);
    $this->_tmp_filename = md5(rand());
    foreach (array($this->_latex_path,$this->_dvips_path,
		   $this->_convert_path,$this->_identify_path) as $j) {
      if (!is_file($j)) {
	$this->_initerrorcode = 'executable not found';
	$this->_initerrorextra = $j;
	$this->_wronginit=true;
      }
    }
  }

  // ===================
  // public functions
  // ===================

  public static function defaultConfig() {
    $base=array(
		'xsizelimit' => '500',
		'ysizelimit' => '500',
		'stringlengthlimit' => '500',
		'formuladensity' => '120',
		'latexclass' => 'article',
		'fontsize' => '10',
		'method' => 'old'
		);
    $commands=array('latex','dvips','convert','identify');
    foreach ($commands as $i) {
      $base[$i.'path']='/usr/bin/'.$i;
    }

    return $base;
  }
  public static function getConfig() {
    global $core;
    $base = LatexRender::defaultConfig();
    if ($core->blog->settings->get('latexrender_options',true) === null) {
      // First appearance of options combo on any blog
      $core->blog->settings->setNameSpace('latexrender');
      $core->blog->settings->put('latexrender_options',
				 '','string',
				 'LatexRender options',true,true);
    }
    
    $user = $core->blog->settings->latexrender_options;
    if ($user === null) {
      $user = $base;
    } else {
      $user = @unserialize($user);
      if (!is_array($user)) {
	$user = array();
      }
      $user = array_merge($base,$user); 
    }
    return $user;
  }
  public static function storeConfig($config,$oldconfig=null) {
    global $core;
    $fixed_options=array();
    $base = LatexRender::defaultConfig();
    foreach ($config as $name => $value) {
      if ((!isset($base[$name])) ||
	  ($base[$name] != $value)) {
	$fixed_options[$name]=$value;
      }
    }
    if ($oldconfig) {
      foreach ($config as $name => $value) {
	if ((!isset($oldconfig[$name])) ||
	    ($oldconfig[$name] != $value)) {
	  $fixed_options[$name]=$value;
	}
      }

    }
    if (count($fixed_options)) {
      $core->blog->settings->setNameSpace('latexrender');
      $core->blog->settings->put('latexrender_options',
				 serialize($fixed_options));
    }
  }

  /**
   * Returns the last error code
   *
   */
  public function getError() {
    return(__($this->_errorcode).' {'.$this->_errorextra.'}');
  }

  /**
   * Tries to match the LaTeX Formula given as argument against the
   * formula cache. If the picture has not been rendered before, it'll
   * try to render the formula and drop it in the picture cache directory.
   *
   * @param string formula in LaTeX format
   * @returns the webserver based URL to a picture which contains the
   * requested LaTeX formula. If anything fails, the resultvalue is false.
   */
  public function getFormulaHTML($latex_formula,$color='000000',$force=false) {
    $aurl=$this->getFormulaURL($latex_formula,$color,$force);
    $url = $aurl[0];
    $depth=$aurl[1];
    if ($depth) {
      $style_css = ' style="vertical-align:-'.$depth.';"';
    }
    $alt_latex_formula = htmlentities($latex_formula, ENT_QUOTES);
    $alt_latex_formula = str_replace("\r",'&#13;',$alt_latex_formula);
    $alt_latex_formula = str_replace("\n",'&#10;',$alt_latex_formula);
    if ($url) {
      $html='<img class="latex" src="'.$url.'" title="'.$alt_latex_formula.'" alt="'.$alt_latex_formula.'" '.$style_css.' />';
    } else {
      $html='<strong>[Unparsable formula ('.$this->getError().'): '.$alt_latex_formula.']</strong>';
    }
    return $html;
  }
    
  /**
   * Tries to match the LaTeX Formula given as argument against the
   * formula cache. If the picture has not been rendered before, it'll
   * try to render the formula and drop it in the picture cache directory.
   *
   * @param string formula in LaTeX format
   * @returns the webserver based URL to a picture which contains the
   * requested LaTeX formula. If anything fails, the resultvalue is false.
   */
  public function getFormulaURL($latex_formula,$color,$force) {
    // circumvent certain security functions of web-software which
    // is pretty pointless right here
    if ($this->_wronginit) {
      $this->_errorcode=$this->_initerrorcode;
      $this->_errorextra=$this->_initerrorextra;
      return array(false,0);
    } else {
      $this->_errorcode='';
      $this->_errorextra='';	
    }
    $latex_formula = preg_replace('/&gt;/i', '>', $latex_formula);
    $latex_formula = preg_replace('/&lt;/i', '<', $latex_formula);
    $formula_hash = md5($latex_formula);
    $filename = $formula_hash;
    if ($color!='000000') {
      $filename=$color.'X'.$filename.'.';
    } else {
      $filename=$filename.'.';
    }
    $full_path_filename = $this->_picture_path.'/'.$filename.'png';
    $full_path_filename_depth = $this->_picture_path.'/'.$filename.'depth';
    if ($force) {
      if (is_file($full_path_filename)) {
	unlink($full_path_filename);
      }
      if (is_file($full_path_filename_depth)) {
	unlink($full_path_filename_depth);
      }
    }
    if (!is_file($full_path_filename) || $force) {
      // security filter: reject too long formulas
      if (strlen($latex_formula) > $this->_string_length_limit) {
	$this->_errorcode = 'formula too long';
	$this->_errorextra = strlen($latex_formula).'>'.$this->_string_length_limit;
	return array(false,0);
      }
	
      // security filter: try to match against LaTeX-Tags Blacklist
      for ($i=0;$i<sizeof($this->_latex_tags_blacklist);$i++) {
	if (stristr($latex_formula,$this->_latex_tags_blacklist[$i])) {
	  $this->_errorcode = 'blacklisted TeX operator';
	  $this->_errorextra = $this->_latex_tags_blacklist[$i];
	  return array(false,0);
	}
      }
	
      // security checks assume correct formula, let's render it
      if (!($this->renderLatex($latex_formula,$color,$this->_picture_path.'/'.$filename))) {
	// errorcode and errorextra should already be filled-in
	return array(false,0);
      }
      if (!is_file($full_path_filename)) {
	$this->_errorcode = 'file not moved correctly';
	$this->_errorextra = $full_path_filename;
	return array(false,0);
      }
    }
    if (is_file($full_path_filename_depth)) {
      $depth=file_get_contents($full_path_filename_depth);
      $depth=substr($depth,0,-1);
    } else {
      $depth='0pt';
    }
    return array($this->_picture_path_httpd.'/'.$filename.'png',
		 $depth
		 );
  }
  public function searchCache($clean=0) {
    $count=0;
    $dp=opendir($this->_picture_path);
    while ($file=readdir($dp)) {
      if ($file != '.' && $file != '..') {
	$count++;
	if ($clean) { unlink($this->_picture_path.'/'.$file); }
      }
    }
    closedir($dp);
    return $count;
  }
  
  // ====================
  // private functions
  // ====================
      
  /**
   * wraps a minimalistic LaTeX document around the formula and returns
   * a string containing the whole document as string. Customize if you
   * want other fonts for example.
   *
   * @param string formula in LaTeX format
   * @returns minimalistic LaTeX document containing the given formula
   */
  private function wrap_formula($latex_formula) {
    $string  = '\documentclass['.$this->_font_size.'pt]{'.$this->_latexclass."}\n";
    $string .= "\\usepackage[latin1]{inputenc}\n";
    $string .= "\\usepackage{amsmath}\n";
    $string .= "\\usepackage{amsfonts}\n";
    $string .= "\\usepackage{amssymb}\n";
    $string .= "\\pagestyle{empty}\n";
    $string .= "\\newsavebox{\\formulabox}\n";
    $string .= "\\newlength{\\formulawidth}\n";
    $string .= "\\newlength{\\formulaheight}\n";
    $string .= "\\newlength{\\formuladepth}\n";
    $string .= "\\setlength{\\topskip}{0pt}\n";
    $string .= "\\setlength{\\parindent}{0pt}\n";
    $string .= "\\setlength{\\abovedisplayskip}{0pt}\n";
    $string .= "\\setlength{\\belowdisplayskip}{0pt}\n";
    $string .= "\\begin{lrbox}{\\formulabox}\n";
    $string .= $latex_formula."\n";
    $string .= "\\end{lrbox}\n";
    $string .= "\\settowidth {\\formulawidth}  {\\usebox{\\formulabox}}\n";
    $string .= "\\settoheight{\\formulaheight} {\\usebox{\\formulabox}}\n";
    $string .= "\\settodepth {\\formuladepth}  {\\usebox{\\formulabox}}\n";
    $string .= "\\newwrite\\foo\n";
    $string .= "\\immediate\\openout\\foo=\\jobname.depth\n";
    $string .= "    \\setlength{\\formuladepth}{1.25\\formuladepth}\n";
    $string .= "    \\addtolength{\\formuladepth}{0.5pt}\n";
    $string .= "    \\immediate\\write\\foo{\\the\\formuladepth}\n";
    $string .= "\\closeout\\foo\n";
    $string .= "\\begin{document}\n";
    $string .= "\\usebox{\\formulabox}\n";
    $string .= "\\end{document}\n";

    return $string;
  }
      
  /**
   * returns the dimensions of a picture file using 'identify' of the
   * imagemagick tools. The resulting array can be adressed with either
   * $dim[0] / $dim[1] or $dim['x'] / $dim['y']
   *
   * @param string path to a picture
   * @returns array containing the picture dimensions
   */
  private function getDimensions($filename) {
    $output=exec($this->_identify_path.' '.$filename);
    $result=explode(' ',$output);
    $dim=explode('x',$result[2]);
    $dim['x'] = $dim[0];
    $dim['y'] = $dim[1];
	
    return $dim;
  }
      
  /**
   * Renders a LaTeX formula by the using the following method:
   *  - write the formula into a wrapped tex-file in a temporary directory
   *    and change to it
   *  - Create a DVI file using latex (tetex)
   *  - Convert DVI file to Postscript (PS) using dvips (tetex)
   *  - convert, trim and add transparancy by using 'convert' from the
   *    imagemagick package.
   *  - Save the resulting image to the picture cache directory using an
   *    md5 hash as filename. Already rendered formulas can be found
   *    directly this way.
   *
   * @param string LaTeX formula
   * @returns true if the picture has been successfully saved to the picture
   *          cache directory
   */
  function renderLatex($latex_formula,$color,$filenamenoext) {
    $colorstring='#'.$color;
    $latex_document = $this->wrap_formula($latex_formula);
    $current_dir = getcwd();
    chdir($this->_tmp_dir);
    // create temporary latex file
    $fp = fopen($this->_tmp_dir.'/'.$this->_tmp_filename.'.tex','w');
    fputs($fp,$latex_document);
    fclose($fp);
	
    // create temporary dvi file
    $command = $this->_latex_path.' --interaction=nonstopmode '.$this->_tmp_filename.'.tex';
    exec($command,$output,$status_code);
    if ($status_code) {
      $this->cleanTemporaryDirectory();
      chdir($current_dir);
      $this->_errorcode = 'compilation failed';
      $this->_errorextra = implode(' ',$output);
      return false;
    }
    unset($output);

    // convert dvi file to postscript using dvips
    $command = $this->_dvips_path.' -E '.$this->_tmp_filename.'.dvi -o '.$this->_tmp_filename.'.ps';
    exec($command,$output,$status_code);
    if ($status_code) {
      $this->cleanTemporaryDirectory();
      chdir($current_dir);
      $this->_errorcode = 'compilation failed';
      $this->_errorextra = $command;
      return false;
    }

    // imagemagick convert ps to image and trim picture
    // test whether the old or new method should be used
    unset($output);
    if ($this->_method == 'new') {
      $command = $this->_convert_path.
	' -channel RGBA'.
	' -density '.$this->_formula_density.
	' -trim '.$this->_tmp_filename.'.ps '.
	"-fill '".$colorstring."' -colorize 100,100,100  ".
	$this->_tmp_filename.'.png';
    } else {
      $command = $this->_convert_path.
	' -density '.$this->_formula_density.
	' -trim -transparent \"#FFFFFF\" '.$this->_tmp_filename.'.ps '.
	$this->_tmp_filename.'.png';
    }
    $status_code = exec($command);
    exec($command,$output,$status_code);
    if ($status_code) {
      $this->cleanTemporaryDirectory();
      chdir($current_dir);
      $this->_errorcode = 'compilation failed';
      $this->_errorextra = $command.' -> '.implode(' ',$output);
      return false;
    }

    // test picture for correct dimensions
    $dim = $this->getDimensions($this->_tmp_filename.'.png');

    if ( ($dim['x'] > $this->_xsize_limit) or ($dim['y'] > $this->_ysize_limit)) {
      $this->cleanTemporaryDirectory();
      chdir($current_dir);
      $this->_errorcode = 'image too large';
      $this->_errorextra = $dim['x'].'x'.$dim['y'].'>>'.
	$this->_xsize_limit.'x'.$this->_ysize_limit;
      return false;
    }

    // copy temporary formula file to cached formula directory
    $latex_hash = md5($latex_formula);
    // offset: change file name to include depth information
    $depthfile = $this->_tmp_filename.'.depth';
    if ($color != '000000') {
      $latex_hash=$color.'X'.$latex_hash;
    }
	
    $status_code = (copy($this->_tmp_filename.'.png',$filenamenoext.'png'));
    if (!$status_code) {
      chdir($current_dir);
      $this->cleanTemporaryDirectory();
      $this->_errorcode = 'Could not copy file';
      $this->_errorextra = $this->_tmp_filename.'.png';
    }
    if(is_readable($depthfile)) {
      $offset = file($depthfile);
      $status_code = (copy($this->_tmp_filename.'.depth',$filenamenoext.'depth'));
      if (!$status_code) {
	$this->_errorcode = 'Could not copy file';
	$this->_errorextra = $this->_tmp_filename.'.depth';
      }
    } else {
      $offset='0pt';
    }
    chdir($current_dir);
    $this->cleanTemporaryDirectory();
    return true;
  }

  /**
   * Cleans the temporary directory
   */
  function cleanTemporaryDirectory() {
    $term=array('tex','aux','log','depth','dvi','ps','png');
    foreach ($term as $i) {
      $fn=$this->_tmp_dir.'/'.$this->_tmp_filename.$i;
      if (is_file($fn)) {
	unlink($fn);
      }
    }
  }

  }

?>
