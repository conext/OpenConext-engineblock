<?php
/**
 * DbPatch
 *
 * Copyright (c) 2011, Sandy Pleyte.
 * Copyright (c) 2010-2011, Martijn de Letter.
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *  * Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *
 *  * Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in
 *    the documentation and/or other materials provided with the
 *    distribution.
 *
 *  * Neither the name of the authors nor the names of his
 *    contributors may be used to endorse or promote products derived
 *    from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package DbPatch
 * @subpackage Command_Patch
 * @author Sandy Pleyte
 * @author Martijn de Letter
 * @copyright 2011 Sandy Pleyte
 * @copyright 2010-2011 Martijn de Letter
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link http://www.github.com/sndpl/DbPatch
 * @since File available since Release 1.0.0
 */

/**
 * SQL Patch file
 * 
 * @package DbPatch
 * @subpackage Command_Patch
 * @author Sandy Pleyte
 * @author Martijn de Letter
 * @copyright 2011 Sandy Pleyte
 * @copyright 2010-2011 Martijn de Letter
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link http://www.github.com/sndpl/DbPatch
 * @since File available since Release 1.0.0
 */
class DbPatch_Command_Patch_SQL extends DbPatch_Command_Patch_Abstract
{
    /**
     * @var array
     */
    protected $data = array(
        'filename' => null,
        'basename' => null,
        'patch_number' => null,
        'branch' => null,
        'description' => null,
    );

    /**
     * Apply SQL Patch
     * 
     * @return bool
     */
    public function apply()
    {
        $this->writer->line('apply patch: ' . $this->basename);
        $content = file_get_contents($this->data['filename']);
        if ($content == '') {
            $this->writer->error(
                sprintf('patch file %s is empty', $this->data['basename'])
            );
            return false;
        }

        $config     = $this->config->db->params;

        $database   = '';
        if (isset($config->dbname) && $config->dbname) {
            $database = escapeshellarg($config->dbname);
        }

        $user = '';
        if (isset($config->username) && $config->username) {
            $user = escapeshellarg($config->username);
        }

        $password = '';
        if (isset($config->password) && $config->password) {
            $password = '-p' . escapeshellarg($config->password);
        }

        $host = '';
        if (isset($config->host) && $config->host) {
            $host = escapeshellarg($config->host);
        }

        $port = '';
        if (isset($config->port) && $config->port) {
            $port = '-P' . (int)$port;
        }

        $filename = escapeshellarg($this->data['filename']);

        $command = sprintf(
            "mysql -h %s -u %s %s %s %s < '%s' 2>&1",
            $host,
            $user,
            $password,
            $port,
            $database,
            $filename
        );

        exec($command, $result, $return);

        if ($return <> 0) {

            $this->writer->error(sprintf("invalid SQL in patch file %s:\n\n%s\n",
                                         $this->data['basename'],
                                         implode(PHP_EOL, $result)
                                 ));
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'SQL';
    }

    /**
     * Return first line of the SQL Patch
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getComment(0);
    }

    /**
     * Create Empty SQL Patch
     *
     * @param string $description
     * @param string $patchDirectory
     * @param string $patchPrefix
     * @return void
     */
    public function create($description, $patchDirectory, $patchPrefix)
    {
        $patchNumberSize = $this->getPatchNumberSize($patchDirectory);
        $filename = $this->getPatchFilename($patchPrefix, strtolower($this->getType()), $patchNumberSize);
        $content = '-- ' . $description . PHP_EOL;
        $this->writeFile($patchDirectory . $filename, $content);
    }

}