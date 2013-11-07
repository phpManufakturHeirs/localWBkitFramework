<?php

/* @phpManufaktur/Contact/Template/default/backend/message.twig */
class __TwigTemplate_4d5d7c0078d4d9d72745f3ca371bb481 extends Twig_Template
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
        return "@phpManufaktur/Contact/Template/default/backend/message.twig";
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
