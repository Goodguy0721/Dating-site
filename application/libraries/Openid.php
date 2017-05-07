<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/**
 * OpenID Library
 *
 * @package		CodeIgniter
 *
 * @author		 bardelot
 *
 * @see				http://cakebaker.42dh.com/2007/01/11/cakephp-and-openid/
 *						 & http://openidenabled.com/php-openid/
 */
class Openid
{
    public $storePath = 'tmp';

    public $sreg_enable = false;
    public $sreg_required = null;
    public $sreg_optional = null;
    public $sreg_policy = null;

    public $pape_enable = false;
    public $pape_policy_uris = null;

    public $request_to;
    public $trust_root;
    public $ext_args;

    public function __construct()
    {
        $CI = &get_instance();
        $CI->config->load('openid');
        $this->storePath = $CI->config->item('openid_storepath');

        $this->_doIncludes();
        log_message('debug', "OpenID Class Initialized");
    }

    public function _doIncludes()
    {
        set_include_path(dirname(__FILE__) . '/Openid' . PATH_SEPARATOR . get_include_path());
        require_once "Auth/OpenID/Consumer.php";
        require_once "Auth/OpenID/FileStore.php";
        require_once "Auth/OpenID/SReg.php";
        require_once "Auth/OpenID/PAPE.php";
    }

    public function set_sreg($enable, $required = null, $optional = null, $policy = null)
    {
        $this->sreg_enable = $enable;
        $this->sreg_required = $required;
        $this->sreg_optional = $optional;
        $this->sreg_policy = $policy;
    }

    public function set_pape($enable, $policy_uris = null)
    {
        $this->pape_enable = $enable;
        $this->pape_policy_uris = $policy_uris;
    }

    public function set_request_to($uri)
    {
        $this->request_to = $uri;
    }

    public function set_trust_root($trust_root)
    {
        $this->trust_root = $trust_root;
    }

    public function set_args($args)
    {
        $this->ext_args = $args;
    }

    public function _set_message($error, $msg, $val = '', $sub = '%s')
    {
        //$CI =& get_instance();
        //$CI->lang->load('openid', 'english');
        //str_replace($sub, $val, $CI->lang->line($msg));
        return $msg;
    //	if ($error){
    //		exit;
    //	}
    }

    public function authenticate($openId)
    {
        $consumer = $this->_getConsumer();
        $authRequest = $consumer->begin($openId);

        // No auth request means we can't begin OpenID.
        if (!$authRequest) {
            return $this->_set_message(true, 'openid_auth_error');
        }

        if ($this->sreg_enable) {
            $sreg_request = Auth_OpenID_SRegRequest::build($this->sreg_required, $this->sreg_optional, $this->sreg_policy);
            if ($sreg_request) {
                $authRequest->addExtension($sreg_request);
            } else {
                return $this->_set_message(true, 'openid_sreg_failed');
            }
        }

        if ($this->pape_enable) {
            $pape_request = new Auth_OpenID_PAPE_Request($this->pape_policy_uris);

            if ($pape_request) {
                $authRequest->addExtension($pape_request);
            } else {
                return $this->_set_message(true, 'openid_pape_failed');
            }
        }

        if ($this->ext_args != null) {
            foreach ($this->ext_args as $extensionArgument) {
                if (count($extensionArgument) == 3) {
                    $authRequest->addExtensionArg($extensionArgument[0], $extensionArgument[1], $extensionArgument[2]);
                }
            }
        }

        if ($authRequest->shouldSendRedirect()) {
            $redirect_url = $authRequest->redirectURL($this->trust_root, $this->request_to);
            // If the redirect URL can't be built, display an error
            // message.
            if (Auth_OpenID::isFailure($redirect_url)) {
                return $this->_set_message(true, 'openid_redirect_failed', $redirect_url->message);
            } else {
                // Send redirect.
                header("Location: " . $redirect_url);
            }
        } else {
            // Generate form markup and render it.
            $form_id = 'openid_message';
            $form_html = $authRequest->formMarkup($this->trust_root, $this->request_to, false, array('id' => $form_id));

            // Display an error if the form markup couldn't be generated;
            // otherwise, render the HTML.
            if (Auth_OpenID::isFailure($form_html)) {
                return $this->_set_message(true, 'openid_redirect_failed', $form_html->message);
            } else {
                $page_contents = array(
                     "<html><head><title>",
                     "OpenID transaction in progress",
                     "</title></head>",
                     "<body onload='document.getElementById(\"" . $form_id . "\").submit()'>",
                     $form_html,
                     "</body></html>", );
                print implode("\n", $page_contents);
            }
        }
    }

    public function getResponse()
    {
        $consumer = $this->_getConsumer();
        $response = $consumer->complete($this->request_to);

        return $response;
    }

    public function _getConsumer()
    {
        if (!file_exists($this->storePath) && !mkdir($this->storePath)) {
            $this->_set_message(true, 'openid_storepath_failed', $this->storePath);
        }

        $store = new Auth_OpenID_FileStore($this->storePath);
        $consumer = new Auth_OpenID_Consumer($store);

        return $consumer;
    }
}
