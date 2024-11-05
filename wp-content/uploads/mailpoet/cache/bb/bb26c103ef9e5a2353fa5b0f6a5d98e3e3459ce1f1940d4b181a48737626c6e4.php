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

/* form/templatesLegacy/settings/date_types.hbs */
class __TwigTemplate_a7edaf4d3eda50ae8f5c664259d28506fe01f5e0bfd4ada764d66acccaa23fa2 extends \MailPoetVendor\Twig\Template
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
        echo "<p class=\"clearfix\">
  <label>";
        // line 2
        echo $this->extensions['MailPoet\Twig\I18n']->translate("Type of date");
        echo "</label>
  <select name=\"params[date_type]\">
    ";
        // line 4
        $context['_parent'] = $context;
        $context['_seq'] = \MailPoetVendor\twig_ensure_traversable(($context["date_types"] ?? null));
        foreach ($context['_seq'] as $context["type"] => $context["label"]) {
            // line 5
            echo "      <option
        {{#ifCond params.date_type \"==\" \"";
            // line 6
            echo \MailPoetVendor\twig_escape_filter($this->env, $context["type"], "html", null, true);
            echo "\"}}
          selected=\"selected\"
        {{/ifCond}}
        data-format=\"";
            // line 9
            echo \MailPoetVendor\twig_escape_filter($this->env, (($__internal_1ab96fedfd46b5c4874d8613941c5f4369b290887cb58cd35d3fea134b4185be = (($__internal_02421c649df9ad88f1f3a422d31249c3bb8f105b216a799c60ff65ba9fa61371 = ($context["date_formats"] ?? null)) && is_array($__internal_02421c649df9ad88f1f3a422d31249c3bb8f105b216a799c60ff65ba9fa61371) || $__internal_02421c649df9ad88f1f3a422d31249c3bb8f105b216a799c60ff65ba9fa61371 instanceof ArrayAccess ? ($__internal_02421c649df9ad88f1f3a422d31249c3bb8f105b216a799c60ff65ba9fa61371[$context["type"]] ?? null) : null)) && is_array($__internal_1ab96fedfd46b5c4874d8613941c5f4369b290887cb58cd35d3fea134b4185be) || $__internal_1ab96fedfd46b5c4874d8613941c5f4369b290887cb58cd35d3fea134b4185be instanceof ArrayAccess ? ($__internal_1ab96fedfd46b5c4874d8613941c5f4369b290887cb58cd35d3fea134b4185be[0] ?? null) : null), "html", null, true);
            echo "\" value=\"";
            echo \MailPoetVendor\twig_escape_filter($this->env, $context["type"], "html", null, true);
            echo "\"
      >";
            // line 10
            echo \MailPoetVendor\twig_escape_filter($this->env, $context["label"], "html", null, true);
            echo "</option>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['type'], $context['label'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 12
        echo "  </select>
  <input type=\"hidden\" name=\"params[date_format]\" value=\"\" />
</p>

<script type=\"text/javascript\">
  jQuery(function(\$) {
    \$('select[name=\"params[date_type]\"]').on('change', function() {
      // set default date format depending on date type
      \$('input[name=\"params[date_format]\"]')
        .val(\$(this)
        .find('option:selected')
        .data('format'));
    });
    // set default format
    \$('select[name=\"params[date_type]\"]').trigger('change');
  });
<{{!}}/script>";
    }

    public function getTemplateName()
    {
        return "form/templatesLegacy/settings/date_types.hbs";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  72 => 12,  64 => 10,  58 => 9,  52 => 6,  49 => 5,  45 => 4,  40 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "form/templatesLegacy/settings/date_types.hbs", "C:\\xampp\\htdocs\\testmmsdcmm\\wp-content\\plugins\\mailpoet\\views\\form\\templatesLegacy\\settings\\date_types.hbs");
    }
}
