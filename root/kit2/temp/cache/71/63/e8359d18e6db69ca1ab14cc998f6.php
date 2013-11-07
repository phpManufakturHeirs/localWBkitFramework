<?php

/* @phpManufaktur/Basic/Template/default/kitcommand/iframe.message.twig */
class __TwigTemplate_7163e8359d18e6db69ca1ab14cc998f6 extends Twig_Template
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
        echo "<div class=\"message item\">";
        echo (isset($context["message"]) ? $context["message"] : null);
        echo "</div>
";
    }

    public function getTemplateName()
    {
        return "@phpManufaktur/Basic/Template/default/kitcommand/iframe.message.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  19 => 10,);
    }
}
