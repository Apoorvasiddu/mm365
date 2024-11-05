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

/* form/templatesLegacy/settings/required.hbs */
class __TwigTemplate_be9c08ea12ad444082965496ba0fa22a949cd1b2b979313a637f5b1da23055e4 extends \MailPoetVendor\Twig\Template
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
        echo $this->extensions['MailPoet\Twig\I18n']->translate("Is this field mandatory?");
        echo "</label>
  <span class=\"group\">
    <label>
      <input type=\"radio\"
        class=\"mailpoet_radio\"
        name=\"params[required]\"
        value=\"1\"
        {{#if params.required}}checked=\"checked\"{{/if}} />";
        // line 9
        echo $this->extensions['MailPoet\Twig\I18n']->translate("Yes");
        echo "
    </label>
    <label>
      <input
        class=\"mailpoet_radio\"
        type=\"radio\"
        name=\"params[required]\"
        value=\"\"
        {{#unless params.required}}checked=\"checked\"{{/unless}} />";
        // line 17
        echo $this->extensions['MailPoet\Twig\I18n']->translate("No");
        echo "
    </label>
  </span>
</p>";
    }

    public function getTemplateName()
    {
        return "form/templatesLegacy/settings/required.hbs";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  61 => 17,  50 => 9,  40 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "form/templatesLegacy/settings/required.hbs", "C:\\xampp\\htdocs\\testmmsdcmm\\wp-content\\plugins\\mailpoet\\views\\form\\templatesLegacy\\settings\\required.hbs");
    }
}
