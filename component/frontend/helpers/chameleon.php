<?php
/**
 * @package   AkeebaReleaseSystem
 * @copyright Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die();

JLoader::import('joomla.application.module.helper');

/**
 * Chameleon skinning for Joomla!
 */
class ArsHelperChameleon
{
	/**
	 * Returns a module object based on custom contents
	 *
	 * @param string $title    The title to show
	 * @param string $contents The HTML inside the module
	 * @param array  $params   Extra parameters
	 */
	static public function getModule($title, $contents, $params = array())
	{
		$jsonParams = json_encode($params);

		$result = new StdClass;
		$result->id = 0;
		$result->title = $title;
		$result->module = 'mod_custom';
		$result->position = '';
		$result->content = $contents;
		$result->showtitle = 1;
		$result->control = '';
		$result->params = $jsonParams;
		$result->user = 0;

		return $result;
	}

	/**
	 * Loads a layout file and renders it as a module
	 *
	 * @param string $title    The title of the module
	 * @param string $basedir  The base path holding the templates
	 * @param string $template The layout name (optional; do not include .php)
	 * @param array  $params   Any module parameters to pass (optional)
	 */
	static public function renderTemplate($title, $basedir, $template = 'default', $params = array())
	{
		// Get the template's contents
		@ob_start();
		@include $basedir . '/' . $template . '.php';
		$contents = ob_get_clean();

		// Set up the rendering attributes
		$attribs = array();
		if (array_key_exists('style', $params))
		{
			$attribs['style'] = $params['style'];
			unset($params['style']);
		}
		else
		{
			$attribs['style'] = 'rounded';
		}

		// Get the rendered module
		$module = self::getModule($title, $contents, $params);
		unset($contents);
		$rendered = JModuleHelper::renderModule($module, $attribs);
		unset($module);

		return $rendered;
	}

	/**
	 * Fetches the additional view parameters for a specific category of modules
	 *
	 * @param string $category The module category, i.e. 'category','release','item'
	 */
	static public function getParams($category = 'default', $bleeding_edge = false)
	{
		static $params = null;

		if (is_null($params))
		{
			JLoader::import('joomla.application.component');
			$component = JComponentHelper::getComponent('com_ars');
			$params = ($component->params instanceof JRegistry) ? $component->params : new JRegistry($component->params);
		}

		switch ($category)
		{
			case 'category':
			default:
				$style = $params->get('categorystyle', 'rounded');
				$sfx = $params->get('categorysuffix', '');

				break;

			case 'release':
				$style = $params->get('releasestyle', 'rounded');
				$sfx = $params->get('releasesuffix', '');

				break;

			case 'item':
				$style = $params->get('itemstyle', 'rounded');
				$sfx = $params->get('itemsuffix', '');

				break;
		}

		if ($bleeding_edge)
		{
			$sfx2 = $params->get('besuffix', '');
			if (!empty($sfx2))
			{
				$sfx .= ' ' . $sfx2;
			}
		}

		return array(
			'style'           => $style,
			'moduleclass_sfx' => $sfx
		);
	}

	static public function getReadOn($title, $link, $style = "readontemplate")
	{
		static $params = null;

		if (is_null($params))
		{
			JLoader::import('joomla.application.component');
			$component = JComponentHelper::getComponent('com_ars');
			$params = ($component->params instanceof JRegistry) ? $component->params : new JRegistry($component->params);
		}

		if ($style == 'readontemplate')
		{
			$default_template = '<a class="readon" href="%s">%s</a>';
		}
		else
		{
			$default_template = '<a class="directlink" href="%s">%s</a>';
		}

		$template = $params->get($style, $default_template);

		$template = str_replace('&quot;', '"', $template);
		$template = str_replace('[[', '\\<', $template);
		$template = str_replace(']]', '\\>', $template);
		$template = str_replace('[', '<', $template);
		$template = str_replace(']', '>', $template);
		$template = str_replace('\\<', '[', $template);
		$template = str_replace('\\>', ']', $template);

		if ($style == 'readontemplate')
		{
			return sprintf($template, $link, $title);
		}
		else
		{
			$default_description = JText::_('COM_ARS_CONFIG_DIRECTLINKDESCRIPTION_DEFAULT');
			$description = $params->get('directlink_description', $default_description);

			return sprintf($template, $link, $description, $title);
		}
	}
}