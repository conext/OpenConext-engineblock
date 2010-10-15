<?php

class EngineBlock_Corto_CoreProxy extends Corto_ProxyServer
{
    protected $_headers = array();
    protected $_output;

    protected $_serviceToControllerMapping = array(
        'singleSignOnService'       => 'authentication/idp/single-sign-on',
        'continueToIdP'             => 'authentication/idp/process-wayf',
        'assertionConsumerService'  => 'authentication/sp/consume-assertion',
        'continueToSP'              => 'authentication/sp/process-consent',
        'idPMetadataService'        => 'authentication/idp/metadata',
        'sPMetadataService'         => 'authentication/sp/metadata',
        'provideConsentService'     => 'authentication/idp/provide-consent',
        'processConsentService'     => 'authentication/idp/process-consent',
        'processedAssertionConsumerService' => 'authentication/proxy/processed-assertion'
    );
    
    // TEMPORARY VO HACK TO TRY TO FETCH hostedendity FROM SESSION
    
    public function serveRequest($uri)
    {
          
        
        $parameters = $this->_getParametersFromUri($uri);
        
        /*
        if (isset($_SESSION["hostedentity"]) && $_SESSION["hostedentity"]!=$parameters["EntityCode"]) {
            echo "Alternative hosted entity found in session, switching.";
            $parameters["EntityCode"] = $_SESSION["hostedentity"];
            var_dump("Loaded ".$parameters["EntityCode"]);
        } else {
            // Remember the last one
            $_SESSION["hostedentity"] = $parameters["EntityCode"];
            var_dump("Stored ".$_SESSION["hostedentity"]);
        } */
           
        $this->setCurrentEntity($parameters['EntityCode'], $parameters['RemoteIdPMd5']);

        // OK KIP EI. De sessie is afhankelijk van de entitycode. De entitycode van de sessie.
        $this->startSession();
        
        
        $this->getSessionLog()->debug("Started request with $uri, resulting in parameters: ". var_export($parameters, true));

        $serviceName = $parameters['ServiceName'];
        $this->getSessionLog()->debug("Calling service '$serviceName'");
        $this->getServicesModule()->$serviceName();
        $this->getSessionLog()->debug("Done calling service '$serviceName'");
    } 
    

    public function getParametersFromUrl($url)
    {
        $parameters = array(
            'EntityCode'        => 'main',
            'ServiceName'       => '',
            'RemoteIdPMd5Hash'  => '',
        );
        $urlPath = parse_url($url, PHP_URL_PATH); // /authentication/x/ServiceName[/remoteIdPMd5Hash]
        if ($urlPath[0] === '/') {
            $urlPath = substr($urlPath, 1);
        }

        foreach ($this->_serviceToControllerMapping as $serviceName => $controllerUri) {
            if (strstr($urlPath, $controllerUri)) {
                $urlPath = str_replace($controllerUri, $serviceName, $urlPath);
                list($parameters['ServiceName'], $parameters['RemoteIdPMd5Hash']) = explode('/', $urlPath);
                return $parameters;
            }
        }

        throw new Corto_ProxyServer_Exception("Unable to map URL '$url' to EngineBlock URL");
    }

    public function getHostedEntityUrl($entityCode, $serviceName = "", $remoteEntityId = "")
    {
        if (!isset($this->_serviceToControllerMapping[$serviceName])) {
            return parent::getHostedEntityUrl($entityCode, $serviceName, $remoteEntityId);
        }

        $scheme = 'http';
        if (isset($_SERVER['HTTPS'])) {
            $scheme = 'https';
        }

        $host = $_SERVER['HTTP_HOST'];

        $mappedUri = $this->_serviceToControllerMapping[$serviceName] .
            ($entityCode!="main" && $serviceName!= "sPMetadataService" ? '/' . "hosted:".$entityCode : '') . 
            ($remoteEntityId ? '/' . md5($remoteEntityId) : '');
        return $scheme . '://' . $host . ($this->_hostedPath ? $this->_hostedPath : '') . $mappedUri;
    }

    public function getOutput()
    {
        return $this->_output;
    }

    public function getHeaders()
    {
        return $this->_headers;
    }

    public function sendOutput($rawOutput)
    {
        $this->_output = $rawOutput;
    }

    public function sendHeader($name, $value)
    {
        $this->_headers[$name] = $value;
    }
}