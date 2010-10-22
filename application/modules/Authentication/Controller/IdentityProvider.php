<?php
 
class Authentication_Controller_IdentityProvider extends EngineBlock_Controller_Abstract
{
    public function singleSignOnAction($idPEntityId = null)
    {
        $this->setNoRender();

        try {
            $proxyServer = new EngineBlock_Corto_Adapter();
            $proxyServer->singleSignOn($idPEntityId);
        } catch(EngineBlock_Groups_Exception_UserDoesNotExist $e) {
            EngineBlock_ApplicationSingleton::getInstance()->getHttpResponse()->setRedirectUrl('/error/myerror');
        }
    }

    public function processWayfAction()
    {
        $this->setNoRender();

        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->processWayf();
    }

    public function metadataAction()
    {
        $this->setNoRender();

        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->idPMetadata();
    }

    public function processConsentAction()
    {
        $this->setNoRender();

        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->processConsent();
    }
}
