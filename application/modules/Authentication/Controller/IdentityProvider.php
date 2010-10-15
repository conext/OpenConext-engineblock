<?php
 
class Authentication_Controller_IdentityProvider extends EngineBlock_Controller_Abstract
{
    public function singleSignOnAction($argument = null)
    {
        $this->setNoRender();
        
        $idPEntityId = null;
        
        $proxyServer = new EngineBlock_Corto_Adapter();
        if (substr($argument, 0, 3)=="vo:") {
            $proxyServer->setVirtualOrganisationContext(substr($argument,3));
        } else {
            
            $idPEntityId = $argument;
        }
        $proxyServer->singleSignOn($idPEntityId);
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