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
 * @package     Importer
 * @version     3.0.0 Beta
 * @category    Config
 * @copyright	2008-2013 Marcel Domke
 * @license     http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE Version 2
 * @author      Marcel Domke <http://www.dcms-studio.de>
 * @link        http://www.dcms-studio.de
 * @file        Base.php
 */

var Lang = {
    langCache: [],
    lang: '',
    baselang: '', // callback language
    setBaseLang: function (langKey) {
        this.baselang = langKey;
    },
    register: function (langKey, items) {

        if (typeof this.langCache[langKey] == 'undefined')
        {
            this.langCache[langKey] = {};
        }
        if (items && items.length) {
            var tmp = {};
            for (var k in items) {
                tmp[k] = items[k];
            }

            $.extend(this.langCache[langKey], tmp);
        }
        return this;
    },
    trans: function (str) {

        if (typeof this.langCache[this.lang][str] == 'undefined') {
            // debug message


            return this.langCache[this.baselang][str];
        }
        return this.langCache[this.lang][str];
    },
    setLanguage: function (langKey) {
        this.lang = langKey;

        if (typeof this.langCache[langKey] == 'undefined') {
            // load this language
        }
        
        
        this.refreshGui();
    },
    refreshGui: function() {
        
    }
};