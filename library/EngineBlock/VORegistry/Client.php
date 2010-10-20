<?php

/**
 * Implementation of the Engine Block internal Virtual Organization 
 * Registry interface.
 * 
 * @author ivo
 */
class EngineBlock_VORegistry_Client
{
    /**
     * Returns an array with metadata about a Virtual Organisation
     * @param String $voIdentifier The identifier of the VO
     * @return array An array with 3 keys:
     *               - groupprovideridentifier: the identifier of the group 
     *                 directory system we need to query to find out the 
     *                 members of this VO, its groups etc.
     *               - groupidentifier: the identifier of the group in this 
     *                 group directory that contains the VO members
     *               - groupstem: if present, defines which stem in the group
     *                 directory to query. A dedicated group directory would 
     *                 not use a stem.
     */
    public function getGroupProviderMetadata($voIdentifier)
    {
        // @todo replace hardcoded values for actual lookup in VORegistry
        return array("groupprovideridentifier"=>"default",
                     "groupidentifier"=>"pci_members",
                     "groupstem"=>"nl:pci");    
    }
    
  
    
}