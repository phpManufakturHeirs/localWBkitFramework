<?php

/* @phpManufaktur/Basic/Template/default/framework/welcome.twig */
class __TwigTemplate_4f59f2ccc3a7cfc43fbf234b0f7678b4 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'head' => array($this, 'block_head'),
            'content' => array($this, 'block_content'),
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
        echo $this->env->getExtension('translator')->trans("kitFramework - Welcome");
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
        echo "/Basic/Template/default/framework/css/welcome.css\" media=\"screen, projection\" />
  <link href=\"//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css\" rel=\"stylesheet\">
";
    }

    // line 24
    public function block_content($context, array $blocks = array())
    {
        // line 25
        echo "  <a name=\"top\"></a>
  <div id=\"welcome_container\">
    <div class=\"hint\">
      <p><i class=\"icon-info-sign icon-2x\"></i> Bitte besuchen Sie die Seite <a href=\"https://kit2.phpmanufaktur.de/firststeps\" target=\"_blank\">Erste Schritte mit dem kitFramework</a> für einen Einstieg.</p>
    </div>
    <h1>Herzlich Willkommen beim kitFramework!</h1>
    ";
        // line 31
        if ((twig_length_filter($this->env, (isset($context["message"]) ? $context["message"] : null)) > 0)) {
            // line 32
            echo "      <div class=\"message\">";
            echo (isset($context["message"]) ? $context["message"] : null);
            echo "</div>
    ";
        } else {
            // line 34
            echo "      <div class=\"message\"><p>Dieser Übersichtsdialog befindet sich noch im Experimentierstadium, Vorschläge und Ideen sind herzlich willkommen!</p></div>
    ";
        }
        // line 36
        echo "    <h2>Installierte Erweiterungen</h2>
    <p>Diese Erweiterungen sind bereits in kitFramework installiert und können verwendet werden.</p>
    ";
        // line 38
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["register_items"]) ? $context["register_items"] : null));
        foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
            // line 39
            echo "      <div class=\"extension_container\">
        <div class=\"extension_image\">
          ";
            // line 41
            if ((twig_length_filter($this->env, $this->getAttribute((isset($context["item"]) ? $context["item"] : null), "start_url")) > 0)) {
                echo "<a href=\"";
                echo (isset($context["FRAMEWORK_URL"]) ? $context["FRAMEWORK_URL"] : null);
                echo $this->getAttribute((isset($context["item"]) ? $context["item"] : null), "start_url");
                echo "?usage=";
                echo (isset($context["usage"]) ? $context["usage"] : null);
                echo "\">";
            }
            // line 42
            echo "            <img src=\"";
            if ((twig_length_filter($this->env, $this->getAttribute((isset($context["item"]) ? $context["item"] : null), "logo_blob")) > 0)) {
                echo "data:image/";
                echo $this->getAttribute((isset($context["item"]) ? $context["item"] : null), "logo_type");
                echo ";charset=utf-8;base64,";
                echo $this->getAttribute((isset($context["item"]) ? $context["item"] : null), "logo_blob");
            } else {
                echo $this->getAttribute((isset($context["item"]) ? $context["item"] : null), "logo_url");
            }
            echo "\" width=\"";
            echo $this->getAttribute((isset($context["item"]) ? $context["item"] : null), "logo_width");
            echo "\" height=\"";
            echo $this->getAttribute((isset($context["item"]) ? $context["item"] : null), "logo_height");
            echo "\" alt=\"";
            echo $this->getAttribute($this->getAttribute((isset($context["item"]) ? $context["item"] : null), "description"), "title");
            echo "\" />
          ";
            // line 43
            if ((twig_length_filter($this->env, $this->getAttribute((isset($context["item"]) ? $context["item"] : null), "start_url")) > 0)) {
                echo "</a>";
            }
            // line 44
            echo "        </div>
        ";
            // line 45
            if ($this->getAttribute((isset($context["item"]) ? $context["item"] : null), "update_available")) {
                // line 46
                echo "          <div class=\"extension_update\">
            <strong>";
                // line 47
                echo $this->env->getExtension('translator')->trans("Update available!");
                echo "</strong><br />
            Release ";
                // line 48
                echo $this->getAttribute((isset($context["item"]) ? $context["item"] : null), "release_available");
                echo "<br />
            <a href=\"";
                // line 49
                echo (isset($context["FRAMEWORK_URL"]) ? $context["FRAMEWORK_URL"] : null);
                echo "/admin/updater/update/";
                echo $this->getAttribute((isset($context["item"]) ? $context["item"] : null), "id");
                echo "?usage=";
                echo (isset($context["usage"]) ? $context["usage"] : null);
                echo "\">Execute Update</a>
          </div>
        ";
            }
            // line 52
            echo "        ";
            if ((twig_length_filter($this->env, $this->getAttribute((isset($context["item"]) ? $context["item"] : null), "start_url")) > 0)) {
                // line 53
                echo "          <div class=\"extension_start\">
            <a href=\"";
                // line 54
                echo (isset($context["FRAMEWORK_URL"]) ? $context["FRAMEWORK_URL"] : null);
                echo $this->getAttribute((isset($context["item"]) ? $context["item"] : null), "start_url");
                echo "?usage=";
                echo (isset($context["usage"]) ? $context["usage"] : null);
                echo "\">";
                echo $this->env->getExtension('translator')->trans("Execute");
                echo "</a>
          </div>
        ";
            }
            // line 57
            echo "      </div>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 59
        echo "    <div class=\"clear\"></div>
    <h2>Verfügbare Erweiterungen für das kitFramework</h2>
    <p>Diese Liste wird automatisch aktualisiert, falls über Github ein neuer <a href=\"https://github.com/phpManufaktur/kitFramework_Catalog\" target=\"blank\">kitFramework Katalog</a> verfügbar ist.</p>
    ";
        // line 62
        $context['_parent'] = (array) $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["catalog_items"]) ? $context["catalog_items"] : null));
        foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
            // line 63
            echo "      <div class=\"extension_container\">
        <div class=\"extension_image\">
          <img src=\"data:image/";
            // line 65
            echo $this->getAttribute((isset($context["item"]) ? $context["item"] : null), "logo_type");
            echo ";charset=utf-8;base64,";
            echo $this->getAttribute((isset($context["item"]) ? $context["item"] : null), "logo_blob");
            echo "\" width=\"";
            echo $this->getAttribute((isset($context["item"]) ? $context["item"] : null), "logo_width");
            echo "\" height=\"";
            echo $this->getAttribute((isset($context["item"]) ? $context["item"] : null), "logo_height");
            echo "\" alt=\"";
            echo $this->getAttribute($this->getAttribute((isset($context["item"]) ? $context["item"] : null), "description"), "title");
            echo "\" />
        </div>
        <div class=\"extension_title\">";
            // line 67
            echo $this->getAttribute($this->getAttribute((isset($context["item"]) ? $context["item"] : null), "description"), "title");
            echo "</div>
        <div class=\"extension_description\">";
            // line 68
            echo $this->getAttribute($this->getAttribute((isset($context["item"]) ? $context["item"] : null), "description"), "short");
            echo "</div>
        <div class=\"extension_release\">Release ";
            // line 69
            echo $this->getAttribute((isset($context["item"]) ? $context["item"] : null), "release");
            echo " <em>(";
            echo twig_date_format_filter($this->env, $this->getAttribute((isset($context["item"]) ? $context["item"] : null), "date"), "d.m.Y");
            echo ")</em></div>
        ";
            // line 70
            if ($this->getAttribute($this->getAttribute($this->getAttribute((isset($context["item"]) ? $context["item"] : null), "info", array(), "any", false, true), "download", array(), "any", false, true), "github", array(), "any", true, true)) {
                // line 71
                echo "          <p><a href=\"";
                echo (isset($context["FRAMEWORK_URL"]) ? $context["FRAMEWORK_URL"] : null);
                echo "/admin/updater/install/";
                echo $this->getAttribute((isset($context["item"]) ? $context["item"] : null), "id");
                echo "?usage=";
                echo (isset($context["usage"]) ? $context["usage"] : null);
                echo "\">Install</a></p>
        ";
            }
            // line 73
            echo "      </div>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
        $context = array_merge($_parent, array_intersect_key($context, $_parent));
        // line 75
        echo "    <div class=\"clear\"></div>
    <div class=\"extension_scan_directories\">
      <a href=\"";
        // line 77
        echo (isset($context["scan_extensions"]) ? $context["scan_extensions"] : null);
        echo "#top\">";
        echo $this->env->getExtension('translator')->trans("Scan for installed extensions");
        echo "</a>
    </div>
    <div class=\"extension_scan_catalog\">
      <a href=\"";
        // line 80
        echo (isset($context["scan_catalog"]) ? $context["scan_catalog"] : null);
        echo "#top\">";
        echo $this->env->getExtension('translator')->trans("Scan the online catalog for available extensions");
        echo "</a>
    </div>

  </div>
";
    }

    public function getTemplateName()
    {
        return "@phpManufaktur/Basic/Template/default/framework/welcome.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  248 => 80,  240 => 77,  236 => 75,  229 => 73,  219 => 71,  217 => 70,  211 => 69,  207 => 68,  203 => 67,  190 => 65,  186 => 63,  182 => 62,  177 => 59,  170 => 57,  159 => 54,  156 => 53,  153 => 52,  143 => 49,  139 => 48,  135 => 47,  132 => 46,  130 => 45,  127 => 44,  123 => 43,  105 => 42,  96 => 41,  92 => 39,  88 => 38,  84 => 36,  80 => 34,  74 => 32,  72 => 31,  64 => 25,  61 => 24,  54 => 21,  49 => 20,  46 => 19,  40 => 18,  34 => 15,  32 => 14,  29 => 12,  27 => 11,  25 => 10,  19 => 10,);
    }
}
