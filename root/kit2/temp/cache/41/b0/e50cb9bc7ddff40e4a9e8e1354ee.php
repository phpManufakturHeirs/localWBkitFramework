<?php

/* @phpManufaktur/Basic/Template/default/framework/backend.body.twig */
class __TwigTemplate_41b0e50cb9bc7ddff40e4a9e8e1354ee extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'head' => array($this, 'block_head'),
            'title' => array($this, 'block_title'),
            'description' => array($this, 'block_description'),
            'keywords' => array($this, 'block_keywords'),
            'content' => array($this, 'block_content'),
            'footer' => array($this, 'block_footer'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 10
        echo "<!DOCTYPE html>
<html>
  <head>
    ";
        // line 13
        $this->displayBlock('head', $context, $blocks);
        // line 21
        echo "  </head>
  <body>
    <div id=\"content\">
      ";
        // line 24
        $this->displayBlock('content', $context, $blocks);
        // line 26
        echo "    </div>
    ";
        // line 27
        $this->displayBlock('footer', $context, $blocks);
        // line 38
        echo "  </body>
</html>      ";
    }

    // line 13
    public function block_head($context, array $blocks = array())
    {
        // line 14
        echo "      <title>";
        $this->displayBlock('title', $context, $blocks);
        echo "</title>
      <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"/>
      <meta name=\"robots\" content=\"noindex,nofollow\" />
      <meta name=\"description\" content=\"";
        // line 17
        $this->displayBlock('description', $context, $blocks);
        echo "\" />
      <meta name=\"keywords\" content=\"";
        // line 18
        $this->displayBlock('keywords', $context, $blocks);
        echo "\" />
      <script src=\"https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js\"></script>
    ";
    }

    // line 14
    public function block_title($context, array $blocks = array())
    {
    }

    // line 17
    public function block_description($context, array $blocks = array())
    {
    }

    // line 18
    public function block_keywords($context, array $blocks = array())
    {
    }

    // line 24
    public function block_content($context, array $blocks = array())
    {
        // line 25
        echo "      ";
    }

    // line 27
    public function block_footer($context, array $blocks = array())
    {
        // line 28
        echo "      <script type=\"text/javascript\">
        if (typeof 'jQuery' !== 'undefined') {
          \$(document).ready(function() {
            ";
        // line 32
        echo "            var content_height = \$('#content').height() + ";
        echo ((array_key_exists("iframe_add_height", $context)) ? (_twig_default_filter((isset($context["iframe_add_height"]) ? $context["iframe_add_height"] : null), 30)) : (30));
        echo " + \"px\";
            parent.document.getElementById(\"kitframework_iframe\").style.height = content_height;
          });
        }
      </script>
    ";
    }

    public function getTemplateName()
    {
        return "@phpManufaktur/Basic/Template/default/framework/backend.body.twig";
    }

    public function getDebugInfo()
    {
        return array (  100 => 32,  95 => 28,  88 => 25,  85 => 24,  80 => 18,  70 => 14,  63 => 18,  59 => 17,  52 => 14,  49 => 13,  44 => 38,  42 => 27,  39 => 26,  37 => 24,  32 => 21,  25 => 10,  159 => 62,  151 => 57,  148 => 56,  138 => 50,  129 => 49,  126 => 48,  120 => 45,  115 => 43,  112 => 42,  106 => 40,  103 => 39,  101 => 38,  98 => 37,  92 => 27,  87 => 32,  84 => 31,  78 => 29,  75 => 17,  73 => 27,  71 => 26,  67 => 25,  64 => 24,  61 => 23,  55 => 21,  50 => 20,  47 => 19,  41 => 18,  35 => 15,  33 => 14,  30 => 13,  28 => 11,  26 => 10,);
    }
}
