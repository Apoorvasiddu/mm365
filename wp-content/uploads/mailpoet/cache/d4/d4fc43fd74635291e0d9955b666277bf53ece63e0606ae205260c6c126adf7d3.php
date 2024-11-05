<?php

use MailPoetVendor\Twig\Environment;
use MailPoetVendor\Twig\Error\LoaderError;
use MailPoetVendor\Twig\Error\RuntimeError;
use MailPoetVendor\Twig\Extension\SandboxExtension;
use MailPoetVendor\Twig\Markup;
use MailPoetVendor\Twig\Sandbox\SecurityError;
use MailPoetVendor\Twig\Sandbox\SecurityNotAllowedTagError;
use MailPoetVendor\Twig\Sandbox\SecurityNotAllowedFilterError;
use MailPoetVendor\Twig\Sandbox\SecurityNotAllowedFunctionError;
use MailPoetVendor\Twig\Source;
use MailPoetVendor\Twig\Template;

/* form/templatesLegacy/blocks/date_months.hbs */
class __TwigTemplate_aa35b016d3c3fa9f95f988d3cf8634c58698c29f5810fe3a29ccc3772aa9d7db extends \MailPoetVendor\Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        $context["currentMonth"] = \MailPoetVendor\twig_date_format_filter($this->env, "now", "n");
        // line 2
        echo "<select id=\"{{ id }}_months\">
  <option value=\"\">";
        // line 3
        echo $this->extensions['MailPoet\Twig\I18n']->translate("Month");
        echo "</option>
  ";
        // line 4
        $context['_parent'] = $context;
        $context['_seq'] = \MailPoetVendor\twig_ensure_traversable(range(1, 12));
        foreach ($context['_seq'] as $context["_key"] => $context["month"]) {
            // line 5
            echo "    <option
      ";
            // line 6
            if ((($context["currentMonth"] ?? null) == $context["month"])) {
                // line 7
                echo "      {{#if params.is_default_today}}selected=\"selected\"{{/if}}
      ";
            }
            // line 9
            echo "    >
    ";
            // line 10
            echo \MailPoetVendor\twig_escape_filter($this->env, (($__internal_1ab96fedfd46b5c4874d8613941c5f4369b290887cb58cd35d3fea134b4185be = ($context["month_names"] ?? null)) && is_array($__internal_1ab96fedfd46b5c4874d8613941c5f4369b290887cb58cd35d3fea134b4185be) || $__internal_1ab96fedfd46b5c4874d8613941c5f4369b290887cb58cd35d3fea134b4185be instanceof ArrayAccess ? ($__internal_1ab96fedfd46b5c4874d8613941c5f4369b290887cb58cd35d3fea134b4185be[($context["month"] - 1)] ?? null) : null), "html", null, true);
            echo "
    </option>
  ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['month'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 13
        echo "</select>";
    }

    public function getTemplateName()
    {
        return "form/templatesLegacy/blocks/date_months.hbs";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  71 => 13,  62 => 10,  59 => 9,  55 => 7,  53 => 6,  50 => 5,  46 => 4,  42 => 3,  39 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "form/templatesLegacy/blocks/date_months.hbs", "C:\\xampp\\htdocs\\testmmsdcmm\\wp-content\\plugins\\mailpoet\\views\\form\\templatesLegacy\\blocks\\date_months.hbs");
    }
}
