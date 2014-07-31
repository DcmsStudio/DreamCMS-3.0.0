<?php
 /**
 * DreamCMS 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP Version 5
 *
 * @package      
 * @version      3.0.0 Beta
 * @category     
 * @copyright    2008-2014 Marcel Domke
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author       Marcel Domke <http://www.dcms-studio.de>
 * @link         http://www.dcms-studio.de
 * @file         Interface.php
 */

interface Compiler_Expression_Interface
{
    // The expression engine control values
    const SINGLE_VAR = 0;
    const ASSIGNMENT = 1;
    const SCALAR = 2;
    const COMPOUND = 3;

    /**
     * The compiler uses this method to send itself to the expression engine.
     *
     * @param Compiler $compiler The compiler object
     */
    public function setCompiler(Compiler $compiler);

    /**
     * The method should reset all the object references it possesses.
     */
    public function dispose();

    /**
     * The role of this method is to parse the expression to the
     * corresponding PHP code.
     *
     * @param String $expression The expression source
     * @return Array
     */
    public function parse($expression);
} // end