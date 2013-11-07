<?php

/* @phpManufaktur/CommandCollection/Template/Comments/default/mail/admin/confirm.comment.twig */
class __TwigTemplate_a8282b23549a71ad26c459b4162ee94b extends Twig_Template
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
            echo "  <p>Please check the the following comment;</p>
  <p>Nickname: ";
            // line 15
            echo $this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "contact_nick_name");
            echo "<br />
    E-Mail: ";
            // line 16
            echo $this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "contact_email");
            echo "<br />
    Contact ID: ";
            // line 17
            echo $this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "contact_id");
            echo "</p>
  <p>";
            // line 18
            echo $this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "comment_headline");
            echo "</p>  
  <div>";
            // line 19
            echo $this->getAttribute((isset($context["comment"]) ? $context["comment"] : null), "comment_content");
            echo "</div>
  <p>&nbsp;</p>
  <p><a href=\"";
            // line 21
            echo (isset($context["link_publish_comment"]) ? $context["link_publish_comment"] : null);
            echo "\">Publish the comment</a></p>
  <p><a href=\"";
            // line 22
            echo (isset($context["link_reject_comment"]) ? $context["link_reject_comment"] : null);
            echo "\">Reject the comment</a></p>
  <p><a href=\"";
            // line 23
            echo (isset($context["link_lock_contact"]) ? $context["link_lock_contact"] : null);
            echo "\">Reject the comment and lock the contact</a></p>
";
        }
    }

    public function getTemplateName()
    {
        return "@phpManufaktur/CommandCollection/Template/Comments/default/mail/admin/confirm.comment.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  58 => 23,  54 => 22,  50 => 21,  45 => 19,  41 => 18,  37 => 17,  33 => 16,  29 => 15,  26 => 14,  24 => 13,  21 => 11,  19 => 10,);
    }
}
