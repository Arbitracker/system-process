<?php
/**
 * systemProcess argument base class
 *
 * This file is part of systemProcess.
 *
 * systemProcess is free software; you can redistribute it and/or modify it
 * under the terms of the Lesser GNU General Public License as published by the
 * Free Software Foundation; version 3 of the License.
 *
 * systemProcess is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the Lesser GNU General Public License
 * for more details.
 *
 * You should have received a copy of the Lesser GNU General Public License
 * along with systemProcess; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

namespace SystemProcess\Argument;

use \SystemProcess\Argument;

/**
 * Unescaped argument
 *
 * Argument, which values are NOT escaped in any way. Be careful, might lead to 
 * command injection.
 * 
 * @version $Revision$
 * @license LGPLv3
 */
class UnescapedArgument extends Argument
{
    /**
     * Get prepared argument value
     * 
     * @return string
     */
    public function getPrepared()
    {
        return $this->value;
    }
}

