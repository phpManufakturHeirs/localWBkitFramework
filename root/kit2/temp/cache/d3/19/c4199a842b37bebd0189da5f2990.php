<?php

/* @phpManufaktur/Basic/Template/default/framework/error.404.twig */
class __TwigTemplate_d319c4199a842b37bebd0189da5f2990 extends Twig_Template
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
        echo $this->env->getExtension('translator')->trans("The requested page could not be found!");
        echo "</title>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"/>
    <meta name=\"robots\" content=\"noindex,nofollow\" />
    <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 16
        echo (isset($context["MANUFAKTUR_URL"]) ? $context["MANUFAKTUR_URL"] : null);
        echo "/Basic/Template/default/framework/css/error.css\" media=\"screen, projection\" />
  </head>
  <body>
    <div id=\"error-404\">
      <p>";
        // line 20
        echo $this->env->getExtension('translator')->trans("The requested page could not be found!");
        echo "</p>
    </div>
  </body>
</html>      ";
    }

    public function getTemplateName()
    {
        return "@phpManufaktur/Basic/Template/default/framework/error.404.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  37 => 20,  30 => 16,  24 => 13,  19 => 10,);
    }
}
