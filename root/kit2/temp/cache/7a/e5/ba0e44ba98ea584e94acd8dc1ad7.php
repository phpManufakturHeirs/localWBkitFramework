<?php

/* @phpManufaktur/Basic/Template/default/kitcommand/iframe.twig */
class __TwigTemplate_7ae5ba0e44ba98ea584e94acd8dc1ad7 extends Twig_Template
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
        echo "<iframe class=\"";
        echo $this->getAttribute((isset($context["frame"]) ? $context["frame"] : null), "class");
        echo "\" id=\"";
        echo $this->getAttribute((isset($context["frame"]) ? $context["frame"] : null), "id");
        echo "\" name=\"";
        echo $this->getAttribute((isset($context["frame"]) ? $context["frame"] : null), "name");
        echo "\" src=\"";
        echo $this->getAttribute((isset($context["frame"]) ? $context["frame"] : null), "source");
        echo "\" width=\"";
        echo $this->getAttribute((isset($context["frame"]) ? $context["frame"] : null), "width");
        echo "\" height=\"";
        echo $this->getAttribute((isset($context["frame"]) ? $context["frame"] : null), "height");
        echo "\" scrolling=\"auto\" marginheight=\"0\" marginwidth=\"0\" frameborder=\"0\" allowTransparency=\"true\" style=\"color:#000;background:rgba(0,0,0,0);\" >
  <p>";
        // line 11
        echo $this->env->getExtension('translator')->trans("Please <a href=\"%source%\">visit this page</a> to view the content of this iframe.", array("%source%" => $this->getAttribute((isset($context["frame"]) ? $context["frame"] : null), "source")));
        echo "</p>
</iframe>
";
    }

    public function getTemplateName()
    {
        return "@phpManufaktur/Basic/Template/default/kitcommand/iframe.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  34 => 11,  19 => 10,);
    }
}
