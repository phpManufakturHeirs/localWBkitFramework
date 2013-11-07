<?php

/* @phpManufaktur/CommandCollection/Template/Comments/default/mail/contact/confirm.contact.twig */
class __TwigTemplate_4f8c3f46238e3755d0298c51c403891c extends Twig_Template
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
  <p>thank your for your comment \"";
            // line 15
            echo $this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "comment_headline");
            echo "\" at <a href=\"";
            echo $this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "comment_url");
            echo "\">";
            echo $this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "comment_url");
            echo "</a>.</p>
  <p>Before we can publish your comment, please confirm us your email address with the following link:</p>  
  <p><a href=\"";
            // line 17
            echo (isset($context["activation_link"]) ? $context["activation_link"] : null);
            echo "\">Confirm email address</a></p>
  <p>Thank you!</p>
";
        }
    }

    public function getTemplateName()
    {
        return "@phpManufaktur/CommandCollection/Template/Comments/default/mail/contact/confirm.contact.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  40 => 17,  31 => 15,  26 => 14,  24 => 13,  21 => 11,  19 => 10,);
    }
}
