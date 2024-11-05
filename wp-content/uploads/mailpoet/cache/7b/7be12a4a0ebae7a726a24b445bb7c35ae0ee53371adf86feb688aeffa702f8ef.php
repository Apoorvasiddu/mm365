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

/* form/templatesLegacy/settings/segment_selection_item.hbs */
class __TwigTemplate_8b33eea8510631b44c9bc2dfc063fd9594643665925047a1f6939f05a6ca3950 extends \MailPoetVendor\Twig\Template
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
        echo "{{#each segments}}
<li data-segment=\"{{ id }}\">
  <label>
    <input class=\"mailpoet_segment_id\" type=\"hidden\" value=\"{{ id }}\" />
    <input class=\"mailpoet_is_checked\" type=\"checkbox\" value=\"1\"
      {{#ifCond is_checked '>' 0}}checked=\"checked\"{{/ifCond}} />
    <input class=\"mailpoet_segment_name\" type=\"hidden\" value=\"{{ name }}\" />
    {{ name }}
  </label>
  <a class=\"remove\" href=\"javascript:;\">";
        // line 10
        echo $this->extensions['MailPoet\Twig\I18n']->translate("Remove");
        echo "</a>
  <a class=\"handle\" href=\"javascript:;\">";
        // line 11
        echo $this->extensions['MailPoet\Twig\I18n']->translate("Move");
        echo "</a>
</li>
{{/each}}";
    }

    public function getTemplateName()
    {
        return "form/templatesLegacy/settings/segment_selection_item.hbs";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  52 => 11,  48 => 10,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "form/templatesLegacy/settings/segment_selection_item.hbs", "C:\\xampp\\htdocs\\testmmsdcmm\\wp-content\\plugins\\mailpoet\\views\\form\\templatesLegacy\\settings\\segment_selection_item.hbs");
    }
}
