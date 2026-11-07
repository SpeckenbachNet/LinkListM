<?php

namespace SpeckenbachNet\Plugin\EditorsXtd\LinkListM\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Editor\Button\Button;
use Joomla\CMS\Event\Editor\EditorButtonsSetupEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

/**
 * Editor button "Link list": opens the LinkListM picker, which inserts a {linklistm ...} tag.
 */
final class LinkListM extends CMSPlugin implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return ['onEditorButtonsSetup' => 'onEditorButtonsSetup'];
    }

    public function onEditorButtonsSetup(EditorButtonsSetupEvent $event): void
    {
        if (\in_array($this->_name, $event->getDisabledButtons(), true)) {
            return;
        }

        $app = $this->getApplication();

        // The picker is a backend page; frontend editing does not get the button
        if (!$app->isClient('administrator') || !ComponentHelper::isEnabled('com_linklistm')) {
            return;
        }

        if (!$app->getIdentity()->authorise('core.manage', 'com_linklistm')) {
            return;
        }

        $this->loadLanguage();

        $event->getButtonsRegistry()->add(new Button(
            $this->_name,
            [
                'action' => 'modal',
                // The editor script replaces "editor" with the actual editor id
                'link'   => 'index.php?option=com_linklistm&view=picker&tmpl=component&editor=' . $event->getEditorId(),
                'text'   => Text::_('PLG_EDITORS-XTD_LINKLISTM_BUTTON'),
                'icon'   => 'link',
                // Whole plugin name, needed by Joomla for backward compatibility
                'name'   => $this->_type . '_' . $this->_name,
            ]
        ));
    }
}
