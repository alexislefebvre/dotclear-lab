<?php
/**
 * �l�ments de configuration pour WikiRenderer 2.0dev
 * fichier pour la transformation de wiki vers XHTML1.0/strict
 * @author Laurent Jouanneau <jouanneau@netcourrier.com>
 * @copyright 2003-2004 Laurent Jouanneau
 * @module Wiki Renderer
 * @version 2.0.6
 * @since 26/09/2004
 * http://ljouanneau.com/softs/wikirenderer/
 * Thanks to all users who found bugs : Loic, Edouard Guerin, Sylvain, Ludovic L.
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
 *
 */

class WikiRendererConfig {
  /**
    * @var array   liste des tags inline
   */
   public $inlinetags= array(
      'strong' =>array('__','__',      null,null),
      'em'     =>array('\'\'','\'\'',  null,null),
      'code'   =>array('@@','@@',      null,null),
      'q'      =>array('^^','^^',      array('lang','cite'),null),
      'cite'   =>array('{{','}}',      array('title'),null),
      'acronym'=>array('??','??',      array('title'),null),
      'link'   =>array('[',']',        array('href','hreflang','title'),'wikibuildlink'),
      'image'  =>array('((','))',       array('src','alt','align','longdesc'),'wikibuildimage'),
      'anchor' =>array('~~','~~',      array('name'),'wikibuildanchor')
   );

   /**
   * liste des balises de type bloc autoris�es.
   * Attention, ordre important (p en dernier, car c'est le bloc par defaut..)
   */
   public $bloctags = array('title'=>true, 'list'=>true,
   'pre'=>true,'hr'=>true, 'blockquote'=>true,'definition'=>true,'table'=>true, 'p'=>true);


   public $simpletags = array('%%%'=>'<br />', ':-)'=>'<img src="laugh.png" alt=":-)" />');

   /**
    * @var   integer   niveau minimum pour les balises titres
    */
   public $minHeaderLevel=3;


   /**
    * indique le sens dans lequel il faut interpreter le nombre de signe de titre
    * true -> ! = titre , !! = sous titre, !!! = sous-sous-titre
    * false-> !!! = titre , !! = sous titre, ! = sous-sous-titre
    */
   public $headerOrder=false;
   public $escapeSpecialChars=true;
   public $inlineTagSeparator='|';
   public $blocAttributeTag='��';

   public $checkWikiWord = false;
   public $checkWikiWordFunction = null;

}

// ===================================== fonctions de g�n�rateur de code HTML sp�cifiques � certaines balises inlines

function wikibuildlink($contents, $attr){
   $cnt=count($contents);
   $attribut='';

   if($cnt >1){
      if($cnt> count($attr))
         $cnt=count($attr)+1;
      if(strpos($contents[1],'javascript:')!==false) // for security reason
         $contents[1]='#';

      for($i=1;$i<$cnt;$i++){
         $attribut.=' '.$attr[$i-1].'="'.$contents[$i].'"';
      }
   }else{
      if(strpos($contents[0],'javascript:')!==false) // for security reason
         $contents[0]='#';
      $attribut=' href="'.$contents[0].'"';
      if(strlen($contents[0]) > 40)
         $contents[0]=substr($contents[0],0,40).'(..)';
   }
   return '<a'.$attribut.'>'.$contents[0].'</a>';
}

function wikibuildanchor($contents, $attr){
   return '<a name="'.$contents[0].'"></a>';
}

function wikibuilddummie($contents, $attr){
   return (isset($contents[0])?$contents[0]:'');
}

function wikibuildimage($contents, $attr){
   $cnt=count($contents);
   $attribut='';
   if($cnt > 4) $cnt=4;
   switch($cnt){
      case 4:
         $attribut.=' longdesc="'.$contents[3].'"';
      case 3:
         if($contents[2]=='l' ||$contents[2]=='L' || $contents[2]=='g' || $contents[2]=='G')
            $attribut.=' style="float:left;"';
         elseif($contents[2]=='r' ||$contents[2]=='R' || $contents[2]=='d' || $contents[2]=='D')
            $attribut.=' style="float:right;"';
      case 2:
         $attribut.=' alt="'.$contents[1].'"';
      case 1:
      default:
         $attribut.=' src="'.$contents[0].'"';
         if($cnt == 1) $attribut.=' alt=""';
   }
   return '<img'.$attribut.' />';

}


// ===================================== d�claration des diff�rents bloc wiki

/**
 * traite les signes de types liste
 */
class WRB_list extends WikiRendererBloc {

   public $_previousTag;
   public $_firstItem;
   public $_firstTagLen;
   public $type='list';
   public $regexp="/^([\*#-]+)(.*)/";

   function open(){
      $this->_previousTag = $this->_detectMatch[1];
      $this->_firstTagLen = strlen($this->_previousTag);
      $this->_firstItem=true;

      if(substr($this->_previousTag,-1,1) == '#')
         return "<ol>\n";
      else
         return "<ul>\n";
   }
   function close(){
      $t=$this->_previousTag;
      $str='';

      for($i=strlen($t); $i >= $this->_firstTagLen; $i--){
          $str.=($t{$i-1}== '#'?"</li></ol>\n":"</li></ul>\n");
      }
      return $str;
   }

   function getRenderedLine(){
      $t=$this->_previousTag;
      $d=strlen($t) - strlen($this->_detectMatch[1]);
      $str='';

      if( $d > 0 ){ // on remonte d'un ou plusieurs cran dans la hierarchie...
         $l=strlen($this->_detectMatch[1]);
         for($i=strlen($t); $i>$l; $i--){
            $str.=($t{$i-1}== '#'?"</li></ol>\n":"</li></ul>\n");
         }
         $str.="</li>\n<li>";
         $this->_previousTag=substr($this->_previousTag,0,-$d); // pour �tre sur...

      }elseif( $d < 0 ){ // un niveau de plus
         $c=substr($this->_detectMatch[1],-1,1);
         $this->_previousTag.=$c;
         $str=($c == '#'?"<ol>\n<li>":"<ul>\n<li>");

      }else{
         $str=($this->_firstItem ? '<li>':'</li><li>');
      }
      $this->_firstItem=false;
      return $str.$this->_renderInlineTag($this->_detectMatch[2]);

   }


}


/**
 * traite les signes de types table
 */
class WRB_table extends WikiRendererBloc {
   public $type='table';
   public $regexp="/^\| ?(.*)/";
   public $_openTag='<table border="1">';
   public $_closeTag='</table>';

   public $_colcount=0;

   function open(){
      $this->_colcount=0;
      return $this->_openTag;
   }


   function getRenderedLine(){

      $result=explode(' | ',trim($this->_detectMatch[1]));
      $str='';
      $t='';

      if((count($result) != $this->_colcount) && ($this->_colcount!=0))
         $t='</table><table border="1">';
      $this->_colcount=count($result);

      for($i=0; $i < $this->_colcount; $i++){
         $str.='<td>'. $this->_renderInlineTag($result[$i]).'</td>';
      }
      $str=$t.'<tr>'.$str.'</tr>';

      return $str;
   }

}

/**
 * traite les signes de types hr
 */
class WRB_hr extends WikiRendererBloc {

   public $type='hr';
   public $regexp='/^={4,} *$/';
   public $_closeNow=true;

   function getRenderedLine(){
      return '<hr />';
   }

}

/**
 * traite les signes de types titre
 */
class WRB_title extends WikiRendererBloc {
   public $type='title';
   public $regexp="/^(\!{1,3})(.*)/";
   public $_closeNow=true;
   public $_minlevel=1;
   public $_order=false;

   function WRB_title(&$wr){
      $this->_minlevel = $wr->config->minHeaderLevel;
      $this->_order = $wr->config->headerOrder;
      parent::WikiRendererBloc($wr);
   }

   function getRenderedLine(){
      if($this->_order)
         $hx= $this->_minlevel + strlen($this->_detectMatch[1])-1;
      else
         $hx= $this->_minlevel + 3-strlen($this->_detectMatch[1]);
      return '<h'.$hx.'>'.$this->_renderInlineTag($this->_detectMatch[2]).'</h'.$hx.'>';
   }
}

/**
 * traite les signes de type paragraphe
 */
class WRB_p extends WikiRendererBloc {
   public $type='p';
   public $regexp="/(.*)/";
   public $_openTag='<p>';
   public $_closeTag='</p>';
}

/**
 * traite les signes de types pre (pour afficher du code..)
 */
class WRB_pre extends WikiRendererBloc {

   public $type='pre';
   public $regexp="/^ (.*)/";
   public $_openTag='<pre>';
   public $_closeTag='</pre>';

   function getRenderedLine(){
      return $this->_renderInlineTag($this->_detectMatch[1]);
   }

}


/**
 * traite les signes de type blockquote
 */
class WRB_blockquote extends WikiRendererBloc {
   public $type='bq';
   public $regexp="/^(\>+)(.*)/";

   function open(){
      $this->_previousTag = $this->_detectMatch[1];
      $this->_firstTagLen = strlen($this->_previousTag);
      $this->_firstLine = true;
      return str_repeat('<blockquote>',$this->_firstTagLen).'<p>';
   }

   function close(){
      return '</p>'.str_repeat('</blockquote>',strlen($this->_previousTag));
   }


   function getRenderedLine(){

      $d=strlen($this->_previousTag) - strlen($this->_detectMatch[1]);
      $str='';

      if( $d > 0 ){ // on remonte d'un cran dans la hierarchie...
         $str='</p>'.str_repeat('</blockquote>',$d).'<p>';
         $this->_previousTag=$this->_detectMatch[1];
      }elseif( $d < 0 ){ // un niveau de plus
         $this->_previousTag=$this->_detectMatch[1];
         $str='</p>'.str_repeat('<blockquote>',-$d).'<p>';
      }else{
         if($this->_firstLine)
            $this->_firstLine=false;
         else
            $str='<br />';
      }
      return $str.$this->_renderInlineTag($this->_detectMatch[2]);
   }
}

/**
 * traite les signes de type d�finitions
 */
class WRB_definition extends WikiRendererBloc {

   public $type='dfn';
   public $regexp="/^;(.*) : (.*)/i";
   public $_openTag='<dl>';
   public $_closeTag='</dl>';

   function getRenderedLine(){
      $dt=$this->_renderInlineTag($this->_detectMatch[1]);
      $dd=$this->_renderInlineTag($this->_detectMatch[2]);
      return "<dt>$dt</dt>\n<dd>$dd</dd>\n";
   }
}

?>