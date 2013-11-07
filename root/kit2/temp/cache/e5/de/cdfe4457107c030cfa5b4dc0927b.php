<?php

/* @phpManufaktur/Basic/Template/default/framework/first.login.twig */
class __TwigTemplate_e5decdfe4457107c030cfa5b4dc0927b extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'head' => array($this, 'block_head'),
            'content' => array($this, 'block_content'),
            'footer' => array($this, 'block_footer'),
        );
    }

    protected function doGetParent(array $context)
    {
        return $this->env->resolveTemplate($this->env->getExtension('kitFramework')->getTemplateFile((isset($context["template_namespace"]) ? $context["template_namespace"] : null), (isset($context["template_file"]) ? $context["template_file"] : null)));
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 10
        if (((isset($context["usage"]) ? $context["usage"] : null) == "framework")) {
            // line 11
            $context["template_namespace"] = "@phpManufaktur/Basic/Template";
            // line 12
            $context["template_file"] = "framework/body.twig";
        } else {
            // line 14
            $context["template_namespace"] = "@phpManufaktur/Basic/Template";
            // line 15
            $context["template_file"] = "framework/backend.body.twig";
        }
        $this->getParent($context)->display($context, array_merge($this->blocks, $blocks));
    }

    // line 18
    public function block_title($context, array $blocks = array())
    {
        echo $this->env->getExtension('translator')->trans("kitFramework - First Login");
    }

    // line 19
    public function block_head($context, array $blocks = array())
    {
        // line 20
        echo "  ";
        $this->displayParentBlock("head", $context, $blocks);
        echo "
  <link rel=\"stylesheet\" type=\"text/css\" href=\"";
        // line 21
        echo (isset($context["MANUFAKTUR_URL"]) ? $context["MANUFAKTUR_URL"] : null);
        echo "/Basic/Template/default/framework/css/login.css\" media=\"screen, projection\" />
";
    }

    // line 23
    public function block_content($context, array $blocks = array())
    {
        // line 24
        echo "  <div class=\"login_form\">
    <h2>";
        // line 25
        echo $this->env->getExtension('translator')->trans("kitFramework - Login");
        echo "</h2>
    ";
        // line 26
        if (($this->getAttribute($this->getAttribute((isset($context["app"]) ? $context["app"] : null), "translator"), "locale") == "de")) {
            // line 27
            echo "      ";
            // line 28
            echo "      ";
            if ((twig_length_filter($this->env, (isset($context["message"]) ? $context["message"] : null)) > 0)) {
                // line 29
                echo "        <div class=\"message\">";
                echo (isset($context["message"]) ? $context["message"] : null);
                echo "</div>
      ";
            } else {
                // line 31
                echo "        <div class=\"message\">
          <p>Hallo ";
                // line 32
                echo $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "display_name"), "vars"), "value");
                echo ",</p>
          <p>sie rufen das kitFramework zum ersten Mal auf.</p>
          <p>Sie sind bereits in ";
                // line 34
                echo (isset($context["usage"]) ? $context["usage"] : null);
                echo " eingeloggt. Damit Sie künftig automatisch mit Ihrem Konto am kitFramework angemeldet werden können, müssen Sie einmalig Ihr Passwort eingeben.</p>
        </div>
      ";
            }
            // line 37
            echo "    ";
        } else {
            // line 38
            echo "      ";
            // line 39
            echo "      ";
            if ((twig_length_filter($this->env, (isset($context["message"]) ? $context["message"] : null)) > 0)) {
                // line 40
                echo "        <div class=\"message\">";
                echo (isset($context["message"]) ? $context["message"] : null);
                echo "</div>
      ";
            } else {
                // line 42
                echo "        <div class=\"message\">
          <p>Hello ";
                // line 43
                echo $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["form"]) ? $context["form"] : null), "display_name"), "vars"), "value");
                echo ",</p>
          <p>this is the first time you are executing the kitFramework.</p>
          <p>You are already logged in  at ";
                // line 45
                echo (isset($context["usage"]) ? $context["usage"] : null);
                echo ". To enable a auto-login into the kitFramework you must authenticate with your password only one times.</p>
        </div>
      ";
            }
            // line 48
            echo "    ";
        }
        // line 49
        echo "    <form action=\"";
        echo (isset($context["FRAMEWORK_URL"]) ? $context["FRAMEWORK_URL"] : null);
        echo "/login/first/cms/check?usage=";
        echo (isset($context["usage"]) ? $context["usage"] : null);
        echo "\" method=\"post\" ";
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : null), 'enctype');
        echo ">
      ";
        // line 50
        echo $this->env->getExtension('form')->renderer->searchAndRenderBlock((isset($context["form"]) ? $context["form"] : null), 'widget');
        echo "
      <label>&nbsp;</label><input type=\"submit\" name=\"submit\" />
      <div class=\"clear\"></div>
    </form>
  </div>
";
    }

    // line 56
    public function block_footer($context, array $blocks = array())
    {
        // line 57
        echo "  ";
        $this->displayParentBlock("footer", $context, $blocks);
        echo "
  <script type=\"text/javascript\">
    if (typeof 'jQuery' !== 'undefined') {
      \$(document).ready(function() {
        ";
        // line 62
        echo "        \$('input[id=form_password]').focus();
      });
    }
  </script>
";
    }

    public function getTemplateName()
    {
        return "@phpManufaktur/Basic/Template/default/framework/first.login.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  159 => 62,  151 => 57,  148 => 56,  138 => 50,  129 => 49,  126 => 48,  120 => 45,  115 => 43,  112 => 42,  106 => 40,  103 => 39,  101 => 38,  98 => 37,  92 => 34,  87 => 32,  84 => 31,  78 => 29,  75 => 28,  73 => 27,  71 => 26,  67 => 25,  64 => 24,  61 => 23,  55 => 21,  50 => 20,  47 => 19,  41 => 18,  35 => 15,  33 => 14,  30 => 12,  28 => 11,  26 => 10,);
    }
}
