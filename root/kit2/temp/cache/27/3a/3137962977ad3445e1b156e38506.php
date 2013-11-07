<?php

/* @phpManufaktur/CommandCollection/Template/Comments/default/mail/contact/pending.confirmation.twig */
class __TwigTemplate_273a3137962977ad3445e1b156e38506 extends Twig_Template
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
        if (($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "translator"), "locale") == "dee")) {
            // line 11
            echo "  ";
        } else {
            // line 13
            echo "  ";
            // line 14
            echo "  <p>Hello ";
            echo $this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "contact_nick_name");
            echo ",</p>
  <p>the administrator is asked to confirm your comment \"";
            // line 15
            echo $this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "comment_headline");
            echo "\" as soon as possible.</p>
";
        }
    }

    public function getTemplateName()
    {
        return "@phpManufaktur/CommandCollection/Template/Comments/default/mail/contact/pending.confirmation.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  31 => 15,  58 => 23,  54 => 22,  50 => 21,  45 => 19,  41 => 18,  37 => 17,  33 => 16,  29 => 15,  26 => 14,  24 => 13,  21 => 11,  19 => 10,);
    }
}
