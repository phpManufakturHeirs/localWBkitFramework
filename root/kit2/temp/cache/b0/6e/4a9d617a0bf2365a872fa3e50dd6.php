<?php

/* @phpManufaktur/Basic/Template/default/kitcommand/iframe.body.twig */
class __TwigTemplate_b06e4a9d617a0bf2365a872fa3e50dd6 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'head' => array($this, 'block_head'),
            'title' => array($this, 'block_title'),
            'robots' => array($this, 'block_robots'),
            'description' => array($this, 'block_description'),
            'keywords' => array($this, 'block_keywords'),
            'stylesheet' => array($this, 'block_stylesheet'),
            'jquery' => array($this, 'block_jquery'),
            'iframe_content_attribute' => array($this, 'block_iframe_content_attribute'),
            'content' => array($this, 'block_content'),
            'footer' => array($this, 'block_footer'),
            'tracking' => array($this, 'block_tracking'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 10
        echo "<!DOCTYPE html>
<html>
  ";
        // line 12
        ob_start();
        // line 13
        echo "  <head>
    ";
        // line 14
        $this->displayBlock('head', $context, $blocks);
        // line 35
        echo "  </head>
  <body>
    <div ";
        // line 37
        $this->displayBlock('iframe_content_attribute', $context, $blocks);
        echo ">
      ";
        // line 38
        $this->displayBlock('content', $context, $blocks);
        // line 43
        echo "    </div>
    ";
        // line 44
        $this->displayBlock('footer', $context, $blocks);
        // line 60
        echo "    ";
        $this->displayBlock('tracking', $context, $blocks);
        // line 66
        echo "  </body>
  ";
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
        // line 68
        echo "</html>
";
    }

    // line 14
    public function block_head($context, array $blocks = array())
    {
        // line 15
        echo "      ";
        if (($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "frame", array(), "any", true, true) && $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "frame"), "redirect"), "active"))) {
            // line 16
            echo "        ";
            // line 17
            echo "        <script type=\"text/javascript\">
          if (top.location == self.location) {
            document.location.replace(\"";
            // line 19
            echo $this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "cms"), "page_url");
            if ((twig_length_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "frame"), "redirect"), "route")) > 0)) {
                echo "?redirect=";
                echo $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "frame"), "redirect"), "route");
            }
            echo "\");
          }
        </script>
      ";
        }
        // line 23
        echo "      <title>";
        $this->displayBlock('title', $context, $blocks);
        echo "</title>
      <meta http-equiv=\"Content-Type\" content=\"text/html; charset=";
        // line 24
        echo (($this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "page", array(), "any", false, true), "charset", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "page", array(), "any", false, true), "charset"), "UTF-8")) : ("UTF-8"));
        echo "\"/>
      <meta name=\"robots\" content=\"";
        // line 25
        $this->displayBlock('robots', $context, $blocks);
        echo "\" />
      <meta name=\"description\" content=\"";
        // line 26
        $this->displayBlock('description', $context, $blocks);
        echo "\" />
      <meta name=\"keywords\" content=\"";
        // line 27
        $this->displayBlock('keywords', $context, $blocks);
        echo "\" />
      ";
        // line 28
        $this->displayBlock('stylesheet', $context, $blocks);
        // line 31
        echo "      ";
        $this->displayBlock('jquery', $context, $blocks);
        // line 34
        echo "    ";
    }

    // line 23
    public function block_title($context, array $blocks = array())
    {
        echo (($this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "page", array(), "any", false, true), "title", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "page", array(), "any", false, true), "title"), "")) : (""));
    }

    // line 25
    public function block_robots($context, array $blocks = array())
    {
        echo (($this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "page", array(), "any", false, true), "robots", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "page", array(), "any", false, true), "robots"), "index,follow")) : ("index,follow"));
    }

    // line 26
    public function block_description($context, array $blocks = array())
    {
        echo (($this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "page", array(), "any", false, true), "description", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "page", array(), "any", false, true), "description"), "")) : (""));
    }

    // line 27
    public function block_keywords($context, array $blocks = array())
    {
        echo (($this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "page", array(), "any", false, true), "keywords", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "page", array(), "any", false, true), "keywords"), "")) : (""));
    }

    // line 28
    public function block_stylesheet($context, array $blocks = array())
    {
        // line 29
        echo "        <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        echo (isset($context["MANUFAKTUR_URL"]) ? $context["MANUFAKTUR_URL"] : null);
        echo "/Basic/Template/default/kitcommand/css/kitcommand.css\" media=\"screen, projection\" />
      ";
    }

    // line 31
    public function block_jquery($context, array $blocks = array())
    {
        // line 32
        echo "        <script type=\"text/javascript\" src=\"https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js\"></script>
      ";
    }

    // line 37
    public function block_iframe_content_attribute($context, array $blocks = array())
    {
        echo "class=\"iframe_content\"";
    }

    // line 38
    public function block_content($context, array $blocks = array())
    {
        // line 39
        echo "        ";
        if ((twig_length_filter($this->env, $this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "message")) > 0)) {
            // line 40
            echo "          <div class=\"iframe_content message\">";
            echo $this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "message");
            echo "</div>
        ";
        }
        // line 42
        echo "      ";
    }

    // line 44
    public function block_footer($context, array $blocks = array())
    {
        // line 45
        echo "      ";
        if (($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "frame", array(), "any", true, true) && $this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "frame"), "auto"))) {
            // line 46
            echo "        <script type=\"text/javascript\">
          if (typeof 'jQuery' !== 'undefined') {
            \$(document).ready(function() {
              ";
            // line 50
            echo "              var content_height = \$('.iframe_content').height() + ";
            echo (($this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "frame", array(), "any", false, true), "add", array(), "any", true, true)) ? (_twig_default_filter($this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "frame", array(), "any", false, true), "add"), 20)) : (20));
            echo " + ";
            echo ((array_key_exists("iframe_add_height", $context)) ? (_twig_default_filter((isset($context["iframe_add_height"]) ? $context["iframe_add_height"] : null), 0)) : (0));
            echo " + \"px\";
              parent.document.getElementById(\"";
            // line 51
            echo $this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "frame"), "id");
            echo "\").style.height = content_height;
              ";
            // line 52
            if ((twig_length_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "frame"), "scroll_to_id")) > 0)) {
                // line 53
                echo "                document.getElementById('";
                echo $this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "frame"), "scroll_to_id");
                echo "').scrollIntoView(true);
              ";
            }
            // line 55
            echo "            });
          }
        </script>
      ";
        }
        // line 59
        echo "    ";
    }

    // line 60
    public function block_tracking($context, array $blocks = array())
    {
        // line 61
        echo "      ";
        if (($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "page", array(), "any", true, true) && (twig_length_filter($this->env, $this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "page"), "tracking")) > 0))) {
            // line 62
            echo "        <!-- tracking code from /kit2/config/tracking.htt -->
        ";
            // line 63
            echo $this->getAttribute($this->getAttribute((isset($context["basic"]) ? $context["basic"] : null), "page"), "tracking");
            echo "
      ";
        }
        // line 65
        echo "    ";
    }

    public function getTemplateName()
    {
        return "@phpManufaktur/Basic/Template/default/kitcommand/iframe.body.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  239 => 65,  234 => 63,  231 => 62,  225 => 60,  221 => 59,  215 => 55,  209 => 53,  207 => 52,  203 => 51,  191 => 46,  188 => 45,  185 => 44,  181 => 42,  175 => 40,  169 => 38,  163 => 37,  158 => 32,  155 => 31,  148 => 29,  139 => 27,  127 => 25,  117 => 34,  114 => 31,  108 => 27,  104 => 26,  100 => 25,  96 => 24,  91 => 23,  74 => 16,  71 => 15,  68 => 14,  63 => 68,  59 => 66,  56 => 60,  54 => 44,  51 => 43,  49 => 38,  45 => 37,  41 => 35,  39 => 14,  36 => 13,  34 => 12,  30 => 10,  645 => 238,  639 => 237,  636 => 236,  629 => 234,  625 => 233,  617 => 232,  613 => 231,  609 => 230,  605 => 229,  601 => 228,  597 => 227,  593 => 226,  589 => 225,  584 => 224,  579 => 223,  576 => 222,  572 => 220,  568 => 219,  560 => 218,  556 => 217,  552 => 216,  548 => 215,  544 => 214,  540 => 213,  536 => 212,  532 => 211,  528 => 210,  523 => 209,  519 => 208,  514 => 205,  512 => 204,  507 => 203,  504 => 202,  501 => 201,  490 => 193,  488 => 192,  481 => 188,  476 => 185,  474 => 184,  470 => 182,  466 => 180,  462 => 178,  460 => 177,  457 => 176,  455 => 175,  451 => 173,  447 => 171,  445 => 170,  442 => 169,  440 => 168,  438 => 167,  429 => 161,  422 => 157,  418 => 156,  414 => 155,  409 => 152,  407 => 146,  402 => 144,  399 => 143,  394 => 141,  389 => 140,  380 => 136,  369 => 132,  364 => 131,  362 => 130,  357 => 128,  353 => 127,  349 => 126,  345 => 125,  340 => 124,  336 => 122,  328 => 121,  324 => 120,  319 => 117,  307 => 111,  301 => 110,  298 => 109,  294 => 107,  292 => 106,  289 => 105,  283 => 101,  261 => 95,  258 => 94,  252 => 91,  248 => 90,  244 => 89,  240 => 88,  237 => 87,  235 => 86,  232 => 85,  228 => 61,  226 => 82,  219 => 79,  205 => 76,  202 => 75,  200 => 74,  196 => 50,  192 => 71,  186 => 70,  183 => 69,  179 => 67,  177 => 66,  174 => 65,  172 => 39,  154 => 59,  151 => 58,  145 => 28,  141 => 54,  137 => 53,  133 => 26,  130 => 51,  128 => 50,  125 => 49,  121 => 23,  119 => 46,  112 => 28,  109 => 41,  95 => 38,  92 => 37,  90 => 36,  85 => 34,  80 => 19,  76 => 17,  73 => 31,  70 => 30,  64 => 27,  52 => 18,  47 => 17,  44 => 16,  37 => 13,  32 => 12,  29 => 11,);
    }
}
