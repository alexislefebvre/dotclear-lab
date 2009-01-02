<?php
if (!defined('DC_CONTEXT_ADMIN')) { exit; }
?>
<html>
<head>
<title>OpenID</title>
<style type="text/css">
pre{
	margin-left: 100px;
	margin-right: 100px;
}

code{
        display: block;
        border: 1px solid #000;
	border-left: 8px solid #000;
	padding: 3px;
}
</style>
</head>
<body>
<h2><?php echo __('Congratulations, the OpenID Widget (2.0) for Dotclear 2 has been successfully installed !'); ?></h2>
<p><?php echo __("First, please add the OpenID widget to your menu, through the widget pannel, then read these instructions") ; ?> :</p>
<p><?php echo __("You just need to modify your theme. For that, please modify your post.html file by following this way") ; ?> :</p>
<p><?php echo __('Replace this'); ?> :</p>
<pre><code>
        &lt;p class="field"&gt;&lt;label for="c_name"&gt;{{tpl:lang Name or nickname}}&nbsp;:&lt;/label&gt;
        &lt;input name="c_name" id="c_name" type="text" size="30" maxlength="255"
        value="{{tpl:CommentPreviewName encode_html="1"}}" /&gt;
        &lt;/p&gt;
        
        &lt;p class="field"&gt;&lt;label for="c_mail"&gt;{{tpl:lang Email address}}&nbsp;:&lt;/label&gt;
        &lt;input name="c_mail" id="c_mail" type="text" size="30" maxlength="255"
        value="{{tpl:CommentPreviewEmail encode_html="1"}}" /&gt;
        &lt;/p&gt;
        
        &lt;p class="field"&gt;&lt;label for="c_site"&gt;{{tpl:lang Website}}
        ({{tpl:lang optional}})&nbsp;:&lt;/label&gt;
        &lt;input name="c_site" id="c_site" type="text" size="30" maxlength="255"
        value="{{tpl:CommentPreviewSite encode_html="1"}}" /&gt;
        &lt;/p&gt;
</code></pre>
<p><?php echo __('By this'); ?> :</p>
<pre><code>
        <strong>&lt;tpl:OpenidSessionIf&gt;</strong>
        &lt;p class="field"&gt;&lt;label for="c_name"&gt;{{tpl:lang Name or nickname}}&nbsp;:&lt;/label&gt;
        &lt;input name="c_name" id="c_name" type="text" size="30" maxlength="255"
        value="{{tpl:CommentPreviewName encode_html="1"}}" /&gt;
        &lt;/p&gt;
        
        &lt;p class="field"&gt;&lt;label for="c_mail"&gt;{{tpl:lang Email address}}&nbsp;:&lt;/label&gt;
        &lt;input name="c_mail" id="c_mail" type="text" size="30" maxlength="255"
        value="{{tpl:CommentPreviewEmail encode_html="1"}}" /&gt;
        &lt;/p&gt;
        
        &lt;p class="field"&gt;&lt;label for="c_site"&gt;{{tpl:lang Website}}
        ({{tpl:lang optional}})&nbsp;:&lt;/label&gt;
        &lt;input name="c_site" id="c_site" type="text" size="30" maxlength="255"
        value="{{tpl:CommentPreviewSite encode_html="1"}}" /&gt;
        &lt;/p&gt;
        <strong>&lt;/tpl:OpenidSessionIf&gt;</strong>
</code></pre>
<p>
<?php echo __("Indeed, you just need to frame this code by the following block : \"&lt;tpl:OpenidSessionIf&gt;\" ."); ?> 
<?php echo __("The installation is now fully functional, enjoy :)"); ?> .</p>
</body>
</html>