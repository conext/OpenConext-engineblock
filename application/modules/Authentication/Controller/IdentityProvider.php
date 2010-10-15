<?php
 
class Authentication_Controller_IdentityProvider extends EngineBlock_Controller_Abstract
{
    public function singleSignOnAction($argument = null)
    {
        $this->setNoRender();
        
        // VO CHANGE
        $idPEntityId = null;
        $hostedEntity = null;
        
        if (substr($argument, 0, 7)=="hosted:") {
        	$hostedEntity = substr($argument,7);
        } else {
        	$idPEntityId = $argument;
        }

        $proxyServer = new EngineBlock_Corto_Adapter($hostedEntity);
        $proxyServer->singleSignOn($idPEntityId);
    }

    public function processWayfAction($argument = null)
    {
        $this->setNoRender();
        
        // VO CHANGE
        $hostedEntity = null;
        
        if (substr($argument, 0, 7)=="hosted:") {
            $hostedEntity = substr($argument,7);
        }

        $proxyServer = new EngineBlock_Corto_Adapter($hostedEntity);
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
        
        // VO HACK
        $hostedEntity = "pci";

        $proxyServer = new EngineBlock_Corto_Adapter($hostedEntity);
        $proxyServer->processConsent();
    }
}