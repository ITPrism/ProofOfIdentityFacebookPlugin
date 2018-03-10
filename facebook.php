<?php
/**
 * @package         IdentityProof
 * @subpackage      Plugins
 * @author          Todor Iliev
 * @copyright       Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license         http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPLv3
 */

// no direct access
defined('_JEXEC') or die;

jimport('Prism.init');
jimport('Identityproof.init');

/**
 * Proof of Identity - Facebook Plugin
 *
 * @package        IdentityProof
 * @subpackage     Plugins
 */
class plgIdentityproofFacebook extends JPlugin
{
    protected $autoloadLanguage = true;

    /**
     * @var JApplicationSite
     */
    protected $app;

    /**
     * This method prepares a code that will be included to step "Extras" on project wizard.
     *
     * @param string                   $context This string gives information about that where it has been executed the trigger.
     * @param stdClass                 $item    User data.
     * @param Joomla\Registry\Registry $params  The parameters of the component
     *
     * @return null|string
     */
    public function onDisplayVerification($context, &$item, &$params)
    {
        if (strcmp('com_identityproof.proof', $context) !== 0) {
            return null;
        }

        if ($this->app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp('html', $docType) !== 0) {
            return null;
        }

        if (!isset($item->id) or !$item->id) {
            return null;
        }

        $filter = JFilterInput::getInstance();

        // Get URI
        $uri    = JUri::getInstance();
        $verificationUrl = $filter->clean($uri->getScheme() . '://' . $uri->getHost()).'/index.php?option=com_identityproof&task=service.verify&service=facebook&' . JSession::getFormToken() . '=1';

        $profile = new Identityproof\Profile\Facebook(JFactory::getDbo());
        $profile->load(array('user_id' => $item->id));

        $loginUrl = '';
        if (!$profile->getId()) {
            $fb = new Facebook\Facebook([
                'app_id' => $this->params->get('app_id'),
                'app_secret' => $this->params->get('secret_key'),
                'default_graph_version' => 'v2.5'
            ]);

            $helper = $fb->getRedirectLoginHelper();
            $permissions = ['public_profile', 'user_website', 'user_hometown']; // optional
            $loginUrl = $helper->getLoginUrl($verificationUrl, $permissions);
        }

        // Get the path for the layout file
        $path = JPath::clean(JPluginHelper::getLayoutPath('identityproof', 'facebook'));

        // Render the login form.
        ob_start();
        include $path;
        $html = ob_get_clean();

        return $html;
    }

    /**
     * This method prepares a code that will be included to step "Extras" on project wizard.
     *
     * @param string                   $context This string gives information about that where it has been executed the trigger.
     * @param Joomla\Registry\Registry $params  The parameters of the component
     *
     * @return null|string
     */
    public function onVerify($context, &$params)
    {
        if (strcmp('com_identityproof.service.facebook', $context) !== 0) {
            return null;
        }

        if ($this->app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp('html', $docType) !== 0) {
            return null;
        }

        $output = array(
            'redirect_url' => JRoute::_(IdentityproofHelperRoute::getProofRoute(), false),
            'message' => ''
        );

        $userId  = JFactory::getUser()->get('id');
        if (!$userId) {
            $output['message'] = JText::_('PLG_IDENTITYPROOF_FACEBOOK_INVALID_USER');
            return $output;
        }

        try {
            $fb = new Facebook\Facebook([
                'app_id'                => $this->params->get('app_id'),
                'app_secret'            => $this->params->get('secret_key'),
                'default_graph_version' => 'v2.5'
            ]);

            $helper = $fb->getRedirectLoginHelper();

            $accessToken = $helper->getAccessToken();

        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            $output['message'] = JText::sprintf('PLG_IDENTITYPROOF_FACEBOOK_GRAPH_ERROR_S', $e->getMessage());
            return $output;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // When Graph returns an error
            $output['message'] = JText::sprintf('PLG_IDENTITYPROOF_FACEBOOK_GRAPH_ERROR_S', $e->getMessage());
            return $output;
        }

        if ($accessToken !== null) {
            $fb->setDefaultAccessToken($accessToken);
            $response = $fb->get('/me?fields=id,name,picture,link,gender,verified,hometown,website');
            $userNode = $response->getGraphUser();

            $profile  = new Identityproof\Profile\Facebook(JFactory::getDbo());
            $profile->load(array('user_id' => $userId));

            $picture  = $userNode->getPicture();
            $hometown = $userNode->getHometown();
            
            $data = array(
                'facebook_id' => $userNode['id'],
                'name' => $userNode['name'],
                'gender' => $userNode['gender'],
                'link' => $userNode['link'],
                'website' => $userNode['website'],
                'verified' => $userNode['verified'],
                'picture' => $picture->getUrl(),
                'hometown' => $hometown->getName()
            );

            if (!$profile->getId()) {
                $data['user_id'] = $userId;
            }

            $profile->bind($data);
            $profile->store();
        }

        return $output;
    }

    /**
     * This method prepares a code that will be included to step "Extras" on project wizard.
     *
     * @param string                   $context This string gives information about that where it has been executed the trigger.
     * @param Joomla\Registry\Registry $params  The parameters of the component
     *
     * @return null|string
     */
    public function onRemove($context, &$params)
    {
        if (strcmp('com_identityproof.service.facebook', $context) !== 0) {
            return null;
        }

        if ($this->app->isAdmin()) {
            return null;
        }

        $doc = JFactory::getDocument();
        /**  @var $doc JDocumentHtml */

        // Check document type
        $docType = $doc->getType();
        if (strcmp('html', $docType) !== 0) {
            return null;
        }

        $output = array(
            'redirect_url' => JRoute::_(IdentityproofHelperRoute::getProofRoute()),
            'message' => ''
        );

        $userId  = JFactory::getUser()->get('id');
        if (!$userId) {
            $output['message'] = JText::_('PLG_IDENTITYPROOF_FACEBOOK_INVALID_USER');
            return $output;
        }

        $profile = new Identityproof\Profile\Facebook(JFactory::getDbo());
        $profile->load(array('user_id' => $userId));

        if (!$profile->getId()) {
            $output['message'] = JText::_('PLG_IDENTITYPROOF_FACEBOOK_INVALID_PROFILE');
            return $output;
        }

        $profile->remove();
        $output['message'] = JText::_('PLG_IDENTITYPROOF_FACEBOOK_RECORD_REMOVED_SUCCESSFULLY');

        return $output;
    }
}
