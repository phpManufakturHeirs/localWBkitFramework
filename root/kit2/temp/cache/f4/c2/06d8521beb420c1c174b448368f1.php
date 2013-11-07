<?php

/* @phpManufaktur/CommandCollection/Template/Comments/default/comments.twig */
class __TwigTemplate_f4c206d8521beb420c1c174b448368f1 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->blocks = array(
            'stylesheet' => array($this, 'block_stylesheet'),
            'jquery' => array($this, 'block_jquery'),
            'content' => array($this, 'block_content'),
            'footer' => array($this, 'block_footer'),
        );
    }

    protected function doGetParent(array $context)
    {
        return $this->env->resolveTemplate($this->env->getExtension('kitFramework')->getTemplateFile("@phpManufaktur/Basic/Template", "kitcommand/iframe.body.twig"));
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->getParent($context)->display($context, array_merge($this->blocks, $blocks));
    }

    // line 11
    public function block_stylesheet($context, array $blocks = array())
    {
        // line 12
        echo "  ";
        $this->displayParentBlock("stylesheet", $context, $blocks);
        echo "
  <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 13
        echo (isset($context["MANUFAKTUR_URL"]) ? $context["MANUFAKTUR_URL"] : null);
        echo "/CommandCollection/Template/Comments/default/css/comments.css\" media=\"screen, projection\" />
  <link href=\"//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css\" rel=\"stylesheet\">
";
    }

    // line 16
    public function block_jquery($context, array $blocks = array())
    {
        // line 17
        echo "  ";
        $this->displayParentBlock("jquery", $context, $blocks);
        echo "
  <script type=\"text/javascript\" src=\"";
        // line 18
        echo (isset($context["MANUFAKTUR_URL"]) ? $context["MANUFAKTUR_URL"] : null);
        echo "/CKEditor/Source/ckeditor.js\"></script>
  <script type=\"text/javascript\" >
    function createNewComment() {
      document.getElementById('comment_form').scrollIntoView(true); return false;
    }
    function jumpToComment(comment_id) {
      document.getElementById(comment_id).scrollIntoView(true); return false;
    }
  </script>
  <script type=\"text/javascript\" src=\"";
        // line 27
        echo (isset($context["MANUFAKTUR_URL"]) ? $context["MANUFAKTUR_URL"] : null);
        echo "/CommandCollection/Template/Rating/default/jquery/jRating.jquery.js\"></script>
";
    }

    // line 30
    public function block_content($context, array $blocks = array())
    {
        // line 31
        echo "  <div class=\"thread\">
    ";
        // line 32
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["thread"]) ? $context["thread"] : null));
        foreach ($context['_seq'] as $context["_key"] => $context["comment"]) {
            // line 33
            echo "      <div class=\"main\" id=\"comment_id_";
            echo $this->getAttribute($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "main"), "comment_id");
            echo "\">
        <div class=\"headline\">";
            // line 34
            echo $this->getAttribute($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "main"), "comment_headline");
            echo "</div>        
        <div class=\"content\">
          ";
            // line 36
            if (($this->getAttribute((isset($context["parameter"]) ? $context["parameter"] : null), "gravatar") && $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "gravatar"), "comment"), "main"), "enabled"))) {
                // line 37
                echo "            <div class=\"gravatar\">
              <img src=\"";
                // line 38
                echo $this->getAttribute($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "main"), "gravatar");
                echo "\" width=\"";
                echo $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "gravatar"), "comment"), "main"), "size");
                echo "\" height=\"";
                echo $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "gravatar"), "comment"), "main"), "size");
                echo "\" alt=\"";
                echo $this->getAttribute($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "main"), "contact_nick_name");
                echo "\" title=\"";
                echo $this->getAttribute($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "main"), "contact_nick_name");
                echo "\" />
            </div>
          ";
            }
            // line 41
            echo "          <div class=\"text\">
            ";
            // line 42
            echo $this->getAttribute($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "main"), "comment_content");
            echo "
          </div>
        </div>
        <div class=\"footer\">
          ";
            // line 46
            if (($this->getAttribute((isset($context["parameter"]) ? $context["parameter"] : null), "gravatar") && $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "gravatar"), "comment"), "main"), "enabled"))) {
                // line 47
                echo "            <div class=\"gravatar\">&nbsp;</div>
          ";
            }
            // line 49
            echo "          <div class=\"text\">  
            ";
            // line 50
            if (($this->getAttribute((isset($context["parameter"]) ? $context["parameter"] : null), "rating") && $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "rating"), "comment"), "main"), "enabled"))) {
                // line 51
                echo "              <div class=\"rating-container\">
                <div class=\"rating-stars rating_";
                // line 52
                echo $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "main"), "rating"), "identifier_id");
                echo "\" 
                     data-average=\"";
                // line 53
                echo $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "main"), "rating"), "average");
                echo "\" 
                     data-id=\"";
                // line 54
                echo $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "main"), "rating"), "identifier_id");
                echo "\" 
                     title=\"";
                // line 55
                echo $this->env->getExtension('translator')->trans("Votes: %count% - Average: %average%", array("%count%" => $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "main"), "rating"), "count"), "%average%" => twig_number_format_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "main"), "rating"), "average"), 2, $this->env->getExtension('translator')->trans("DECIMAL_SEPARATOR"), $this->env->getExtension('translator')->trans("THOUSAND_SEPARATOR"))));
                echo "\"></div>
              </div>
            ";
            }
            // line 58
            echo "            <div class=\"nick-name\">
              ";
            // line 59
            if ((twig_length_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "main"), "contact_url")) > 0)) {
                echo "<a href=\"";
                echo $this->getAttribute($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "main"), "contact_url");
                echo "\" target=\"_blank\">";
                echo $this->getAttribute($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "main"), "contact_nick_name");
                echo "</a>";
            } else {
                echo $this->getAttribute($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "main"), "contact_nick_name");
            }
            echo " - ";
            echo twig_date_format_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "main"), "comment_timestamp"), $this->env->getExtension('translator')->trans("DATETIME_FORMAT"));
            echo "
            </div>
          </div>  
        </div>
        
        ";
            // line 64
            if ((twig_length_filter($this->env, $this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "sub")) > 0)) {
                // line 65
                echo "          <div class=\"sub-row\">
            ";
                // line 66
                if (($this->getAttribute((isset($context["parameter"]) ? $context["parameter"] : null), "gravatar") && $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "gravatar"), "comment"), "main"), "enabled"))) {
                    // line 67
                    echo "              <div class=\"gravatar\">&nbsp;</div>
            ";
                }
                // line 69
                echo "            <div class=\"text\">
              <div class=\"sub-table ";
                // line 70
                if ((!$this->getAttribute((isset($context["parameter"]) ? $context["parameter"] : null), "gravatar"))) {
                    echo "add-space";
                }
                echo "\">
              ";
                // line 71
                $context['_parent'] = (array) $context;
                $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "sub"));
                foreach ($context['_seq'] as $context["_key"] => $context["reply"]) {
                    // line 72
                    echo "                <div class=\"reply\">
                  <div class=\"content\">
                    ";
                    // line 74
                    if (($this->getAttribute((isset($context["parameter"]) ? $context["parameter"] : null), "gravatar") && $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "gravatar"), "comment"), "reply"), "enabled"))) {
                        // line 75
                        echo "                      <div class=\"gravatar\">
                        <img src=\"";
                        // line 76
                        echo $this->getAttribute((isset($context["reply"]) ? $context["reply"] : null), "gravatar");
                        echo "\" width=\"";
                        echo $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "gravatar"), "comment"), "reply"), "size");
                        echo "\" height=\"";
                        echo $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "gravatar"), "comment"), "reply"), "size");
                        echo "\" alt=\"";
                        echo $this->getAttribute((isset($context["reply"]) ? $context["reply"] : null), "contact_nick_name");
                        echo "\" title=\"";
                        echo $this->getAttribute((isset($context["reply"]) ? $context["reply"] : null), "contact_nick_name");
                        echo "\" />
                      </div>
                    ";
                    }
                    // line 79
                    echo "                    <div class=\"text\">";
                    echo $this->getAttribute((isset($context["reply"]) ? $context["reply"] : null), "comment_content");
                    echo "</div>
                  </div>            
                  <div class=\"footer\">
                    ";
                    // line 82
                    if (($this->getAttribute((isset($context["parameter"]) ? $context["parameter"] : null), "gravatar") && $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "gravatar"), "comment"), "main"), "enabled"))) {
                        // line 83
                        echo "                      <div class=\"gravatar\">&nbsp;</div>
                    ";
                    }
                    // line 85
                    echo "                    <div class=\"text\">
                      ";
                    // line 86
                    if (($this->getAttribute((isset($context["parameter"]) ? $context["parameter"] : null), "rating") && $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "rating"), "comment"), "reply"), "enabled"))) {
                        // line 87
                        echo "                        <div class=\"rating-container\">
                          <div class=\"rating-stars rating_";
                        // line 88
                        echo $this->getAttribute($this->getAttribute((isset($context["reply"]) ? $context["reply"] : null), "rating"), "identifier_id");
                        echo "\" 
                               data-average=\"";
                        // line 89
                        echo $this->getAttribute($this->getAttribute((isset($context["reply"]) ? $context["reply"] : null), "rating"), "average");
                        echo "\" 
                               data-id=\"";
                        // line 90
                        echo $this->getAttribute($this->getAttribute((isset($context["reply"]) ? $context["reply"] : null), "rating"), "identifier_id");
                        echo "\" 
                               title=\"";
                        // line 91
                        echo $this->env->getExtension('translator')->trans("Votes: %count% - Average: %average%", array("%count%" => $this->getAttribute($this->getAttribute((isset($context["reply"]) ? $context["reply"] : null), "rating"), "count"), "%average%" => twig_number_format_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["reply"]) ? $context["reply"] : null), "rating"), "average"), 2, $this->env->getExtension('translator')->trans("DEZIMAL_SEPARATOR"), $this->env->getExtension('translator')->trans("THOUSAND_SEPARATOR"))));
                        echo "\"></div>
                        </div>
                      ";
                    }
                    // line 94
                    echo "                      <div class=\"nick-name\">
                        ";
                    // line 95
                    if ((twig_length_filter($this->env, $this->getAttribute((isset($context["reply"]) ? $context["reply"] : null), "contact_url")) > 0)) {
                        echo "<a href=\"";
                        echo $this->getAttribute((isset($context["reply"]) ? $context["reply"] : null), "contact_url");
                        echo "\" target=\"_blank\">";
                        echo $this->getAttribute((isset($context["reply"]) ? $context["reply"] : null), "contact_nick_name");
                        echo "</a>";
                    } else {
                        echo $this->getAttribute((isset($context["reply"]) ? $context["reply"] : null), "contact_nick_name");
                    }
                    echo " - ";
                    echo twig_date_format_filter($this->env, $this->getAttribute((isset($context["reply"]) ? $context["reply"] : null), "comment_timestamp"), $this->env->getExtension('translator')->trans("DATETIME_FORMAT"));
                    echo "
                      </div>
                    </div>
                  </div>
                </div>
              ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['reply'], $context['_parent'], $context['loop']);
                $context = array_merge($_parent, array_intersect_key($context, $_parent));
                // line 101
                echo "              </div>
            </div>  
          </div>  
        ";
            }
            // line 105
            echo "        <div class=\"reply-to\">
          ";
            // line 106
            if (($this->getAttribute((isset($context["parameter"]) ? $context["parameter"] : null), "gravatar") && $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "gravatar"), "comment"), "main"), "enabled"))) {
                // line 107
                echo "            <div class=\"gravatar\">&nbsp;</div>
          ";
            }
            // line 109
            echo "          <div class=\"text\">
          <i class=\"icon-comments-alt\"></i>&nbsp;<a href=\"";
            // line 110
            echo (((((isset($context["FRAMEWORK_URL"]) ? $context["FRAMEWORK_URL"] : null) . "/collection/comments/reply/id/") . $this->getAttribute($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "main"), "comment_id")) . "?pid=") . $this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "pid"));
            echo "\">";
            echo $this->env->getExtension('translator')->trans("Reply to this comment");
            echo "</a>&nbsp;
          <i class=\"icon-comment-alt\"></i>&nbsp;<a href=\"javascript:createNewComment();\">";
            // line 111
            echo $this->env->getExtension('translator')->trans("Create a new comment");
            echo "</a>
          </div>
        </div>

      </div>      
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['comment'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 117
        echo "    <div class=\"clear\"></div>
  </div>
  <div id=\"comment_form\" class=\"comment submit\"> 
    ";
        // line 120
        $this->displayParentBlock("content", $context, $blocks);
        echo "
    <form action=\"";
        // line 121
        echo (isset($context["FRAMEWORK_URL"]) ? $context["FRAMEWORK_URL"] : null);
        echo "/collection/comments/submit?pid=";
        echo $this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "pid");
        echo "\" method=\"post\" ";
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : null), 'enctype');
        echo ">
      ";
        // line 122
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : null), 'errors');
        echo "
      ";
        // line 124
        echo "      ";
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "comment_id"), 'row');
        echo "
      ";
        // line 125
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "identifier_id"), 'row');
        echo "
      ";
        // line 126
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "contact_id"), 'row');
        echo "
      ";
        // line 127
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "comment_parent"), 'row');
        echo "      
      ";
        // line 128
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "_token"), 'row');
        echo "
      
      ";
        // line 130
        if (($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "comment_parent"), "vars"), "value") > 0)) {
            // line 131
            echo "        <h2>";
            echo $this->env->getExtension('translator')->trans("You are replying to the comment <i>%headline%</i>", array("%headline%" => $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "comment_headline"), "vars"), "value")));
            echo "</h2>
        <input type=\"hidden\" name=\"";
            // line 132
            echo $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "comment_headline"), "vars"), "full_name");
            echo "\" id=\"";
            echo $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "comment_headline"), "vars"), "id");
            echo "\" value=\"";
            echo $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "comment_headline"), "vars"), "value");
            echo "\" />
        <div>
          <label>&nbsp;</label>
          <div class=\"value\">
            <a href=\"javascript:jumpToComment('comment_id_";
            // line 136
            echo $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "comment_parent"), "vars"), "value");
            echo "')\"><i class=\"icon-comments\"></i> ";
            echo $this->env->getExtension('translator')->trans("jump to this comment");
            echo "</a>
          </div>
        </div>
      ";
        } else {
            // line 140
            echo "        <h2>";
            echo $this->env->getExtension('translator')->trans("Your comment");
            echo "</h2>
        ";
            // line 141
            echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "comment_headline"), 'row');
            echo "
      ";
        }
        // line 143
        echo "      <div>
        ";
        // line 144
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "comment_content"), 'label');
        echo "
        <div class=\"value\">
          ";
        // line 146
        echo call_user_func_array($this->env->getFunction('CKEditor')->getCallable(), array($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "comment_content"), "vars"), "id"), $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "comment_content"), "vars"), "full_name"), $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "comment_content"), "vars"), "value"), "100%", "150px", ((isset($context["MANUFAKTUR_URL"]) ? $context["MANUFAKTUR_URL"] : null) . "/CommandCollection/Template/Comments/default/ckeditor.config.js")));
        // line 152
        echo "          
        </div>
      </div>
      ";
        // line 155
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "contact_nick_name"), 'row');
        echo "
      ";
        // line 156
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "contact_email"), 'row');
        echo "
      ";
        // line 157
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "contact_url"), 'row');
        echo "
      <div>
        <label>&nbsp;</label>
        <div class=\"value\">
          ";
        // line 161
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "comment_update_info"), 'widget');
        echo "
        </div>
      </div>
      <div>
        <label>&nbsp;</label>
        <div class=\"value hint\">
          ";
        // line 167
        if (($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "translator"), "locale") == "de")) {
            // line 168
            echo "            ";
            // line 169
            echo "            <p>Ihre E-Mail Adresse wird nicht veröffentlicht. Wenn Sie uns die URL Ihrer Website bzw. Homepage mitteilen, setzen wir automatisch einen Backlink für Sie!</p>
            ";
            // line 170
            if ($this->getAttribute((isset($context["parameter"]) ? $context["parameter"] : null), "gravatar")) {
                // line 171
                echo "              <p>Falls Ihre E-Mail Adresse bei <a href=\"http://de.gravatar.com/\" target=\"_blank\">Gravatar</a> registriert ist, zeigen wir Ihren Avatar neben Ihrem Kommentar an.</p>
            ";
            }
            // line 173
            echo "            <p>Die Benachrichtigung bei neuen Kommentaren bezieht sich ausschließlich auf diese Seite und kann jederzeit wieder abbestellt werden.</p>
          ";
        } else {
            // line 175
            echo "            ";
            // line 176
            echo "            <p>Your email address will not published. If you submit the URL of your website, we will create a backlink for you.</p>
            ";
            // line 177
            if ($this->getAttribute((isset($context["parameter"]) ? $context["parameter"] : null), "gravatar")) {
                // line 178
                echo "              <p>If your email address is registered at <a href=\"http://gravatar.com/\" target=\"_blank\">Gravatar</a>, we will show your avatar beside your comment.</p>
            ";
            }
            // line 180
            echo "            <p>The notification of new comments is restricted to this page and can cancelled at any time.</p>
          ";
        }
        // line 182
        echo "        </div>
      </div>
      ";
        // line 184
        if (($this->getAttribute((isset($context["parameter"]) ? $context["parameter"] : null), "captcha") && $this->env->getExtension('kitFramework')->reCaptchaIsActive())) {
            // line 185
            echo "        <div>
          <label>&nbsp;</label>
          <div class=\"value\">
            ";
            // line 188
            echo $this->env->getExtension('kitFramework')->reCaptcha();
            echo "
          </div>
        </div>
      ";
        }
        // line 192
        echo "      ";
        // line 193
        echo "      <div>
        <label for=\"submit\">&nbsp;</label>
        <input type=\"submit\" name=\"submit\" />
      </div>
      <div class=\"clear\"></div>
    </form>
  </div>
";
    }

    // line 201
    public function block_footer($context, array $blocks = array())
    {
        // line 202
        echo "  ";
        $context["iframe_add_height"] = 130;
        // line 203
        echo "  ";
        $this->displayParentBlock("footer", $context, $blocks);
        echo " 
  ";
        // line 204
        if ($this->getAttribute((isset($context["parameter"]) ? $context["parameter"] : null), "rating")) {
            // line 205
            echo "    <script type=\"text/javascript\">
      if (typeof 'jQuery' !== 'undefined') {
        \$(document).ready(function() {        
          ";
            // line 208
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["thread"]) ? $context["thread"] : null));
            foreach ($context['_seq'] as $context["_key"] => $context["comment"]) {
                // line 209
                echo "            ";
                if ($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "rating"), "comment"), "main"), "enabled")) {
                    echo "  
              \$(\".rating_";
                    // line 210
                    echo $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "main"), "rating"), "identifier_id");
                    echo "\").jRating({
                bigStarsPath: '";
                    // line 211
                    echo (isset($context["MANUFAKTUR_URL"]) ? $context["MANUFAKTUR_URL"] : null);
                    echo "/CommandCollection/Template/Rating/default/css/icons/stars.png',
                smallStarsPath: '";
                    // line 212
                    echo (isset($context["MANUFAKTUR_URL"]) ? $context["MANUFAKTUR_URL"] : null);
                    echo "/CommandCollection/Template/Rating/default/css/icons/small.png',
                phpPath: '";
                    // line 213
                    echo (isset($context["FRAMEWORK_URL"]) ? $context["FRAMEWORK_URL"] : null);
                    echo "/collection/rating/response',
                type: '";
                    // line 214
                    echo $this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "rating"), "type");
                    echo "',
                length : ";
                    // line 215
                    echo $this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "rating"), "length");
                    echo ",
                step: ";
                    // line 216
                    echo $this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "rating"), "step");
                    echo ",
                rateMax: ";
                    // line 217
                    echo $this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "rating"), "rate_max");
                    echo ",
                showRateInfo: ";
                    // line 218
                    if ($this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "rating"), "show_rate_info")) {
                        echo "true";
                    } else {
                        echo "false";
                    }
                    echo ",
                ";
                    // line 219
                    if ($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "main"), "rating"), "is_disabled")) {
                        echo "isDisabled: true,";
                    }
                    // line 220
                    echo "              });
            ";
                }
                // line 222
                echo "            ";
                if ($this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "rating"), "comment"), "reply"), "enabled")) {
                    // line 223
                    echo "              ";
                    $context['_parent'] = (array) $context;
                    $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "sub"));
                    foreach ($context['_seq'] as $context["_key"] => $context["reply"]) {
                        // line 224
                        echo "                \$(\".rating_";
                        echo $this->getAttribute($this->getAttribute((isset($context["reply"]) ? $context["reply"] : null), "rating"), "identifier_id");
                        echo "\").jRating({
                  bigStarsPath: '";
                        // line 225
                        echo (isset($context["MANUFAKTUR_URL"]) ? $context["MANUFAKTUR_URL"] : null);
                        echo "/CommandCollection/Template/Rating/default/css/icons/stars.png',
                  smallStarsPath: '";
                        // line 226
                        echo (isset($context["MANUFAKTUR_URL"]) ? $context["MANUFAKTUR_URL"] : null);
                        echo "/CommandCollection/Template/Rating/default/css/icons/small.png',
                  phpPath: '";
                        // line 227
                        echo (isset($context["FRAMEWORK_URL"]) ? $context["FRAMEWORK_URL"] : null);
                        echo "/collection/rating/response',
                  type:'";
                        // line 228
                        echo $this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "rating"), "type");
                        echo "',
                  length : ";
                        // line 229
                        echo $this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "rating"), "length");
                        echo ",
                  step: ";
                        // line 230
                        echo $this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "rating"), "step");
                        echo ",
                  rateMax: ";
                        // line 231
                        echo $this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "rating"), "rate_max");
                        echo ",
                  showRateInfo: ";
                        // line 232
                        if ($this->getAttribute($this->getAttribute((isset($context["configuration"]) ? $context["configuration"] : null), "rating"), "show_rate_info")) {
                            echo "true";
                        } else {
                            echo "false";
                        }
                        echo ",
                  ";
                        // line 233
                        if ($this->getAttribute($this->getAttribute((isset($context["reply"]) ? $context["reply"] : null), "rating"), "is_disabled")) {
                            echo "isDisabled: true,";
                        }
                        // line 234
                        echo "                });
              ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_iterated'], $context['_key'], $context['reply'], $context['_parent'], $context['loop']);
                    $context = array_merge($_parent, array_intersect_key($context, $_parent));
                    // line 236
                    echo "            ";
                }
                // line 237
                echo "          ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['comment'], $context['_parent'], $context['loop']);
            $context = array_merge($_parent, array_intersect_key($context, $_parent));
            // line 238
            echo "        });
      }
    </script>
  ";
        }
    }

    public function getTemplateName()
    {
        return "@phpManufaktur/CommandCollection/Template/Comments/default/comments.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  645 => 238,  639 => 237,  636 => 236,  629 => 234,  625 => 233,  617 => 232,  613 => 231,  609 => 230,  605 => 229,  601 => 228,  597 => 227,  593 => 226,  589 => 225,  584 => 224,  579 => 223,  576 => 222,  572 => 220,  568 => 219,  560 => 218,  556 => 217,  552 => 216,  548 => 215,  544 => 214,  540 => 213,  536 => 212,  532 => 211,  528 => 210,  523 => 209,  519 => 208,  514 => 205,  512 => 204,  507 => 203,  504 => 202,  501 => 201,  490 => 193,  488 => 192,  481 => 188,  476 => 185,  474 => 184,  470 => 182,  466 => 180,  462 => 178,  460 => 177,  457 => 176,  455 => 175,  451 => 173,  447 => 171,  445 => 170,  442 => 169,  440 => 168,  438 => 167,  429 => 161,  422 => 157,  418 => 156,  414 => 155,  409 => 152,  407 => 146,  402 => 144,  399 => 143,  394 => 141,  389 => 140,  380 => 136,  369 => 132,  364 => 131,  362 => 130,  357 => 128,  353 => 127,  349 => 126,  345 => 125,  340 => 124,  336 => 122,  328 => 121,  324 => 120,  319 => 117,  307 => 111,  301 => 110,  298 => 109,  294 => 107,  292 => 106,  289 => 105,  283 => 101,  261 => 95,  258 => 94,  252 => 91,  248 => 90,  244 => 89,  240 => 88,  237 => 87,  235 => 86,  232 => 85,  228 => 83,  226 => 82,  219 => 79,  205 => 76,  202 => 75,  200 => 74,  196 => 72,  192 => 71,  186 => 70,  183 => 69,  179 => 67,  177 => 66,  174 => 65,  172 => 64,  154 => 59,  151 => 58,  145 => 55,  141 => 54,  137 => 53,  133 => 52,  130 => 51,  128 => 50,  125 => 49,  121 => 47,  119 => 46,  112 => 42,  109 => 41,  95 => 38,  92 => 37,  90 => 36,  85 => 34,  80 => 33,  76 => 32,  73 => 31,  70 => 30,  64 => 27,  52 => 18,  47 => 17,  44 => 16,  37 => 13,  32 => 12,  29 => 11,);
    }
}
