<?php

namespace SpeckenbachNet\Component\LinkListM\Administrator\View\Dashboard;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use SpeckenbachNet\Component\LinkListM\Administrator\Model\DashboardModel;

/**
 * Component start page.
 */
class HtmlView extends BaseHtmlView
{
    /** The author's tip page (fixed: it belongs to the developer, not the site owner). */
    public const DONATE_URL = 'https://paypal.me/michaelspeckenbach';

    /** Bug reports go to the GitHub issues of the repository (fixed: it belongs to the developer). */
    public const ISSUES_URL = 'https://github.com/SpeckenbachNet/LinkListM/issues/new';

    /** @var array{links: int, categories: int} */
    public array $tiles = [];

    /** @var object[] */
    public array $categories = [];

    public array $todos     = [];
    public array $info      = [];
    public array $checks    = [];
    public array $changelog = [];

    /** @var array{state: string, version: string, security: int} */
    public array $update = [];

    public bool $canCreate  = false;
    public bool $canOptions = false;

    /** May open System → Updates / Update Sites / Database (com_installer). */
    public bool $canUpdate = false;

    public string $donateUrl = self::DONATE_URL;

    /** New GitHub issue, title and versions filled in */
    public string $issueUrl = '';

    public function display($tpl = null)
    {
        /** @var DashboardModel $model */
        $model = $this->getModel();

        $this->tiles      = $model->getTileCounts();
        $this->categories = $model->getCategories();
        $this->info       = $model->getInfo();
        $this->update     = $model->getUpdateStatus($this->info['version']);
        $this->todos      = $model->getTodos($this->categories, $this->update);
        $this->checks     = $model->getChecks($this->info['version'], $this->update);
        $this->changelog  = $model->getChangelog();
        $this->issueUrl   = self::ISSUES_URL . '?' . http_build_query([
            'title' => 'com_linklistm ' . $this->info['version'] . ': ',
            'body'  => "LinkListM " . $this->info['version'] . ", Joomla " . JVERSION . ", PHP " . PHP_VERSION . "\n\n"
                . "### What happened?\n\n\n### Steps to reproduce\n\n1. \n",
        ], '', '&', PHP_QUERY_RFC3986);

        $canDo            = ContentHelper::getActions('com_linklistm');
        $this->canCreate  = (bool) $canDo->get('core.create');
        $this->canOptions = $canDo->get('core.admin') || $canDo->get('core.options');
        $this->canUpdate  = (bool) Factory::getApplication()->getIdentity()->authorise('core.manage', 'com_installer');

        ToolbarHelper::title(Text::_('COM_LINKLISTM_DASHBOARD_TITLE'), 'link');

        if ($this->canOptions) {
            ToolbarHelper::preferences('com_linklistm');
        }

        parent::display($tpl);
    }
}
