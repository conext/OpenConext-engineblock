<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

require_once('Surfnet/Auth/Adapter/Saml.php');
require_once('Surfnet/Identity.php');

/**
 * Action helper to force authentication in every action.
 *
 * @todo make this more flexible: Accept more different types of identities.
 * @author marc
 */
class EngineBlock_Authenticator
{
    const AUTH_DISPLAY_NAME_SAML_ATTRIBUTE = 'urn:mace:dir:attribute-def:cn';

    public static function authenticate()
    {
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_NonPersistent());
        $adapter = new Surfnet_Auth_Adapter_Saml();

        $res = $auth->authenticate($adapter);

        $samlIdentity = $res->getIdentity();
        $identity = new Surfnet_Identity($samlIdentity['nameid'][0]);
        $identity->displayName = $samlIdentity[self::AUTH_DISPLAY_NAME_SAML_ATTRIBUTE][0];

        return $identity;
    }
}