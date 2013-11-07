<?php

/* @phpManufaktur/Basic/Template/default/framework/error.twig */
class __TwigTemplate_ffeb82986b0ad5d3dac159a08fab38d4 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 10
        echo "<!DOCTYPE html>
<html>
  <head>
    <title>";
        // line 13
        echo $this->env->getExtension('translator')->trans("kitFramework Error");
        echo "</title>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"/>
    <meta name=\"robots\" content=\"noindex,nofollow\" />
    <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 16
        echo (isset($context["MANUFAKTUR_URL"]) ? $context["MANUFAKTUR_URL"] : null);
        echo "/Basic/Template/default/framework/css/error.css\" media=\"screen, projection\" />
  </head>
  <body>
    <div id=\"error\">
      <div class=\"message\">
        <b>";
        // line 21
        echo (isset($context["code"]) ? $context["code"] : null);
        echo "</b> - ";
        if ((twig_length_filter($this->env, $this->getAttribute((isset($context["message"]) ? $context["message"] : null), "short")) > 0)) {
            echo $this->getAttribute((isset($context["message"]) ? $context["message"] : null), "full");
        } else {
            echo $this->getAttribute((isset($context["message"]) ? $context["message"] : null), "full");
        }
        // line 22
        echo "      </div>
      <div class=\"file\">[";
        // line 23
        echo (isset($context["line"]) ? $context["line"] : null);
        echo "] ";
        echo (isset($context["file"]) ? $context["file"] : null);
        echo "</div>
      <div class=\"report\">        
        <p>";
        // line 25
        echo $this->env->getExtension('translator')->trans("Need help? Please visit the <a href=\"%url%\" target=\"_blank\">phpManufaktur Support Group</a>.", array("%url%" => "https://support.phpmanufaktur.de"));
        echo "</p>
      </div>
    </div>
  </body>
</html>      ";
    }

    public function getTemplateName()
    {
        return "@phpManufaktur/Basic/Template/default/framework/error.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  56 => 25,  49 => 23,  46 => 22,  38 => 21,  30 => 16,  24 => 13,  19 => 10,);
    }
}
